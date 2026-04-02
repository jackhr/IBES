import type {
  AddOn,
  ContactInfo,
  OrderRequest,
  TaxiRequest,
  Vehicle,
  VehicleDiscount
} from "../lib/api";

import rawAddOns from "../../mock-data/raw/add_ons.json";
import rawAdminApiTokens from "../../mock-data/raw/admin_api_tokens.json";
import rawAdminUsers from "../../mock-data/raw/admin_users.json";
import rawAnalyticsDailyMetrics from "../../mock-data/raw/analytics_daily_metrics.json";
import rawContactInfo from "../../mock-data/raw/contact_info.json";
import rawMigrations from "../../mock-data/raw/migrations.json";
import rawOrderRequestAddOns from "../../mock-data/raw/order_request_add_ons.json";
import rawOrderRequests from "../../mock-data/raw/order_requests.json";
import rawTaxiRequests from "../../mock-data/raw/taxi_requests.json";
import rawVehicleDiscounts from "../../mock-data/raw/vehicle_discounts.json";
import rawVehicles from "../../mock-data/raw/vehicles.json";
import rawVisitorPageViews from "../../mock-data/raw/visitor_page_views.json";
import rawVisitorSessions from "../../mock-data/raw/visitor_sessions.json";
import rawSummary from "../../mock-data/raw/_summary.json";

type RawAddOn = {
  id: number;
  name: string;
  cost: number | null;
  description: string;
  abbr: string;
  fixed_price: number | boolean;
};

type RawAdminUser = {
  id: number;
  username: string;
  password_hash: string;
  role: string;
  active: number | boolean;
  last_login_at: string | null;
  created_at: string | null;
  updated_at: string | null;
};

type RawAdminApiToken = {
  id: number;
  admin_user_id: number;
  token_hash: string;
  expires_at: string;
  last_used_at: string | null;
  created_at: string | null;
  updated_at: string | null;
};

type RawContactInfo = {
  id: number;
  first_name: string;
  last_name: string;
  driver_license: string | null;
  hotel: string | null;
  country_or_region: string | null;
  street: string | null;
  town_or_city: string | null;
  state_or_county: string | null;
  phone: string;
  email: string;
};

type RawAnalyticsDailyMetric = {
  id: number;
  snapshot_date: string;
  order_requests_count: number;
  new_customers_count: number;
  active_vehicles_count: number;
  revenue_usd: number;
  growth_rate_pct: number;
  unique_visitors_count: number;
  mobile_visitors_count: number;
  desktop_visitors_count: number;
  page_views_count: number;
  metadata: Record<string, unknown> | null;
  captured_at: string | null;
  created_at: string | null;
  updated_at: string | null;
};

type RawVisitorSession = {
  id: number;
  visitor_id: string;
  session_id: string;
  first_seen_at: string;
  last_seen_at: string;
  entry_path: string | null;
  entry_referrer: string | null;
  ip_address: string | null;
  user_agent: string | null;
  device_type: string;
  is_bot: number | boolean;
  os_name: string | null;
  browser_name: string | null;
  language: string | null;
  timezone: string | null;
  created_at: string | null;
  updated_at: string | null;
};

type RawVisitorPageView = {
  id: number;
  visitor_session_id: number | null;
  visitor_id: string;
  visited_at: string;
  route_path: string;
  full_url: string | null;
  query_string: string | null;
  referrer: string | null;
  user_agent: string | null;
  device_type: string;
  is_bot: number | boolean;
  os_name: string | null;
  browser_name: string | null;
  language: string | null;
  timezone: string | null;
  ip_address: string | null;
  viewport_width: number | null;
  viewport_height: number | null;
  screen_width: number | null;
  screen_height: number | null;
  event_type: string;
  metadata: Record<string, unknown> | null;
  created_at: string | null;
  updated_at: string | null;
};

type RawMigration = {
  id: number;
  migration: string;
  batch: number;
};

type RawOrderRequest = {
  id: number;
  key: string;
  pick_up: string;
  drop_off: string;
  pick_up_location: string;
  drop_off_location: string;
  confirmed: number | boolean;
  contact_info_id: number;
  sub_total: number;
  car_id: number;
  days: number;
  created_at: string | null;
  updated_at: string | null;
};

