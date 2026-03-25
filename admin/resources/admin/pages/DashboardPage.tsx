import axios from "axios";
import { FormEvent, useEffect, useMemo, useState } from "react";
import { LogOut, RefreshCw } from "lucide-react";
import {
  createAddOn,
  createVehicle,
  createVehicleDiscount,
  deleteAddOn,
  deleteVehicle,
  deleteVehicleDiscount,
  getDashboardAnalytics,
  getAddOns,
  getApiErrorMessage,
  getDashboardSummary,
  getOrderRequests,
  getTaxiRequests,
  getVehicleDiscounts,
  getVehicles,
  updateAddOn,
  updateOrderStatus,
  updateVehicle,
  updateVehicleDiscount
} from "../lib/api";
import type {
  AddOn,
  DashboardAnalytics,
  DashboardAnalyticsRange,
  DashboardSummary,
  OrderRequest,
  TaxiRequest,
  Vehicle,
  VehicleDiscount,
  DashboardPageProps,
  Section,
  ConfirmDialogState,
  PaginationMeta,
  LoadResourceOptions
} from "../types";
import DashboardTabs from "../components/dashboard/DashboardTabs";
import FormModal from "../components/dashboard/FormModal";
import { Button } from "../components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../components/ui/card";
import { Checkbox } from "../components/ui/checkbox";
import {
  Modal,
  ModalContent,
  ModalDescription,
  ModalFooter,
  ModalHeader,
  ModalTitle
} from "../components/ui/modal";
import { Input } from "../components/ui/input";
import { Label } from "../components/ui/label";
import { Select } from "../components/ui/select";
import { Tabs, TabsContent } from "../components/ui/tabs";
import { Textarea } from "../components/ui/textarea";
import OverviewPage from "./OverviewPage";
import VehiclesPage from "./VehiclesPage";
import AddOnsPage from "./AddOnsPage";
import DiscountsPage from "./DiscountsPage";
import OrderRequestsPage from "./OrderRequestsPage";
import TaxiRequestsPage from "./TaxiRequestsPage";
import {
  addOnTemplate,
  discountTemplate,
  initialConfirmState,
  ORDER_REQUESTS_PER_PAGE,
  RESOURCE_CACHE_KEYS,
  sectionTabs,
  TAXI_REQUESTS_PER_PAGE,
  vehicleTemplate
} from "../consts";
import { initialPaginationMeta, readCachedResource, writeCachedResource } from "../lib/utils";

