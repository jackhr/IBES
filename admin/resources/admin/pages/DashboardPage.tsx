import axios from "axios";
import { FormEvent, useEffect, useMemo, useState } from "react";
import {
  type LucideIcon,
  CarFront,
  CarTaxiFront,
  CircleDollarSign,
  ClipboardList,
  LayoutGrid,
  LogOut,
  Plus,
  RefreshCw,
  Tags,
  Pencil,
  BadgePercent
} from "lucide-react";
import {
  createAddOn,
  createVehicle,
  createVehicleDiscount,
  deleteAddOn,
  deleteVehicle,
  deleteVehicleDiscount,
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
import type { AddOn, AdminUser, DashboardSummary, OrderRequest, TaxiRequest, Vehicle, VehicleDiscount } from "../types";
import DashboardTabs, { type DashboardTabItem } from "../components/dashboard/DashboardTabs";
import DataTable from "../components/dashboard/DataTable";
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
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from "../components/ui/table";
import { Tabs, TabsContent } from "../components/ui/tabs";
import { Textarea } from "../components/ui/textarea";

type DashboardPageProps = {
  user: AdminUser;
  onLogout: () => Promise<void>;
};

type Section = "overview" | "vehicles" | "addons" | "discounts" | "orders" | "taxi";

type ConfirmDialogState = {
  open: boolean;
  title: string;
  description: string;
  action: (() => Promise<void>) | null;
};

type OverviewMetric = {
  title: string;
  value: string;
  note: string;
  icon: LucideIcon;
};

const sectionTabs: DashboardTabItem<Section>[] = [
  { value: "overview", label: "Overview", icon: LayoutGrid },
  { value: "vehicles", label: "Vehicles", icon: CarFront },
  { value: "addons", label: "Add-Ons", icon: Tags },
  { value: "discounts", label: "Discounts", icon: BadgePercent },
  { value: "orders", label: "Orders", icon: ClipboardList },
  { value: "taxi", label: "Taxi", icon: CarTaxiFront }
];

const vehicleTemplate: Partial<Vehicle> = {
  name: "",
  type: "suv",
  slug: "",
  showing: true,
  landing_order: null,
  base_price_XCD: 0,
  base_price_USD: 0,
  insurance: 0,
  times_requested: 0,
  people: 4,
  bags: 2,
  doors: 4,
  four_wd: false,
  ac: true,
  manual: false,
  year: new Date().getFullYear(),
  taxi: false
};

const addOnTemplate: Partial<AddOn> = {
  name: "",
  cost: 0,
  description: "",
  abbr: "",
  fixed_price: false
};

const discountTemplate: Partial<VehicleDiscount> = {
  vehicle_id: 0,
  days: 4,
  price_USD: 0,
  price_XCD: 0
};

const initialConfirmState: ConfirmDialogState = {
  open: false,
  title: "",
  description: "",
  action: null
};

const MAX_SPECIAL_REQUIREMENTS_PREVIEW_LENGTH = 90;

export default function DashboardPage({ user, onLogout }: DashboardPageProps) {
  const [section, setSection] = useState<Section>("overview");
  const [busy, setBusy] = useState(false);
  const [feedback, setFeedback] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const [summary, setSummary] = useState<DashboardSummary | null>(null);
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [addOns, setAddOns] = useState<AddOn[]>([]);
  const [discounts, setDiscounts] = useState<VehicleDiscount[]>([]);
  const [orders, setOrders] = useState<OrderRequest[]>([]);
  const [taxiRequests, setTaxiRequests] = useState<TaxiRequest[]>([]);
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

  const overviewMetrics = useMemo<OverviewMetric[]>(
    () => [
      {
        title: "Total Vehicles",
        value: summary ? String(summary.vehicles_total) : "-",
        note: summary ? `${summary.vehicles_showing} visible on the website.` : "",
        icon: CarFront
      },
      {
        title: "Add-Ons & Discounts",
        value: summary ? String(summary.add_ons_total) : "-",
        note: summary ? `${summary.vehicle_discounts_total} discount rows configured.` : "",
        icon: Tags
      },
      {
        title: "Order Requests",
        value: summary ? String(summary.order_requests_total) : "-",
        note: summary ? `${summary.order_requests_pending} pending confirmations.` : "",
        icon: ClipboardList
      },
      {
        title: "Revenue (USD)",
        value: summary ? `$${summary.order_requests_revenue.toFixed(2)}` : "-",
        note: "Order subtotal aggregate.",
        icon: CircleDollarSign
      }
    ],
    [summary]
  );

  useEffect(() => {
    void loadAll();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const loadAll = async () => {
    setBusy(true);
    setError(null);

    try {
      const [summaryRes, vehiclesRes, addOnsRes, discountsRes, ordersRes, taxiRes] = await Promise.all([
        getDashboardSummary(),
        getVehicles(),
        getAddOns(),
        getVehicleDiscounts(),
        getOrderRequests({ per_page: 50, status: "all" }),
        getTaxiRequests({ per_page: 50 })
      ]);

      setSummary(summaryRes);
      setVehicles(vehiclesRes);
      setAddOns(addOnsRes);
      setDiscounts(discountsRes);
      setOrders(ordersRes.items);
      setTaxiRequests(taxiRes.items);
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

  const getTaxiSpecialRequirementsPreview = (value: string | null) => {
    const content = value?.trim();

    if (!content) {
      return "-";
    }

    if (content.length <= MAX_SPECIAL_REQUIREMENTS_PREVIEW_LENGTH) {
      return content;
    }

    return `${content.slice(0, MAX_SPECIAL_REQUIREMENTS_PREVIEW_LENGTH - 1)}…`;
  };

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
      <div className="mx-auto max-w-[1400px] space-y-6">
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

          <div className="space-y-6">
            <TabsContent value="overview" className="space-y-4">
              <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {overviewMetrics.map((metric) => (
                  <Card key={metric.title} className="border-border/70 shadow-sm">
                    <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
                      <CardDescription>{metric.title}</CardDescription>
                      <metric.icon className="text-muted-foreground h-4 w-4" />
                    </CardHeader>
                    <CardContent className="space-y-1">
                      <CardTitle className="text-2xl">{metric.value}</CardTitle>
                      <p className="text-muted-foreground text-sm">{metric.note}</p>
                    </CardContent>
                  </Card>
                ))}
              </div>

              <Card className="border-border/70 shadow-sm">
                <CardHeader>
                  <CardTitle className="text-base">Operations Overview</CardTitle>
                  <CardDescription>Quick-glance service health for today.</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-3 text-sm md:grid-cols-3">
                  <div className="rounded-lg border bg-background px-3 py-2">
                    <p className="text-muted-foreground">Pending Orders</p>
                    <p className="mt-1 text-lg font-semibold">{summary.order_requests_pending}</p>
                  </div>
                  <div className="rounded-lg border bg-background px-3 py-2">
                    <p className="text-muted-foreground">Confirmed Orders</p>
                    <p className="mt-1 text-lg font-semibold">
                      {Math.max(0, summary.order_requests_total - summary.order_requests_pending)}
                    </p>
                  </div>
                  <div className="rounded-lg border bg-background px-3 py-2">
                    <p className="text-muted-foreground">Taxi Queue</p>
                    <p className="mt-1 text-lg font-semibold">{summary.taxi_requests_total}</p>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="vehicles" className="space-y-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between">
                  <div>
                    <CardTitle>Vehicles</CardTitle>
                    <CardDescription>Manage your rentable fleet and landing order.</CardDescription>
                  </div>
                  <Button onClick={openVehicleCreateModal}>
                    <Plus className="h-4 w-4" />
                    New Vehicle
                  </Button>
                </CardHeader>
                <CardContent>
                  <DataTable>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>ID</TableHead>
                          <TableHead>Name</TableHead>
                          <TableHead>Type</TableHead>
                          <TableHead>USD</TableHead>
                          <TableHead>Showing</TableHead>
                          <TableHead>Requests</TableHead>
                          <TableHead className="text-right">Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {sortedVehicles.map((vehicle) => (
                          <TableRow key={vehicle.id}>
                            <TableCell>{vehicle.id}</TableCell>
                            <TableCell>{vehicle.name}</TableCell>
                            <TableCell>{vehicle.type}</TableCell>
                            <TableCell>${vehicle.base_price_USD}</TableCell>
                            <TableCell>{vehicle.showing ? "Yes" : "No"}</TableCell>
                            <TableCell>{vehicle.times_requested}</TableCell>
                            <TableCell>
                              <div className="flex justify-end">
                                <Button
                                  type="button"
                                  variant="outline"
                                  size="sm"
                                  onClick={() => openVehicleEditModal(vehicle)}
                                  disabled={busy}
                                >
                                  <Pencil className="h-3.5 w-3.5" />
                                  Edit
                                </Button>
                              </div>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </DataTable>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="addons" className="space-y-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between">
                  <div>
                    <CardTitle>Add-Ons</CardTitle>
                    <CardDescription>Configure optional rental items and extras.</CardDescription>
                  </div>
                  <Button onClick={openAddOnCreateModal}>
                    <Plus className="h-4 w-4" />
                    New Add-On
                  </Button>
                </CardHeader>
                <CardContent>
                  <DataTable>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>ID</TableHead>
                          <TableHead>Name</TableHead>
                          <TableHead>Abbr</TableHead>
                          <TableHead>Cost</TableHead>
                          <TableHead>Fixed</TableHead>
                          <TableHead className="text-right">Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {addOns.map((addOn) => (
                          <TableRow key={addOn.id}>
                            <TableCell>{addOn.id}</TableCell>
                            <TableCell>{addOn.name}</TableCell>
                            <TableCell>{addOn.abbr}</TableCell>
                            <TableCell>{addOn.cost ?? "-"}</TableCell>
                            <TableCell>{addOn.fixed_price ? "Yes" : "No"}</TableCell>
                            <TableCell>
                              <div className="flex justify-end">
                                <Button
                                  type="button"
                                  variant="outline"
                                  size="sm"
                                  onClick={() => openAddOnEditModal(addOn)}
                                  disabled={busy}
                                >
                                  <Pencil className="h-3.5 w-3.5" />
                                  Edit
                                </Button>
                              </div>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </DataTable>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="discounts" className="space-y-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between">
                  <div>
                    <CardTitle>Vehicle Discounts</CardTitle>
                    <CardDescription>Define discounted rates by vehicle and minimum days.</CardDescription>
                  </div>
                  <Button onClick={openDiscountCreateModal}>
                    <Plus className="h-4 w-4" />
                    New Discount
                  </Button>
                </CardHeader>
                <CardContent>
                  <DataTable>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>ID</TableHead>
                          <TableHead>Vehicle</TableHead>
                          <TableHead>Days</TableHead>
                          <TableHead>USD</TableHead>
                          <TableHead>XCD</TableHead>
                          <TableHead className="text-right">Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {discounts.map((discount) => (
                          <TableRow key={discount.id}>
                            <TableCell>{discount.id}</TableCell>
                            <TableCell>{discount.vehicle?.name ?? `#${discount.vehicle_id}`}</TableCell>
                            <TableCell>{discount.days}</TableCell>
                            <TableCell>${discount.price_USD}</TableCell>
                            <TableCell>${discount.price_XCD}</TableCell>
                            <TableCell>
                              <div className="flex justify-end">
                                <Button
                                  type="button"
                                  variant="outline"
                                  size="sm"
                                  onClick={() => openDiscountEditModal(discount)}
                                  disabled={busy}
                                >
                                  <Pencil className="h-3.5 w-3.5" />
                                  Edit
                                </Button>
                              </div>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </DataTable>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="orders" className="space-y-4">
              <Card>
                <CardHeader>
                  <CardTitle>Order Requests</CardTitle>
                  <CardDescription>Latest 50 car rental requests with quick status toggles.</CardDescription>
                </CardHeader>
                <CardContent>
                  <DataTable>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>ID</TableHead>
                          <TableHead>Customer</TableHead>
                          <TableHead>Vehicle</TableHead>
                          <TableHead>Days</TableHead>
                          <TableHead>Subtotal</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead className="text-right">Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {orders.map((order) => (
                          <TableRow key={order.id}>
                            <TableCell>{order.id}</TableCell>
                            <TableCell>
                              {order.contact_info
                                ? `${order.contact_info.first_name} ${order.contact_info.last_name}`
                                : "-"}
                            </TableCell>
                            <TableCell>{order.vehicle?.name ?? "-"}</TableCell>
                            <TableCell>{order.days}</TableCell>
                            <TableCell>${order.sub_total.toFixed(2)}</TableCell>
                            <TableCell>{order.confirmed ? "Confirmed" : "Pending"}</TableCell>
                            <TableCell>
                              <div className="flex justify-end">
                                <Button
                                  type="button"
                                  variant="outline"
                                  size="sm"
                                  onClick={() => void toggleOrderStatusHandler(order)}
                                  disabled={busy}
                                >
                                  Mark {order.confirmed ? "Pending" : "Confirmed"}
                                </Button>
                              </div>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </DataTable>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="taxi" className="space-y-4">
              <Card>
                <CardHeader>
                  <CardTitle>Taxi Requests</CardTitle>
                  <CardDescription>Latest 50 transfer requests from the public taxi form.</CardDescription>
                </CardHeader>
                <CardContent>
                  <DataTable>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>ID</TableHead>
                          <TableHead>Name</TableHead>
                          <TableHead>Phone</TableHead>
                          <TableHead>Pickup</TableHead>
                          <TableHead>Dropoff</TableHead>
                          <TableHead>Time</TableHead>
                          <TableHead>Pax</TableHead>
                          <TableHead>Special Requirements</TableHead>
                          <TableHead>Created</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {taxiRequests.map((request) => (
                          <TableRow
                            key={request.request_id}
                            role="button"
                            tabIndex={0}
                            className="cursor-pointer"
                            onClick={() => openTaxiRequestDetail(request)}
                            onKeyDown={(event) => {
                              if (event.key === "Enter" || event.key === " ") {
                                event.preventDefault();
                                openTaxiRequestDetail(request);
                              }
                            }}
                          >
                            <TableCell>{request.request_id}</TableCell>
                            <TableCell>{request.customer_name}</TableCell>
                            <TableCell>{request.customer_phone}</TableCell>
                            <TableCell>{request.pickup_location}</TableCell>
                            <TableCell>{request.dropoff_location}</TableCell>
                            <TableCell>{request.pickup_time}</TableCell>
                            <TableCell>{request.number_of_passengers}</TableCell>
                            <TableCell>
                              <span className="block max-w-[280px] truncate" title={request.special_requirements ?? "-"}>
                                {getTaxiSpecialRequirementsPreview(request.special_requirements)}
                              </span>
                            </TableCell>
                            <TableCell>{request.created_at}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </DataTable>
                </CardContent>
              </Card>
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
