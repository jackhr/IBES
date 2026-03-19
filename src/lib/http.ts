export type ApiResponse = {
  success?: boolean;
  message?: string;
  status?: number;
};

export async function postJson<T extends ApiResponse>(url: string, payload: unknown): Promise<T> {
  const response = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });

  let data: T | null = null;

  try {
    data = (await response.json()) as T;
  } catch {
    data = null;
  }

  if (!response.ok || (data && data.success === false)) {
    throw new Error(data?.message || `Request failed with status ${response.status}`);
  }

  return (data ?? {}) as T;
}
