export type AdminUser = {
  id: number;
  username: string;
  role: string;
  active: boolean;
  last_login_at: string | null;
};

export type DashboardSummary = {
  vehicles_total: number;
  vehicles_showing: number;
  add_ons_total: number;
  vehicle_discounts_total: number;
  order_requests_total: number;
  order_requests_pending: number;
  order_requests_revenue: number;
  taxi_requests_total: number;
};

export type DashboardAnalyticsRange = "7d" | "30d" | "90d";

export type DashboardMetricCard = {
  value: number;
  change_pct: number;
};

export type DashboardAnalyticsPoint = {
  date: string;
  label: string;
  revenue_usd: number;
  order_requests: number;
  new_customers: number;
  active_vehicles: number;
  unique_visitors: number;
  mobile_visitors: number;
  desktop_visitors: number;
  page_views: number;
  growth_rate_pct: number;
};

export type DashboardAnalytics = {
  range: DashboardAnalyticsRange;
  start_date: string;
  end_date: string;
  generated_at: string;
  cards: {
    total_revenue: DashboardMetricCard;
    new_customers: DashboardMetricCard;
    current_vehicles: DashboardMetricCard;
    growth_rate: DashboardMetricCard;
  };
  chart: DashboardAnalyticsPoint[];
  table: DashboardAnalyticsPoint[];
};

export type Vehicle = {
  id: number;
  name: string;
  type: string;
  slug: string;
  showing: boolean;
  landing_order: number | null;
  base_price_XCD: number;
  base_price_USD: number;
  insurance: number;
  times_requested: number;
  people: number;
  bags: number | null;
  doors: number;
  four_wd: boolean;
  ac: boolean;
  manual: boolean;
  year: number;
  taxi: boolean;
  image_url: string;
};

export type AddOn = {
  id: number;
  name: string;
  cost: number | null;
  description: string;
  abbr: string;
  fixed_price: boolean;
};

export type VehicleDiscount = {
  id: number;
  vehicle_id: number;
  price_XCD: number;
  price_USD: number;
  days: number;
  vehicle?: {
    id: number;
    name: string;
    slug: string;
  };
};

export type ContactInfo = {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  hotel?: string | null;
  country_or_region?: string | null;
};

export type OrderRequestAddOn = {
  id: number;
  add_on_id: number;
  quantity: number;
  add_on: AddOn | null;
};

export type OrderRequest = {
  id: number;
  key: string;
  pick_up: string;
  drop_off: string;
  pick_up_location: string;
  drop_off_location: string;
  confirmed: boolean;
  sub_total: number;
  days: number;
  created_at: string | null;
  updated_at: string | null;
  vehicle: Pick<Vehicle, "id" | "name" | "slug" | "type"> | null;
  contact_info: ContactInfo | null;
  add_ons: OrderRequestAddOn[];
};

export type TaxiRequest = {
  request_id: number;
  customer_name: string;
  customer_phone: string;
  pickup_location: string;
  dropoff_location: string;
  pickup_time: string;
  number_of_passengers: number;
  special_requirements: string | null;
  created_at: string;
};

export type DashboardPageProps = {
  user: AdminUser;
  onLogout: () => Promise<void>;
};

export type Section = "overview" | "vehicles" | "addons" | "discounts" | "orders" | "taxi";

export type ConfirmDialogState = {
  open: boolean;
  title: string;
  description: string;
  action: (() => Promise<void>) | null;
};

export type PaginationMeta = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

export type LoadResourceOptions = {
  cacheKey?: string;
  readFromCache?: boolean;
  writeToCache?: boolean;
};

export type CachedResourceEnvelope<TResource> = {
  value: TResource;
  expiresAt: number;
};