export default function DashboardPage({ user, onLogout }: DashboardPageProps) {
  const [section, setSection] = useState<Section>("overview");
  const [busy, setBusy] = useState(false);
  const [feedback, setFeedback] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const [summary, setSummary] = useState<DashboardSummary | null>(null);
  const [analytics, setAnalytics] = useState<DashboardAnalytics | null>(null);
  const [analyticsRange, setAnalyticsRange] = useState<DashboardAnalyticsRange>("90d");
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [addOns, setAddOns] = useState<AddOn[]>([]);
  const [discounts, setDiscounts] = useState<VehicleDiscount[]>([]);
  const [orders, setOrders] = useState<OrderRequest[]>([]);
  const [taxiRequests, setTaxiRequests] = useState<TaxiRequest[]>([]);
  const [orderRequestsMeta, setOrderRequestsMeta] = useState<PaginationMeta>(
    initialPaginationMeta(ORDER_REQUESTS_PER_PAGE)
  );
  const [taxiRequestsMeta, setTaxiRequestsMeta] = useState<PaginationMeta>(
    initialPaginationMeta(TAXI_REQUESTS_PER_PAGE)
  );
  const [orderRequestsPage, setOrderRequestsPage] = useState(1);
  const [taxiRequestsPage, setTaxiRequestsPage] = useState(1);
  const [taxiDetailOpen, setTaxiDetailOpen] = useState(false);
  const [selectedTaxiRequest, setSelectedTaxiRequest] = useState<TaxiRequest | null>(null);

  const [vehicleModalOpen, setVehicleModalOpen] = useState(false);
  const [vehicleModalMode, setVehicleModalMode] = useState<"create" | "edit">("create");
  const [vehicleEditingId, setVehicleEditingId] = useState<number | null>(null);
  const [vehicleDraft, setVehicleDraft] = useState<Partial<Vehicle>>(vehicleTemplate);

  const [addOnModalOpen, setAddOnModalOpen] = useState(false);
  const [addOnModalMode, setAddOnModalMode] = useState<"create" | "edit">("create");
  const [addOnEditingId, setAddOnEditingId] = useState<number | null>(null);
  const [addOnDraft, setAddOnDraft] = useState<Partial<AddOn>>(addOnTemplate);

  const [discountModalOpen, setDiscountModalOpen] = useState(false);
  const [discountModalMode, setDiscountModalMode] = useState<"create" | "edit">("create");
  const [discountEditingId, setDiscountEditingId] = useState<number | null>(null);
  const [discountDraft, setDiscountDraft] = useState<Partial<VehicleDiscount>>(discountTemplate);

  const [confirmDialog, setConfirmDialog] = useState<ConfirmDialogState>(initialConfirmState);
  const [confirmBusy, setConfirmBusy] = useState(false);

  const sortedVehicles = useMemo(
    () =>
      [...vehicles].sort(
        (a, b) =>
          (a.landing_order ?? Number.MAX_SAFE_INTEGER) -
          (b.landing_order ?? Number.MAX_SAFE_INTEGER)
      ),
    [vehicles]
  );

  const loadAll = async () => {
    setBusy(true);
    setError(null);

    try {
      const [summaryRes, analyticsRes, vehiclesRes, addOnsRes, discountsRes, ordersRes, taxiRes] = await Promise.all([
        getDashboardSummary(),
        getDashboardAnalytics(analyticsRange),
        getVehicles(),
        getAddOns(),
        getVehicleDiscounts(),
        getOrderRequests({ per_page: ORDER_REQUESTS_PER_PAGE, page: orderRequestsPage, status: "all" }),
        getTaxiRequests({ per_page: TAXI_REQUESTS_PER_PAGE, page: taxiRequestsPage })
      ]);

      setSummary(summaryRes);
      setAnalytics(analyticsRes);
      setVehicles(vehiclesRes);
      setAddOns(addOnsRes);
      setDiscounts(discountsRes);
      setOrders(ordersRes.items);
      setTaxiRequests(taxiRes.items);
      setOrderRequestsMeta(ordersRes.meta);
      setTaxiRequestsMeta(taxiRes.meta);
      writeCachedResource(RESOURCE_CACHE_KEYS.vehicles, vehiclesRes);
      writeCachedResource(RESOURCE_CACHE_KEYS.addOns, addOnsRes);
      writeCachedResource(RESOURCE_CACHE_KEYS.discounts, discountsRes);
    } catch (loadError) {
      if (axios.isAxiosError(loadError) && loadError.response?.status === 401) {
        setError("Your admin session expired. Please sign in again.");
        void onLogout();
        return;
      }

      setError(getApiErrorMessage(loadError));
    } finally {
      setBusy(false);
    }
  };

  const loadResource = async <TResource,>(
    apiGetter: () => Promise<TResource>,
    onSuccess: (data: TResource) => void,
    options?: LoadResourceOptions
  ) => {
    setBusy(true);
    setError(null);

    const shouldReadFromCache = options?.readFromCache ?? false;
    const shouldWriteToCache = options?.writeToCache ?? Boolean(options?.cacheKey);
    const cacheKey = options?.cacheKey;

    if (shouldReadFromCache && cacheKey) {
      const cached = readCachedResource<TResource>(cacheKey);

      if (cached !== null) {
        onSuccess(cached);
        setBusy(false);
        return;
      }
    }

    try {
      const data = await apiGetter();
      onSuccess(data);

      if (cacheKey && shouldWriteToCache) {
        writeCachedResource(cacheKey, data);
      }
    } catch (loadError) {
      if (axios.isAxiosError(loadError) && loadError.response?.status === 401) {
        setError("Your admin session expired. Please sign in again.");
        void onLogout();
        return;
      }

      setError(getApiErrorMessage(loadError));
    } finally {
      setBusy(false);
    }
  };

  const withFeedback = async (action: () => Promise<void>, successMessage: string) => {
    setBusy(true);
    setError(null);
    setFeedback(null);

    try {
      await action();
      setFeedback(successMessage);
    } catch (actionError) {
      setError(getApiErrorMessage(actionError));
    } finally {
      setBusy(false);
    }
  };

  const openConfirm = (title: string, description: string, action: () => Promise<void>) => {
    setConfirmDialog({
      open: true,
      title,
      description,
      action
    });
  };

  const executeConfirm = async () => {
    if (!confirmDialog.action) {
      return;
    }

    setConfirmBusy(true);

    try {
      await confirmDialog.action();
      setConfirmDialog(initialConfirmState);
    } finally {
      setConfirmBusy(false);
    }
  };

  const openVehicleCreateModal = () => {
    setVehicleModalMode("create");
    setVehicleEditingId(null);
    setVehicleDraft(vehicleTemplate);
    setVehicleModalOpen(true);
  };

  const openVehicleEditModal = (vehicle: Vehicle) => {
    setVehicleModalMode("edit");
    setVehicleEditingId(vehicle.id);
    setVehicleDraft({ ...vehicle });
    setVehicleModalOpen(true);
  };

  const submitVehicleModal = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (vehicleModalMode === "edit" && vehicleEditingId === null) {
      setError("Vehicle edit context is missing.");
      return;
    }

    await withFeedback(async () => {
      if (vehicleModalMode === "create") {
        await createVehicle(vehicleDraft);
      } else {
        await updateVehicle(vehicleEditingId as number, vehicleDraft);
      }

      setVehicleModalOpen(false);
      setVehicleDraft(vehicleTemplate);
      setVehicleEditingId(null);
      await loadAll();
    }, vehicleModalMode === "create" ? "Vehicle created." : "Vehicle updated.");
  };

  const requestVehicleDelete = (vehicle: Vehicle) => {
    openConfirm(
      `Delete ${vehicle.name}?`,
      "This action cannot be undone.",
      async () => {
        await withFeedback(async () => {
          await deleteVehicle(vehicle.id);
          await loadAll();
        }, "Vehicle deleted.");
      }
    );
  };

  const requestVehicleDeleteFromModal = () => {
    if (vehicleModalMode !== "edit" || vehicleEditingId === null) {
      return;
    }

    const vehicle = vehicles.find((item) => item.id === vehicleEditingId);

    if (!vehicle) {
      setError("Vehicle no longer exists.");
      return;
    }

    setVehicleModalOpen(false);
    requestVehicleDelete(vehicle);
  };

  const openAddOnCreateModal = () => {
    setAddOnModalMode("create");
    setAddOnEditingId(null);
    setAddOnDraft(addOnTemplate);
    setAddOnModalOpen(true);
  };

  const openAddOnEditModal = (addOn: AddOn) => {
    setAddOnModalMode("edit");
    setAddOnEditingId(addOn.id);
    setAddOnDraft({ ...addOn });
    setAddOnModalOpen(true);
  };

  const submitAddOnModal = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (addOnModalMode === "edit" && addOnEditingId === null) {
      setError("Add-on edit context is missing.");
      return;
    }

    await withFeedback(async () => {
      if (addOnModalMode === "create") {
        await createAddOn(addOnDraft);
      } else {
        await updateAddOn(addOnEditingId as number, addOnDraft);
      }

      setAddOnModalOpen(false);
      setAddOnDraft(addOnTemplate);
      setAddOnEditingId(null);
      await loadAll();
    }, addOnModalMode === "create" ? "Add-on created." : "Add-on updated.");
  };

  const requestAddOnDelete = (addOn: AddOn) => {
    openConfirm(
      `Delete ${addOn.name}?`,
      "This action cannot be undone.",
      async () => {
        await withFeedback(async () => {
          await deleteAddOn(addOn.id);
          await loadAll();
        }, "Add-on deleted.");
      }
    );
  };

  const requestAddOnDeleteFromModal = () => {
    if (addOnModalMode !== "edit" || addOnEditingId === null) {
      return;
    }

    const addOn = addOns.find((item) => item.id === addOnEditingId);

    if (!addOn) {
      setError("Add-on no longer exists.");
      return;
    }

    setAddOnModalOpen(false);
    requestAddOnDelete(addOn);
  };

  const openDiscountCreateModal = () => {
    setDiscountModalMode("create");
    setDiscountEditingId(null);
    setDiscountDraft(discountTemplate);
    setDiscountModalOpen(true);
  };

  const openDiscountEditModal = (discount: VehicleDiscount) => {
    setDiscountModalMode("edit");
    setDiscountEditingId(discount.id);
    setDiscountDraft({
      vehicle_id: discount.vehicle_id,
      days: discount.days,
      price_USD: discount.price_USD,
      price_XCD: discount.price_XCD
    });
    setDiscountModalOpen(true);
  };

  const submitDiscountModal = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (discountModalMode === "edit" && discountEditingId === null) {
      setError("Discount edit context is missing.");
      return;
    }

    await withFeedback(async () => {
      if (discountModalMode === "create") {
        await createVehicleDiscount(discountDraft);
      } else {
        await updateVehicleDiscount(discountEditingId as number, discountDraft);
      }

      setDiscountModalOpen(false);
      setDiscountDraft(discountTemplate);
      setDiscountEditingId(null);
      await loadAll();
    }, discountModalMode === "create" ? "Discount created." : "Discount updated.");
  };

  const requestDiscountDelete = (discount: VehicleDiscount) => {
    openConfirm(
      `Delete discount #${discount.id}?`,
      "This action cannot be undone.",
      async () => {
        await withFeedback(async () => {
          await deleteVehicleDiscount(discount.id);
          await loadAll();
        }, "Discount deleted.");
      }
    );
  };

  const requestDiscountDeleteFromModal = () => {
    if (discountModalMode !== "edit" || discountEditingId === null) {
      return;
    }

    const discount = discounts.find((item) => item.id === discountEditingId);

    if (!discount) {
      setError("Discount no longer exists.");
      return;
    }

    setDiscountModalOpen(false);
    requestDiscountDelete(discount);
  };

  const toggleOrderStatusHandler = async (order: OrderRequest) => {
    await withFeedback(async () => {
      await updateOrderStatus(order.id, !order.confirmed);
      await loadAll();
    }, `Order #${order.id} updated.`);
  };

  const openTaxiRequestDetail = (request: TaxiRequest) => {
    setSelectedTaxiRequest(request);
    setTaxiDetailOpen(true);
  };

  const formatPaginationRange = (meta: PaginationMeta) => {
    if (meta.total === 0) {
      return "0 of 0";
    }

    const start = (meta.current_page - 1) * meta.per_page + 1;
    const end = Math.min(meta.total, meta.current_page * meta.per_page);
    return `${start}-${end} of ${meta.total}`;
  };

  useEffect(() => {
    // need to fetch the latest data of a section when it's visited,
    // in case the user made changes in another section that would affect it
    // (e.g. creating a discount would affect the overview analytics)
    switch (section) {
      case "overview":
        void loadResource(getDashboardSummary, (data) => {
          setSummary(data);
        });
        void loadResource(() => getDashboardAnalytics(analyticsRange), (data) => {
          setAnalytics(data);
        });
        break;
      case "vehicles":
        void loadResource(getVehicles, (data) => {
          setVehicles(data);
        }, {
          cacheKey: RESOURCE_CACHE_KEYS.vehicles,
          readFromCache: true
        });
        break;
      case "addons":
        void loadResource(getAddOns, (data) => {
          setAddOns(data);
        }, {
          cacheKey: RESOURCE_CACHE_KEYS.addOns,
          readFromCache: true
        });
        break;
      case "discounts":
        void loadResource(getVehicleDiscounts, (data) => {
          setDiscounts(data);
        }, {
          cacheKey: RESOURCE_CACHE_KEYS.discounts,
          readFromCache: true
        });
        break;
      case "orders":
        void loadResource(
          () => getOrderRequests({ per_page: ORDER_REQUESTS_PER_PAGE, page: orderRequestsPage, status: "all" }),
          (data) => {
            setOrders(data.items);
            setOrderRequestsMeta(data.meta);
          }
        );
        break;
      case "taxi":
        void loadResource(
          () => getTaxiRequests({ per_page: TAXI_REQUESTS_PER_PAGE, page: taxiRequestsPage }),
          (data) => {
            setTaxiRequests(data.items);
            setTaxiRequestsMeta(data.meta);
          }
        );
        break;
    }
  }, [analyticsRange, onLogout, orderRequestsPage, section, taxiRequestsPage]);

  if (!summary) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-muted/40 p-6">
        <Card className="w-full max-w-lg">
          <CardHeader>
            <CardTitle>IBES Admin Dashboard</CardTitle>
            <CardDescription>Load the latest data to begin.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {error ? (
              <div className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm font-medium text-destructive">
                {error}
              </div>
            ) : null}
            <Button onClick={() => void loadAll()} disabled={busy}>
              <RefreshCw className="h-4 w-4" />
              {busy ? "Loading..." : "Load Dashboard"}
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-muted/35 p-4 md:p-6">
      <div className="mx-auto max-w-350 space-y-6">
        <Card className="border-border/70 shadow-sm">
          <CardHeader className="flex flex-col gap-4 pb-5 md:flex-row md:items-center md:justify-between">
            <div>
              <CardTitle className="text-3xl">IBES Admin Dashboard</CardTitle>
              <CardDescription>Signed in as {user.username}. Manage fleet, pricing, and requests.</CardDescription>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Button variant="outline" onClick={() => void loadAll()} disabled={busy}>
                <RefreshCw className="h-4 w-4" />
                {busy ? "Refreshing..." : "Refresh"}
              </Button>
              <Button variant="destructive" onClick={() => void onLogout()} disabled={busy}>
                <LogOut className="h-4 w-4" />
                Logout
              </Button>
            </div>
          </CardHeader>
        </Card>

        {feedback ? (
          <div className="rounded-md border border-emerald-300/70 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
            {feedback}
          </div>
        ) : null}
        {error ? (
          <div className="rounded-md border border-destructive/30 bg-destructive/10 px-4 py-2 text-sm font-medium text-destructive">
            {error}
          </div>
        ) : null}

        <Tabs
          value={section}
          onValueChange={(value) => setSection(value as Section)}
          className="grid gap-6 lg:grid-cols-[260px_1fr]"
        >
          <aside className="space-y-4">
            <Card className="overflow-hidden border-border/70">
              <CardHeader className="border-b bg-card/70 py-4">
                <CardTitle className="text-base">Navigation</CardTitle>
                <CardDescription>Switch between admin modules.</CardDescription>
              </CardHeader>
              <CardContent className="p-3">
                <DashboardTabs tabs={sectionTabs} />
              </CardContent>
            </Card>
            <Card className="border-border/70">
              <CardHeader>
                <CardTitle className="text-sm">Snapshot</CardTitle>
                <CardDescription>Current totals from the live booking database.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-2 text-sm text-muted-foreground">
                <p>{summary.vehicles_total} vehicles</p>
                <p>{summary.add_ons_total} add-ons</p>
                <p>{summary.vehicle_discounts_total} discount rows</p>
                <p>{summary.order_requests_total} order requests</p>
                <p>{summary.taxi_requests_total} taxi requests</p>
              </CardContent>
            </Card>
          </aside>

          <div className="min-w-0 space-y-6">
            <TabsContent value="overview" className="space-y-4">
              <OverviewPage
                summary={summary}
                analytics={analytics}
                analyticsRange={analyticsRange}
                busy={busy}
                onAnalyticsRangeChange={setAnalyticsRange}
              />
            </TabsContent>

            <TabsContent value="vehicles" className="space-y-4">
              <VehiclesPage
                vehicles={sortedVehicles}
                busy={busy}
                onCreate={openVehicleCreateModal}
                onEdit={openVehicleEditModal}
              />
            </TabsContent>

            <TabsContent value="addons" className="space-y-4">
              <AddOnsPage
                addOns={addOns}
                busy={busy}
                onCreate={openAddOnCreateModal}
                onEdit={openAddOnEditModal}
              />
            </TabsContent>

            <TabsContent value="discounts" className="space-y-4">
              <DiscountsPage
                discounts={discounts}
                busy={busy}
                onCreate={openDiscountCreateModal}
                onEdit={openDiscountEditModal}
              />
            </TabsContent>

            <TabsContent value="orders" className="space-y-4">
              <OrderRequestsPage
                orders={orders}
                busy={busy}
                onToggleStatus={(order) => void toggleOrderStatusHandler(order)}
                paginationLabel={formatPaginationRange(orderRequestsMeta)}
                currentPage={orderRequestsMeta.current_page}
                lastPage={orderRequestsMeta.last_page}
                canGoPrevious={orderRequestsMeta.current_page > 1}
                canGoNext={orderRequestsMeta.current_page < orderRequestsMeta.last_page}
                onPreviousPage={() => setOrderRequestsPage((prev) => Math.max(1, prev - 1))}
                onNextPage={() =>
                  setOrderRequestsPage((prev) => Math.min(Math.max(1, orderRequestsMeta.last_page), prev + 1))
                }
              />
            </TabsContent>

            <TabsContent value="taxi" className="space-y-4">
              <TaxiRequestsPage
                taxiRequests={taxiRequests}
                busy={busy}
                onOpenDetail={openTaxiRequestDetail}
                paginationLabel={formatPaginationRange(taxiRequestsMeta)}
                currentPage={taxiRequestsMeta.current_page}
                lastPage={taxiRequestsMeta.last_page}
                canGoPrevious={taxiRequestsMeta.current_page > 1}
                canGoNext={taxiRequestsMeta.current_page < taxiRequestsMeta.last_page}
                onPreviousPage={() => setTaxiRequestsPage((prev) => Math.max(1, prev - 1))}
                onNextPage={() =>
                  setTaxiRequestsPage((prev) => Math.min(Math.max(1, taxiRequestsMeta.last_page), prev + 1))
                }
              />
            </TabsContent>
          </div>
        </Tabs>

        <FormModal
          open={vehicleModalOpen}
          onOpenChange={setVehicleModalOpen}
          title={vehicleModalMode === "create" ? "Add Vehicle" : "Edit Vehicle"}
          description="Set pricing, specs, and visibility for this vehicle."
          onSubmit={(event) => void submitVehicleModal(event)}
          submitLabel={vehicleModalMode === "create" ? "Create Vehicle" : "Save Changes"}
          loading={busy}
          dangerActionLabel={vehicleModalMode === "edit" ? "Delete Vehicle" : undefined}
          onDangerAction={vehicleModalMode === "edit" ? requestVehicleDeleteFromModal : undefined}
        >
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="vehicle-name">Name</Label>
              <Input
                id="vehicle-name"
                value={vehicleDraft.name ?? ""}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, name: event.target.value }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-type">Type</Label>
              <Input
                id="vehicle-type"
                value={vehicleDraft.type ?? ""}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, type: event.target.value }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-slug">Slug</Label>
              <Input
                id="vehicle-slug"
                value={vehicleDraft.slug ?? ""}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, slug: event.target.value }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-year">Year</Label>
              <Input
                id="vehicle-year"
                type="number"
                value={vehicleDraft.year ?? new Date().getFullYear()}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, year: Number(event.target.value) }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-usd">USD / Day</Label>
              <Input
                id="vehicle-usd"
                type="number"
                value={vehicleDraft.base_price_USD ?? 0}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, base_price_USD: Number(event.target.value) }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-xcd">XCD / Day</Label>
              <Input
                id="vehicle-xcd"
                type="number"
                value={vehicleDraft.base_price_XCD ?? 0}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, base_price_XCD: Number(event.target.value) }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-insurance">Insurance</Label>
              <Input
                id="vehicle-insurance"
                type="number"
                value={vehicleDraft.insurance ?? 0}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, insurance: Number(event.target.value) }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-landing-order">Landing Order</Label>
              <Input
                id="vehicle-landing-order"
                type="number"
                value={vehicleDraft.landing_order ?? ""}
                onChange={(event) =>
                  setVehicleDraft((prev) => ({
                    ...prev,
                    landing_order: event.target.value === "" ? null : Number(event.target.value)
                  }))
                }
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-seats">Seats</Label>
              <Input
                id="vehicle-seats"
                type="number"
                value={vehicleDraft.people ?? 4}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, people: Number(event.target.value) }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-bags">Bags</Label>
              <Input
                id="vehicle-bags"
                type="number"
                value={vehicleDraft.bags ?? 0}
                onChange={(event) =>
                  setVehicleDraft((prev) => ({
                    ...prev,
                    bags: event.target.value === "" ? null : Number(event.target.value)
                  }))
                }
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-doors">Doors</Label>
              <Input
                id="vehicle-doors"
                type="number"
                value={vehicleDraft.doors ?? 4}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, doors: Number(event.target.value) }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle-requests">Times Requested</Label>
              <Input
                id="vehicle-requests"
                type="number"
                value={vehicleDraft.times_requested ?? 0}
                onChange={(event) =>
                  setVehicleDraft((prev) => ({
                    ...prev,
                    times_requested: Number(event.target.value)
                  }))
                }
              />
            </div>
          </div>
          <div className="grid gap-3 md:grid-cols-3">
            <label className="flex items-center gap-2 text-sm font-medium">
              <Checkbox
                checked={Boolean(vehicleDraft.showing)}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, showing: event.target.checked }))}
              />
              Showing
            </label>
            <label className="flex items-center gap-2 text-sm font-medium">
              <Checkbox
                checked={Boolean(vehicleDraft.ac)}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, ac: event.target.checked }))}
              />
              A/C
            </label>
            <label className="flex items-center gap-2 text-sm font-medium">
              <Checkbox
                checked={Boolean(vehicleDraft.manual)}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, manual: event.target.checked }))}
              />
              Manual
            </label>
            <label className="flex items-center gap-2 text-sm font-medium">
              <Checkbox
                checked={Boolean(vehicleDraft.four_wd)}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, four_wd: event.target.checked }))}
              />
              4WD
            </label>
            <label className="flex items-center gap-2 text-sm font-medium">
              <Checkbox
                checked={Boolean(vehicleDraft.taxi)}
                onChange={(event) => setVehicleDraft((prev) => ({ ...prev, taxi: event.target.checked }))}
              />
              Taxi Enabled
            </label>
          </div>
        </FormModal>

        <FormModal
          open={addOnModalOpen}
          onOpenChange={setAddOnModalOpen}
          title={addOnModalMode === "create" ? "Add Add-On" : "Edit Add-On"}
          description="Add optional products or services for bookings."
          onSubmit={(event) => void submitAddOnModal(event)}
          submitLabel={addOnModalMode === "create" ? "Create Add-On" : "Save Changes"}
          loading={busy}
          dangerActionLabel={addOnModalMode === "edit" ? "Delete Add-On" : undefined}
          onDangerAction={addOnModalMode === "edit" ? requestAddOnDeleteFromModal : undefined}
        >
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="addon-name">Name</Label>
              <Input
                id="addon-name"
                value={addOnDraft.name ?? ""}
                onChange={(event) => setAddOnDraft((prev) => ({ ...prev, name: event.target.value }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="addon-abbr">Abbreviation</Label>
              <Input
                id="addon-abbr"
                value={addOnDraft.abbr ?? ""}
                onChange={(event) => setAddOnDraft((prev) => ({ ...prev, abbr: event.target.value }))}
                required
              />
            </div>
            <div className="space-y-2 md:col-span-2">
              <Label htmlFor="addon-description">Description</Label>
              <Textarea
                id="addon-description"
                value={addOnDraft.description ?? ""}
                onChange={(event) => setAddOnDraft((prev) => ({ ...prev, description: event.target.value }))}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="addon-cost">Cost</Label>
              <Input
                id="addon-cost"
                type="number"
                value={addOnDraft.cost ?? 0}
                onChange={(event) =>
                  setAddOnDraft((prev) => ({
                    ...prev,
                    cost: event.target.value === "" ? null : Number(event.target.value)
                  }))
                }
              />
            </div>
            <label className="flex items-end gap-2 text-sm font-medium">
              <Checkbox
                checked={Boolean(addOnDraft.fixed_price)}
                onChange={(event) => setAddOnDraft((prev) => ({ ...prev, fixed_price: event.target.checked }))}
              />
              Fixed price
            </label>
          </div>
        </FormModal>

        <FormModal
          open={discountModalOpen}
          onOpenChange={setDiscountModalOpen}
          title={discountModalMode === "create" ? "Add Discount" : "Edit Discount"}
          description="Set discounted daily prices for longer rentals."
          onSubmit={(event) => void submitDiscountModal(event)}
          submitLabel={discountModalMode === "create" ? "Create Discount" : "Save Changes"}
          loading={busy}
          dangerActionLabel={discountModalMode === "edit" ? "Delete Discount" : undefined}
          onDangerAction={discountModalMode === "edit" ? requestDiscountDeleteFromModal : undefined}
        >
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2 md:col-span-2">
              <Label htmlFor="discount-vehicle">Vehicle</Label>
              <Select
                id="discount-vehicle"
                value={discountDraft.vehicle_id ?? 0}
                onChange={(event) =>
                  setDiscountDraft((prev) => ({
                    ...prev,
                    vehicle_id: Number(event.target.value)
                  }))
                }
                required
              >
                <option value={0}>Select vehicle...</option>
                {vehicles.map((vehicle) => (
                  <option key={vehicle.id} value={vehicle.id}>
                    {vehicle.name}
                  </option>
                ))}
              </Select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="discount-days">Days</Label>
              <Input
                id="discount-days"
                type="number"
                value={discountDraft.days ?? 4}
                onChange={(event) =>
                  setDiscountDraft((prev) => ({
                    ...prev,
                    days: Number(event.target.value)
                  }))
                }
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="discount-usd">USD</Label>
              <Input
                id="discount-usd"
                type="number"
                value={discountDraft.price_USD ?? 0}
                onChange={(event) =>
                  setDiscountDraft((prev) => ({
                    ...prev,
                    price_USD: Number(event.target.value)
                  }))
                }
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="discount-xcd">XCD</Label>
              <Input
                id="discount-xcd"
                type="number"
                value={discountDraft.price_XCD ?? 0}
                onChange={(event) =>
                  setDiscountDraft((prev) => ({
                    ...prev,
                    price_XCD: Number(event.target.value)
                  }))
                }
                required
              />
            </div>
          </div>
        </FormModal>

        <Modal
          open={taxiDetailOpen}
          onOpenChange={(open) => {
            setTaxiDetailOpen(open);

            if (!open) {
              setSelectedTaxiRequest(null);
            }
          }}
        >
          <ModalContent>
            <ModalHeader>
              <ModalTitle>Taxi Request #{selectedTaxiRequest?.request_id ?? "-"}</ModalTitle>
              <ModalDescription>Full details from the public taxi request form.</ModalDescription>
            </ModalHeader>

            {selectedTaxiRequest ? (
              <div className="grid gap-3 text-sm md:grid-cols-2">
                <div>
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Request ID</p>
                  <p>{selectedTaxiRequest.request_id}</p>
                </div>
                <div>
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Name</p>
                  <p>{selectedTaxiRequest.customer_name}</p>
                </div>
                <div>
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Phone</p>
                  <p>{selectedTaxiRequest.customer_phone}</p>
                </div>
                <div>
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Pickup</p>
                  <p>{selectedTaxiRequest.pickup_location}</p>
                </div>
                <div>
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Dropoff</p>
                  <p>{selectedTaxiRequest.dropoff_location}</p>
                </div>
                <div>
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Pickup Time</p>
                  <p>{selectedTaxiRequest.pickup_time}</p>
                </div>
                <div>
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Passengers</p>
                  <p>{selectedTaxiRequest.number_of_passengers}</p>
                </div>
                <div className="md:col-span-2">
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Special Requirements</p>
                  <p className="whitespace-pre-wrap">{selectedTaxiRequest.special_requirements || "-"}</p>
                </div>
                <div className="md:col-span-2">
                  <p className="text-muted-foreground text-xs font-semibold uppercase">Created</p>
                  <p>{selectedTaxiRequest.created_at}</p>
                </div>
              </div>
            ) : null}

            <ModalFooter>
              <Button type="button" variant="outline" onClick={() => setTaxiDetailOpen(false)}>
                Close
              </Button>
            </ModalFooter>
          </ModalContent>
        </Modal>

        <Modal
          open={confirmDialog.open}
          onOpenChange={(open) =>
            setConfirmDialog((prev) => ({
              ...prev,
              open,
              action: open ? prev.action : null
            }))
          }
        >
          <ModalContent>
            <ModalHeader>
              <ModalTitle>{confirmDialog.title}</ModalTitle>
              <ModalDescription>{confirmDialog.description}</ModalDescription>
            </ModalHeader>
            <ModalFooter>
              <Button
                type="button"
                variant="outline"
                onClick={() => setConfirmDialog(initialConfirmState)}
                disabled={confirmBusy}
              >
                Cancel
              </Button>
              <Button type="button" variant="destructive" onClick={() => void executeConfirm()} disabled={confirmBusy}>
                {confirmBusy ? "Working..." : "Confirm"}
              </Button>
            </ModalFooter>
          </ModalContent>
        </Modal>
      </div>
    </div>
  );
}