type RawOrderRequestAddOn = {
  id: number;
  order_request_id: number;
  add_on_id: number;
  quantity: number;
};

type RawTaxiRequest = {
  request_id: number;
  customer_name: string;
  customer_phone: string;
  pickup_location: string;
  dropoff_location: string;
  pickup_time: string;
  number_of_passengers: number;
  special_requirements: string | null;
  created_at: string | null;
};

type RawVehicle = {
  id: number;
  name: string;
  type: string;
  slug: string;
  image_filename?: string | null;
  showing: number | boolean;
  landing_order: number | null;
  base_price_XCD: number;
  base_price_USD: number;
  insurance: number;
  times_requested: number;
  people: number;
  bags: number | null;
  doors: number;
  "4wd": number | boolean;
  ac: number | boolean;
  manual: number | boolean;
  year: number;
  taxi: number | boolean;
};

type RawVehicleDiscount = {
  id: number;
  vehicle_id: number;
  price_XCD: number;
  price_USD: number;
  days: number;
};

type RawSummary = {
  source_sql: string;
  generated_at_utc: string;
  tables: Record<string, number>;
};

const toBoolean = (value: number | boolean | null | undefined): boolean => {
  return value === true || value === 1;
};

const rawAddOnsData = rawAddOns as RawAddOn[];
const rawAdminApiTokensData = rawAdminApiTokens as RawAdminApiToken[];
const rawAdminUsersData = rawAdminUsers as RawAdminUser[];
const rawAnalyticsDailyMetricsData = rawAnalyticsDailyMetrics as RawAnalyticsDailyMetric[];
const rawContactInfoData = rawContactInfo as RawContactInfo[];
const rawMigrationsData = rawMigrations as RawMigration[];
const rawOrderRequestsData = rawOrderRequests as RawOrderRequest[];
const rawOrderRequestAddOnsData = rawOrderRequestAddOns as RawOrderRequestAddOn[];
const rawTaxiRequestsData = rawTaxiRequests as RawTaxiRequest[];
const rawVehiclesData = rawVehicles as RawVehicle[];
const rawVehicleDiscountsData = rawVehicleDiscounts as RawVehicleDiscount[];
const rawVisitorSessionsData = rawVisitorSessions as RawVisitorSession[];
const rawVisitorPageViewsData = rawVisitorPageViews as RawVisitorPageView[];
const rawSummaryData = rawSummary as RawSummary;
const VEHICLE_IMAGE_PREFIX = "/gallery/";

const addOnIdsByOrderRequestId = rawOrderRequestAddOnsData.reduce<Map<number, number[]>>((accumulator, row) => {
  const current = accumulator.get(row.order_request_id) ?? [];
  const quantity = Math.max(1, Number(row.quantity || 0));

  for (let i = 0; i < quantity; i += 1) {
    current.push(row.add_on_id);
  }

  accumulator.set(row.order_request_id, current);
  return accumulator;
}, new Map<number, number[]>());

const maxDiscountDaysByVehicleId = rawVehicleDiscountsData.reduce<Map<number, number>>((accumulator, row) => {
  const currentMax = accumulator.get(row.vehicle_id) ?? 0;
  accumulator.set(row.vehicle_id, Math.max(currentMax, row.days));
  return accumulator;
}, new Map<number, number>());

export const mockAddOns: AddOn[] = rawAddOnsData.map((row) => {
  return {
    id: row.id,
    name: row.name,
    cost: row.cost,
    description: row.description,
    abbr: row.abbr,
    fixedPrice: toBoolean(row.fixed_price)
  };
});

export const mockContactInfo: ContactInfo[] = rawContactInfoData.map((row) => {
  return {
    id: row.id,
    firstName: row.first_name,
    lastName: row.last_name,
    driverLicense: row.driver_license,
    hotel: row.hotel,
    countryOrRegion: row.country_or_region,
    street: row.street,
    townOrCity: row.town_or_city,
    stateOrCounty: row.state_or_county,
    phone: row.phone,
    email: row.email
  };
});

