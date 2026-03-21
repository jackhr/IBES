import axios from "axios";
import { FormEvent, useEffect, useMemo, useState } from "react";
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
  updateVehicle
} from "../lib/api";
import type { AddOn, AdminUser, DashboardSummary, OrderRequest, TaxiRequest, Vehicle, VehicleDiscount } from "../types";

type DashboardPageProps = {
  user: AdminUser;
  onLogout: () => Promise<void>;
};

type Section = "overview" | "vehicles" | "addons" | "discounts" | "orders" | "taxi";

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

  const [vehicleDraft, setVehicleDraft] = useState<Partial<Vehicle>>(vehicleTemplate);
  const [addOnDraft, setAddOnDraft] = useState<Partial<AddOn>>(addOnTemplate);
  const [discountDraft, setDiscountDraft] = useState<Partial<VehicleDiscount>>(discountTemplate);

  const sortedVehicles = useMemo(
    () => [...vehicles].sort((a, b) => (a.landing_order ?? Number.MAX_SAFE_INTEGER) - (b.landing_order ?? Number.MAX_SAFE_INTEGER)),
    [vehicles]
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

  const createVehicleHandler = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    await withFeedback(async () => {
      await createVehicle(vehicleDraft);
      setVehicleDraft(vehicleTemplate);
      await loadAll();
    }, "Vehicle created.");
  };

  const editVehicleHandler = async (vehicle: Vehicle) => {
    const nextName = window.prompt("Vehicle name", vehicle.name);

    if (!nextName) {
      return;
    }

    const nextUsd = Number(window.prompt("Base USD/day", String(vehicle.base_price_USD)));
    const nextXcd = Number(window.prompt("Base XCD/day", String(vehicle.base_price_XCD)));

    if (!Number.isFinite(nextUsd) || !Number.isFinite(nextXcd)) {
      setError("USD and XCD prices must be valid numbers.");
      return;
    }

    const showing = window.confirm("Click OK to keep this vehicle visible on the website.");

    await withFeedback(async () => {
      await updateVehicle(vehicle.id, {
        name: nextName.trim(),
        base_price_USD: nextUsd,
        base_price_XCD: nextXcd,
        showing
      });
      await loadAll();
    }, "Vehicle updated.");
  };

  const deleteVehicleHandler = async (vehicle: Vehicle) => {
    if (!window.confirm(`Delete ${vehicle.name}? This cannot be undone.`)) {
      return;
    }

    await withFeedback(async () => {
      await deleteVehicle(vehicle.id);
      await loadAll();
    }, "Vehicle deleted.");
  };

  const createAddOnHandler = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    await withFeedback(async () => {
      await createAddOn(addOnDraft);
      setAddOnDraft(addOnTemplate);
      await loadAll();
    }, "Add-on created.");
  };

  const editAddOnHandler = async (addOn: AddOn) => {
    const nextName = window.prompt("Add-on name", addOn.name);

    if (!nextName) {
      return;
    }

    const nextCostRaw = window.prompt("Cost (leave blank for null)", addOn.cost?.toString() ?? "");
    const nextCost =
      nextCostRaw === null || nextCostRaw.trim() === ""
        ? null
        : Number(nextCostRaw);

    if (nextCostRaw !== null && nextCost !== null && !Number.isFinite(nextCost)) {
      setError("Cost must be a valid number.");
      return;
    }

    await withFeedback(async () => {
      await updateAddOn(addOn.id, {
        name: nextName.trim(),
        cost: nextCost
      });
      await loadAll();
    }, "Add-on updated.");
  };

  const deleteAddOnHandler = async (addOn: AddOn) => {
    if (!window.confirm(`Delete add-on "${addOn.name}"?`)) {
      return;
    }

    await withFeedback(async () => {
      await deleteAddOn(addOn.id);
      await loadAll();
    }, "Add-on deleted.");
  };

  const createDiscountHandler = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    await withFeedback(async () => {
      await createVehicleDiscount(discountDraft);
      setDiscountDraft(discountTemplate);
      await loadAll();
    }, "Discount created.");
  };

  const deleteDiscountHandler = async (discount: VehicleDiscount) => {
    if (!window.confirm(`Delete discount #${discount.id}?`)) {
      return;
    }

    await withFeedback(async () => {
      await deleteVehicleDiscount(discount.id);
      await loadAll();
    }, "Discount deleted.");
  };

  const toggleOrderStatusHandler = async (order: OrderRequest) => {
    await withFeedback(async () => {
      await updateOrderStatus(order.id, !order.confirmed);
      await loadAll();
    }, `Order #${order.id} updated.`);
  };

  return (
    <div className="admin-shell">
      <header className="admin-topbar">
        <div>
          <h1>IBES Admin Dashboard</h1>
          <p>Signed in as {user.username}</p>
        </div>
        <div className="topbar-actions">
          <button type="button" onClick={loadAll} disabled={busy}>
            Refresh
          </button>
          <button type="button" className="danger" onClick={() => void onLogout()} disabled={busy}>
            Logout
          </button>
        </div>
      </header>

      <nav className="admin-nav">
        {(["overview", "vehicles", "addons", "discounts", "orders", "taxi"] as Section[]).map((entry) => (
          <button
            key={entry}
            type="button"
            className={entry === section ? "active" : ""}
            onClick={() => setSection(entry)}
          >
            {entry === "addons" ? "Add-Ons" : entry.charAt(0).toUpperCase() + entry.slice(1)}
          </button>
        ))}
      </nav>

      {feedback ? <div className="form-success">{feedback}</div> : null}
      {error ? <div className="form-error">{error}</div> : null}

      {!summary ? (
        <div className="panel">{busy ? "Loading admin data..." : <button onClick={loadAll}>Load Dashboard</button>}</div>
      ) : null}

      {summary && section === "overview" ? (
        <section className="panel stat-grid">
          <article>
            <h3>Vehicles</h3>
            <strong>{summary.vehicles_total}</strong>
            <p>{summary.vehicles_showing} visible on website</p>
          </article>
          <article>
            <h3>Add-Ons</h3>
            <strong>{summary.add_ons_total}</strong>
            <p>{summary.vehicle_discounts_total} discount rows</p>
          </article>
          <article>
            <h3>Orders</h3>
            <strong>{summary.order_requests_total}</strong>
            <p>{summary.order_requests_pending} pending</p>
          </article>
          <article>
            <h3>Revenue (USD)</h3>
            <strong>${summary.order_requests_revenue.toFixed(2)}</strong>
            <p>Based on order subtotal totals</p>
          </article>
          <article>
            <h3>Taxi Requests</h3>
            <strong>{summary.taxi_requests_total}</strong>
            <p>Latest inbound transfer requests</p>
          </article>
        </section>
      ) : null}

      {summary && section === "vehicles" ? (
        <section className="panel">
          <h2>Vehicles</h2>
          <form className="inline-form" onSubmit={createVehicleHandler}>
            <input
              placeholder="Name"
              value={vehicleDraft.name ?? ""}
              onChange={(event) => setVehicleDraft((prev) => ({ ...prev, name: event.target.value }))}
              required
            />
            <input
              placeholder="Type"
              value={vehicleDraft.type ?? ""}
              onChange={(event) => setVehicleDraft((prev) => ({ ...prev, type: event.target.value }))}
              required
            />
            <input
              placeholder="Slug"
              value={vehicleDraft.slug ?? ""}
              onChange={(event) => setVehicleDraft((prev) => ({ ...prev, slug: event.target.value }))}
              required
            />
            <input
              type="number"
              placeholder="USD"
              value={vehicleDraft.base_price_USD ?? 0}
              onChange={(event) => setVehicleDraft((prev) => ({ ...prev, base_price_USD: Number(event.target.value) }))}
              required
            />
            <input
              type="number"
              placeholder="XCD"
              value={vehicleDraft.base_price_XCD ?? 0}
              onChange={(event) => setVehicleDraft((prev) => ({ ...prev, base_price_XCD: Number(event.target.value) }))}
              required
            />
            <input
              type="number"
              placeholder="Seats"
              value={vehicleDraft.people ?? 4}
              onChange={(event) => setVehicleDraft((prev) => ({ ...prev, people: Number(event.target.value) }))}
              required
            />
            <input
              type="number"
              placeholder="Year"
              value={vehicleDraft.year ?? new Date().getFullYear()}
              onChange={(event) => setVehicleDraft((prev) => ({ ...prev, year: Number(event.target.value) }))}
              required
            />
            <button type="submit" disabled={busy}>Add Vehicle</button>
          </form>

          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Type</th>
                  <th>USD</th>
                  <th>Showing</th>
                  <th>Requests</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {sortedVehicles.map((vehicle) => (
                  <tr key={vehicle.id}>
                    <td>{vehicle.id}</td>
                    <td>{vehicle.name}</td>
                    <td>{vehicle.type}</td>
                    <td>${vehicle.base_price_USD}</td>
                    <td>{vehicle.showing ? "Yes" : "No"}</td>
                    <td>{vehicle.times_requested}</td>
                    <td className="actions">
                      <button type="button" onClick={() => void editVehicleHandler(vehicle)} disabled={busy}>
                        Edit
                      </button>
                      <button type="button" className="danger" onClick={() => void deleteVehicleHandler(vehicle)} disabled={busy}>
                        Delete
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      ) : null}

      {summary && section === "addons" ? (
        <section className="panel">
          <h2>Add-Ons</h2>
          <form className="inline-form" onSubmit={createAddOnHandler}>
            <input
              placeholder="Name"
              value={addOnDraft.name ?? ""}
              onChange={(event) => setAddOnDraft((prev) => ({ ...prev, name: event.target.value }))}
              required
            />
            <input
              placeholder="Abbreviation"
              value={addOnDraft.abbr ?? ""}
              onChange={(event) => setAddOnDraft((prev) => ({ ...prev, abbr: event.target.value }))}
              required
            />
            <input
              type="number"
              placeholder="Cost"
              value={addOnDraft.cost ?? 0}
              onChange={(event) => setAddOnDraft((prev) => ({ ...prev, cost: Number(event.target.value) }))}
            />
            <input
              placeholder="Description"
              value={addOnDraft.description ?? ""}
              onChange={(event) => setAddOnDraft((prev) => ({ ...prev, description: event.target.value }))}
              required
            />
            <label className="checkbox">
              <input
                type="checkbox"
                checked={Boolean(addOnDraft.fixed_price)}
                onChange={(event) => setAddOnDraft((prev) => ({ ...prev, fixed_price: event.target.checked }))}
              />
              Fixed Price
            </label>
            <button type="submit" disabled={busy}>Add Add-On</button>
          </form>

          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Cost</th>
                  <th>Fixed</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {addOns.map((addOn) => (
                  <tr key={addOn.id}>
                    <td>{addOn.id}</td>
                    <td>{addOn.name}</td>
                    <td>{addOn.cost ?? "-"}</td>
                    <td>{addOn.fixed_price ? "Yes" : "No"}</td>
                    <td className="actions">
                      <button type="button" onClick={() => void editAddOnHandler(addOn)} disabled={busy}>
                        Edit
                      </button>
                      <button type="button" className="danger" onClick={() => void deleteAddOnHandler(addOn)} disabled={busy}>
                        Delete
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      ) : null}

      {summary && section === "discounts" ? (
        <section className="panel">
          <h2>Vehicle Discounts</h2>
          <form className="inline-form" onSubmit={createDiscountHandler}>
            <select
              value={discountDraft.vehicle_id ?? 0}
              onChange={(event) => setDiscountDraft((prev) => ({ ...prev, vehicle_id: Number(event.target.value) }))}
              required
            >
              <option value={0}>Select vehicle...</option>
              {vehicles.map((vehicle) => (
                <option key={vehicle.id} value={vehicle.id}>
                  {vehicle.name}
                </option>
              ))}
            </select>
            <input
              type="number"
              placeholder="Days"
              value={discountDraft.days ?? 4}
              onChange={(event) => setDiscountDraft((prev) => ({ ...prev, days: Number(event.target.value) }))}
              required
            />
            <input
              type="number"
              placeholder="USD"
              value={discountDraft.price_USD ?? 0}
              onChange={(event) => setDiscountDraft((prev) => ({ ...prev, price_USD: Number(event.target.value) }))}
              required
            />
            <input
              type="number"
              placeholder="XCD"
              value={discountDraft.price_XCD ?? 0}
              onChange={(event) => setDiscountDraft((prev) => ({ ...prev, price_XCD: Number(event.target.value) }))}
              required
            />
            <button type="submit" disabled={busy}>Add Discount</button>
          </form>

          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Vehicle</th>
                  <th>Days</th>
                  <th>USD</th>
                  <th>XCD</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {discounts.map((discount) => (
                  <tr key={discount.id}>
                    <td>{discount.id}</td>
                    <td>{discount.vehicle?.name ?? `#${discount.vehicle_id}`}</td>
                    <td>{discount.days}</td>
                    <td>${discount.price_USD}</td>
                    <td>${discount.price_XCD}</td>
                    <td className="actions">
                      <button type="button" className="danger" onClick={() => void deleteDiscountHandler(discount)} disabled={busy}>
                        Delete
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      ) : null}

      {summary && section === "orders" ? (
        <section className="panel">
          <h2>Order Requests (Latest 50)</h2>
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Customer</th>
                  <th>Vehicle</th>
                  <th>Days</th>
                  <th>Subtotal</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {orders.map((order) => (
                  <tr key={order.id}>
                    <td>{order.id}</td>
                    <td>{order.contact_info ? `${order.contact_info.first_name} ${order.contact_info.last_name}` : "-"}</td>
                    <td>{order.vehicle?.name ?? "-"}</td>
                    <td>{order.days}</td>
                    <td>${order.sub_total.toFixed(2)}</td>
                    <td>{order.confirmed ? "Confirmed" : "Pending"}</td>
                    <td className="actions">
                      <button type="button" onClick={() => void toggleOrderStatusHandler(order)} disabled={busy}>
                        Mark {order.confirmed ? "Pending" : "Confirmed"}
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      ) : null}

      {summary && section === "taxi" ? (
        <section className="panel">
          <h2>Taxi Requests (Latest 50)</h2>
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Phone</th>
                  <th>Pickup</th>
                  <th>Dropoff</th>
                  <th>Time</th>
                  <th>Pax</th>
                </tr>
              </thead>
              <tbody>
                {taxiRequests.map((request) => (
                  <tr key={request.request_id}>
                    <td>{request.request_id}</td>
                    <td>{request.customer_name}</td>
                    <td>{request.customer_phone}</td>
                    <td>{request.pickup_location}</td>
                    <td>{request.dropoff_location}</td>
                    <td>{request.pickup_time}</td>
                    <td>{request.number_of_passengers}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      ) : null}
    </div>
  );
}
