import axios from "axios";

export type ApiResponse = {
  success?: boolean;
  message?: string;
  status?: number;
};

export async function postJson<T extends ApiResponse>(url: string, payload: unknown): Promise<T> {
  try {
    const response = await axios.post<T>(url, payload, {
      headers: { "Content-Type": "application/json" }
    });

    const data = response.data;

    if (data && data.success === false) {
      throw new Error(data.message || `Request failed with status ${response.status}`);
    }

    return (data ?? {}) as T;
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      const payloadData = error.response?.data as ApiResponse | undefined;
      throw new Error(payloadData?.message || error.message);
    }

    if (error instanceof Error) {
      throw error;
    }

    throw new Error("Request failed");
  }
}