export const mockOrderRequests: OrderRequest[] = rawOrderRequestsData.map((row) => {
  return {
    id: row.id,
    key: row.key,
    pickUp: row.pick_up,
    dropOff: row.drop_off,
    pickUpLocation: row.pick_up_location,
    dropOffLocation: row.drop_off_location,
    confirmed: toBoolean(row.confirmed),
    contactInfoId: row.contact_info_id,
    subTotal: row.sub_total,
    carId: row.car_id,
    days: row.days,
    createdAt: row.created_at,
    updatedAt: row.updated_at,
    addOnIds: addOnIdsByOrderRequestId.get(row.id) ?? []
  };
});

export const mockTaxiRequests: TaxiRequest[] = rawTaxiRequestsData.map((row) => {
  return {
    requestId: row.request_id,
    customerName: row.customer_name,
    customerPhone: row.customer_phone,
    pickupLocation: row.pickup_location,
    dropoffLocation: row.dropoff_location,
    pickupTime: row.pickup_time,
    numberOfPassengers: row.number_of_passengers,
    specialRequirements: row.special_requirements,
    createdAt: row.created_at
  };
});

export const mockVehicles: Vehicle[] = rawVehiclesData.map((row) => {
  return {
    id: row.id,
    name: row.name,
    type: row.type,
    slug: row.slug,
    showing: toBoolean(row.showing),
    landingOrder: row.landing_order,
    basePriceXcd: row.base_price_XCD,
    basePriceUsd: row.base_price_USD,
    insurance: row.insurance,
    timesRequested: row.times_requested,
    people: row.people,
    bags: row.bags,
    doors: row.doors,
    fourWd: toBoolean(row["4wd"]),
    ac: toBoolean(row.ac),
    manual: toBoolean(row.manual),
    year: row.year,
    taxi: toBoolean(row.taxi),
    imgSrc: `${VEHICLE_IMAGE_PREFIX}${row.image_filename ?? `${row.slug}.avif`}`,
    discountDays: maxDiscountDaysByVehicleId.get(row.id) ?? null
  };
});

export const mockVehicleDiscounts: VehicleDiscount[] = rawVehicleDiscountsData.map((row) => {
  return {
    id: row.id,
    vehicleId: row.vehicle_id,
    priceXcd: row.price_XCD,
    priceUsd: row.price_USD,
    days: row.days
  };
});

export const mockAdminUsers = rawAdminUsersData.map((row) => {
  return {
    id: row.id,
    username: row.username,
    passwordHash: row.password_hash,
    role: row.role,
    active: toBoolean(row.active),
    lastLoginAt: row.last_login_at,
    createdAt: row.created_at,
    updatedAt: row.updated_at
  };
});

export const mockAdminApiTokens = rawAdminApiTokensData;

export const mockMigrations = rawMigrationsData;

export const mockSummary = rawSummaryData;

export const mockAnalyticsDailyMetrics = rawAnalyticsDailyMetricsData;

export const mockVisitorSessions = rawVisitorSessionsData;

export const mockVisitorPageViews = rawVisitorPageViewsData;

export const mockRawTables = {
  add_ons: rawAddOnsData,
  admin_api_tokens: rawAdminApiTokensData,
  admin_users: rawAdminUsersData,
  analytics_daily_metrics: rawAnalyticsDailyMetricsData,
  contact_info: rawContactInfoData,
  migrations: rawMigrationsData,
  order_requests: rawOrderRequestsData,
  order_request_add_ons: rawOrderRequestAddOnsData,
  taxi_requests: rawTaxiRequestsData,
  visitor_sessions: rawVisitorSessionsData,
  visitor_page_views: rawVisitorPageViewsData,
  vehicles: rawVehiclesData,
  vehicle_discounts: rawVehicleDiscountsData
};

export const mockData = {
  addOns: mockAddOns,
  adminApiTokens: mockAdminApiTokens,
  adminUsers: mockAdminUsers,
  analyticsDailyMetrics: mockAnalyticsDailyMetrics,
  contactInfo: mockContactInfo,
  migrations: mockMigrations,
  orderRequests: mockOrderRequests,
  taxiRequests: mockTaxiRequests,
  visitorSessions: mockVisitorSessions,
  visitorPageViews: mockVisitorPageViews,
  vehicles: mockVehicles,
  vehicleDiscounts: mockVehicleDiscounts,
  summary: mockSummary,
  raw: mockRawTables
};
