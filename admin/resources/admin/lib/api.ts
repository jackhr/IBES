import axios, { AxiosError } from "axios";
import type {
  AddOn,
  AdminUser,
  DashboardSummary,
  OrderRequest,
  TaxiRequest,
  Vehicle,
  VehicleDiscount
} from "../types";

type ApiEnvelope<T> = {
  success: boolean;
  message?: string;
  data: T;
};

type Paginated<T> = {
  items: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
};

export const ADMIN_TOKEN_KEY = "ibes_admin_token";

const api = axios.create({
  baseURL: "/api/admin",
  headers: {
    "Content-Type": "application/json"
  }
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem(ADMIN_TOKEN_KEY);

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

export function getApiErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const axiosError = error as AxiosError<{ message?: string }>;
    const message = axiosError.response?.data?.message;

    if (typeof message === "string" && message.trim() !== "") {
      return message;
    }
  }

  if (error instanceof Error && error.message.trim() !== "") {
    return error.message;
  }

  return "Request failed. Please try again.";
}

export async function adminLogin(username: string, password: string): Promise<{ token: string; user: AdminUser }> {
  const response = await api.post<ApiEnvelope<{ token: string; user: AdminUser }>>("/login", {
    username,
    password
  });

  return response.data.data;
}

export async function adminMe(): Promise<AdminUser> {
  const response = await api.get<ApiEnvelope<{ user: AdminUser }>>("/me");
  return response.data.data.user;
}

export async function adminLogout(): Promise<void> {
  await api.post("/logout");
}

export async function getDashboardSummary(): Promise<DashboardSummary> {
  const response = await api.get<ApiEnvelope<DashboardSummary>>("/dashboard/summary");
  return response.data.data;
}

export async function getVehicles(): Promise<Vehicle[]> {
  const response = await api.get<ApiEnvelope<Vehicle[]>>("/vehicles");
  return response.data.data;
}

export async function createVehicle(payload: Partial<Vehicle>): Promise<Vehicle> {
  const response = await api.post<ApiEnvelope<Vehicle>>("/vehicles", payload);
  return response.data.data;
}

export async function updateVehicle(id: number, payload: Partial<Vehicle>): Promise<Vehicle> {
  const response = await api.put<ApiEnvelope<Vehicle>>(`/vehicles/${id}`, payload);
  return response.data.data;
}

export async function deleteVehicle(id: number): Promise<void> {
  await api.delete(`/vehicles/${id}`);
}

export async function getAddOns(): Promise<AddOn[]> {
  const response = await api.get<ApiEnvelope<AddOn[]>>("/add-ons");
  return response.data.data;
}

export async function createAddOn(payload: Partial<AddOn>): Promise<AddOn> {
  const response = await api.post<ApiEnvelope<AddOn>>("/add-ons", payload);
  return response.data.data;
}

export async function updateAddOn(id: number, payload: Partial<AddOn>): Promise<AddOn> {
  const response = await api.put<ApiEnvelope<AddOn>>(`/add-ons/${id}`, payload);
  return response.data.data;
}

export async function deleteAddOn(id: number): Promise<void> {
  await api.delete(`/add-ons/${id}`);
}

export async function getVehicleDiscounts(): Promise<VehicleDiscount[]> {
  const response = await api.get<ApiEnvelope<VehicleDiscount[]>>("/vehicle-discounts");
  return response.data.data;
}

export async function createVehicleDiscount(payload: Partial<VehicleDiscount>): Promise<VehicleDiscount> {
  const response = await api.post<ApiEnvelope<VehicleDiscount>>("/vehicle-discounts", payload);
  return response.data.data;
}

export async function updateVehicleDiscount(id: number, payload: Partial<VehicleDiscount>): Promise<VehicleDiscount> {
  const response = await api.put<ApiEnvelope<VehicleDiscount>>(`/vehicle-discounts/${id}`, payload);
  return response.data.data;
}

export async function deleteVehicleDiscount(id: number): Promise<void> {
  await api.delete(`/vehicle-discounts/${id}`);
}

export async function getOrderRequests(params?: {
  per_page?: number;
  status?: "all" | "pending" | "confirmed";
  search?: string;
}): Promise<Paginated<OrderRequest>> {
  const response = await api.get<ApiEnvelope<Paginated<OrderRequest>>>("/order-requests", {
    params
  });

  return response.data.data;
}

export async function updateOrderStatus(id: number, confirmed: boolean): Promise<OrderRequest> {
  const response = await api.patch<ApiEnvelope<OrderRequest>>(`/order-requests/${id}/status`, {
    confirmed
  });

  return response.data.data;
}

export async function getTaxiRequests(params?: {
  per_page?: number;
  search?: string;
}): Promise<Paginated<TaxiRequest>> {
  const response = await api.get<ApiEnvelope<Paginated<TaxiRequest>>>("/taxi-requests", {
    params
  });

  return response.data.data;
}
