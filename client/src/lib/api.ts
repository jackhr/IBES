import axios, { AxiosResponse } from "axios";

export type ApiEnvelope<TData> = {
  success?: boolean;
  message?: string;
  status?: number;
  data: TData;
};

export type AddOn = {
  id: number;
  name: string;
  cost: number | null;
  description: string;
  abbr: string;
  fixedPrice: boolean;
};

export type ContactInfo = {
  id: number;
  firstName: string;
  lastName: string;
  driverLicense: string | null;
  hotel: string | null;
  countryOrRegion: string | null;
  street: string | null;
  townOrCity: string | null;
  stateOrCounty: string | null;
  phone: string;
  email: string;
};

export type OrderRequest = {
  id: number;
  key: string;
  pickUp: string;
  dropOff: string;
  pickUpLocation: string;
  dropOffLocation: string;
  confirmed: boolean;
  contactInfoId: number;
  subTotal: number;
  carId: number;
  days: number;
  createdAt: string | null;
  updatedAt: string | null;
  addOnIds?: number[];
};

export type TaxiRequest = {
  requestId: number;
  customerName: string;
  customerPhone: string;
  pickupLocation: string;
  dropoffLocation: string;
  pickupTime: string;
  numberOfPassengers: number;
  specialRequirements: string | null;
  createdAt: string | null;
};

export type Vehicle = {
  id: number;
  name: string;
  type: string;
  slug: string;
  showing: boolean;
  landingOrder: number | null;
  basePriceXcd: number;
  basePriceUsd: number;
  insurance: number;
  timesRequested: number;
  people: number;
  bags: number | null;
  doors: number;
  fourWd: boolean;
  ac: boolean;
  manual: boolean;
  year: number;
  taxi: boolean;
  imgSrc: string;
  discountDays?: number | null;
};

export type VehicleDiscount = {
  id: number;
  vehicleId: number;
  priceXcd: number;
  priceUsd: number;
  days: number;
};

const api = axios.create({
  headers: {
    "Content-Type": "application/json"
  }
});

function unwrap<TData>(response: AxiosResponse<ApiEnvelope<TData>>): TData {
  const payload = response.data;

  if (payload?.success === false) {
    throw new Error(payload.message || "Request failed");
  }

  return payload.data;
}

export async function listAddOns(): Promise<AddOn[]> {
  const response = await api.get<ApiEnvelope<{ addOns: AddOn[] }>>("/api/add-ons");
  return unwrap(response).addOns;
}

export async function getAddOn(id: number): Promise<AddOn> {
  const response = await api.get<ApiEnvelope<{ addOn: AddOn }>>(`/api/add-ons/${id}`);
  return unwrap(response).addOn;
}

export async function createContactInfo(payload: Partial<ContactInfo>): Promise<ContactInfo> {
  const response = await api.post<ApiEnvelope<{ contactInfo: ContactInfo }>>("/api/contact-info", payload);
  return unwrap(response).contactInfo;
}

export async function getContactInfo(id: number): Promise<ContactInfo> {
  const response = await api.get<ApiEnvelope<{ contactInfo: ContactInfo }>>(`/api/contact-info/${id}`);
  return unwrap(response).contactInfo;
}

export async function createOrderRequest(payload: Record<string, unknown>): Promise<OrderRequest> {
  const response = await api.post<ApiEnvelope<{ orderRequest: OrderRequest }>>("/api/order-requests", payload);
  return unwrap(response).orderRequest;
}

export async function getOrderRequestByKey(key: string): Promise<OrderRequest> {
  const response = await api.get<ApiEnvelope<{ orderRequest: OrderRequest }>>(`/api/order-requests/${encodeURIComponent(key)}`);
  return unwrap(response).orderRequest;
}

export async function createTaxiRequest(payload: Record<string, unknown>): Promise<TaxiRequest> {
  const response = await api.post<ApiEnvelope<{ taxiRequest: TaxiRequest }>>("/api/taxi-requests", payload);
  return unwrap(response).taxiRequest;
}

export async function getTaxiRequest(id: number): Promise<TaxiRequest> {
  const response = await api.get<ApiEnvelope<{ taxiRequest: TaxiRequest }>>(`/api/taxi-requests/${id}`);
  return unwrap(response).taxiRequest;
}

export async function listVehicles(showingOnly = false): Promise<Vehicle[]> {
  const response = await api.get<ApiEnvelope<{ vehicles: Vehicle[] }>>("/api/vehicles", {
    params: {
      ...(showingOnly ? { showing: true } : {})
    }
  });

  return unwrap(response).vehicles;
}

export async function getLandingVehicles(): Promise<Vehicle[]> {
  const response = await api.get<ApiEnvelope<{ vehicles: Vehicle[] }>>("/api/vehicles/landing");
  return unwrap(response).vehicles;
}

export async function getVehicle(id: number): Promise<Vehicle> {
  const response = await api.get<ApiEnvelope<{ vehicle: Vehicle }>>(`/api/vehicles/${id}`);
  return unwrap(response).vehicle;
}

export async function listVehicleDiscounts(vehicleId?: number): Promise<VehicleDiscount[]> {
  const response = await api.get<ApiEnvelope<{ vehicleDiscounts: VehicleDiscount[] }>>("/api/vehicle-discounts", {
    params: {
      ...(typeof vehicleId === "number" ? { vehicleId } : {})
    }
  });

  return unwrap(response).vehicleDiscounts;
}

export async function getBestVehicleDiscount(vehicleId: number, days: number): Promise<VehicleDiscount> {
  const response = await api.get<ApiEnvelope<{ vehicleDiscount: VehicleDiscount }>>("/api/vehicle-discounts", {
    params: {
      vehicleId,
      days
    }
  });

  return unwrap(response).vehicleDiscount;
}
