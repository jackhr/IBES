import {
  ArrowDownRight,
  ArrowUpRight,
  BadgeDollarSign,
  CarFront,
  ChartLine,
  UserRoundPlus
} from "lucide-react";
import type { ReactNode } from "react";
import {
  Area,
  AreaChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis
} from "recharts";

import type {
  DashboardAnalytics,
  DashboardAnalyticsPoint,
  DashboardAnalyticsRange,
  DashboardMetricCard
} from "../../types";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../ui/card";
import DataTable from "./DataTable";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "../ui/table";
import { Tabs, TabsList, TabsTrigger } from "../ui/tabs";

type AnalyticsOverviewProps = {
  analytics: DashboardAnalytics | null;
  range: DashboardAnalyticsRange;
  busy: boolean;
  onRangeChange: (range: DashboardAnalyticsRange) => void;
};

const RANGE_OPTIONS: { label: string; value: DashboardAnalyticsRange }[] = [
  { label: "Last 3 months", value: "90d" },
  { label: "Last month", value: "30d" },
  { label: "Last 7 days", value: "7d" }
];

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
}

function formatPercent(value: number): string {
  const absValue = Math.abs(value);
  return `${absValue.toFixed(1)}%`;
}

function ChangeBadge({ value }: { value: number }) {
  const positive = value >= 0;

  return (
    <span
      className={`inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium ${
        positive
          ? "border-emerald-200 bg-emerald-50 text-emerald-700"
          : "border-rose-200 bg-rose-50 text-rose-700"
      }`}
    >
      {positive ? <ArrowUpRight className="h-3.5 w-3.5" /> : <ArrowDownRight className="h-3.5 w-3.5" />}
      {formatPercent(value)}
    </span>
  );
}

function MetricCard({
  label,
  value,
  helper,
  icon,
  currency = false
}: {
  label: string;
  value: DashboardMetricCard;
  helper: string;
  icon: ReactNode;
  currency?: boolean;
}) {
  return (
    <Card className="border-border/70 shadow-sm">
      <CardHeader className="space-y-3 pb-2">
        <div className="flex items-start justify-between gap-3">
          <CardDescription className="text-sm font-medium">{label}</CardDescription>
          <ChangeBadge value={value.change_pct} />
        </div>
      </CardHeader>
      <CardContent className="space-y-2">
        <CardTitle className="text-4xl font-semibold tracking-tight">
          {currency ? formatCurrency(value.value) : label === "Growth Rate" ? `${value.value.toFixed(2)}%` : Math.round(value.value).toLocaleString()}
        </CardTitle>
        <div className="text-muted-foreground flex items-center gap-2 text-sm">
          {icon}
          <span>{helper}</span>
        </div>
      </CardContent>
    </Card>
  );
}

function ChartTooltip({
  active,
  payload,
  label
}: {
  active?: boolean;
  payload?: Array<{ value?: number | string; name?: string }>;
  label?: string;
}) {
  if (!active || !payload || payload.length === 0) {
    return null;
  }

  const uniqueVisitors = Number(payload.find((entry) => entry.name === "Unique Visitors")?.value ?? 0);
  const pageViews = Number(payload.find((entry) => entry.name === "Page Views")?.value ?? 0);
  const mobileVisitors = Number(payload.find((entry) => entry.name === "Mobile Visitors")?.value ?? 0);
  const desktopVisitors = Number(payload.find((entry) => entry.name === "Desktop Visitors")?.value ?? 0);

  return (
    <div className="rounded-lg border border-border/70 bg-card px-3 py-2 text-xs shadow-md">
      <p className="mb-1 font-semibold text-foreground">{label}</p>
      <p className="text-muted-foreground">Unique Visitors: {Math.round(uniqueVisitors)}</p>
      <p className="text-muted-foreground">Page Views: {Math.round(pageViews)}</p>
      <p className="text-muted-foreground">Mobile Visitors: {Math.round(mobileVisitors)}</p>
      <p className="text-muted-foreground">Desktop Visitors: {Math.round(desktopVisitors)}</p>
    </div>
  );
}

export default function AnalyticsOverview({ analytics, range, busy, onRangeChange }: AnalyticsOverviewProps) {
  const rows = analytics?.table ?? [];
  const chartRows = analytics?.chart ?? [];

  return (
    <div className="space-y-4">
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <MetricCard
          label="Total Revenue"
          value={analytics?.cards.total_revenue ?? { value: 0, change_pct: 0 }}
          helper="Booking subtotal over selected window"
          icon={<BadgeDollarSign className="h-4 w-4" />}
          currency
        />
        <MetricCard
          label="New Customers"
          value={analytics?.cards.new_customers ?? { value: 0, change_pct: 0 }}
          helper="Total order requests in the past 30 days"
          icon={<UserRoundPlus className="h-4 w-4" />}
        />
        <MetricCard
          label="Current Vehicles"
          value={analytics?.cards.current_vehicles ?? { value: 0, change_pct: 0 }}
          helper="Vehicles currently in use"
          icon={<CarFront className="h-4 w-4" />}
        />
        <MetricCard
          label="Growth Rate"
          value={analytics?.cards.growth_rate ?? { value: 0, change_pct: 0 }}
          helper="Revenue growth vs previous period"
          icon={<ChartLine className="h-4 w-4" />}
        />
      </div>

      <Card className="border-border/70 shadow-sm">
        <CardHeader className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <CardTitle>Total Visitors</CardTitle>
            <CardDescription>Total for the selected period.</CardDescription>
          </div>
          <Tabs
            value={range}
            onValueChange={(value) => onRangeChange(value as DashboardAnalyticsRange)}
            className="gap-0"
          >
            <TabsList className="h-10 p-1">
              {RANGE_OPTIONS.map((option) => (
                <TabsTrigger key={option.value} value={option.value} className="h-8 px-4" disabled={busy}>
                  {option.label}
                </TabsTrigger>
              ))}
            </TabsList>
          </Tabs>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="h-[320px] w-full">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={chartRows} margin={{ top: 8, right: 16, left: 0, bottom: 8 }}>
                <defs>
                  <linearGradient id="revenueGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="hsl(var(--primary))" stopOpacity={0.3} />
                    <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0.02} />
                  </linearGradient>
                  <linearGradient id="ordersGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="hsl(var(--foreground))" stopOpacity={0.18} />
                    <stop offset="95%" stopColor="hsl(var(--foreground))" stopOpacity={0.02} />
                  </linearGradient>
                </defs>
                <CartesianGrid vertical={false} strokeDasharray="3 3" />
                <XAxis dataKey="label" tickLine={false} axisLine={false} minTickGap={20} />
                <YAxis yAxisId="left" hide />
                <YAxis yAxisId="right" hide orientation="right" />
                <Tooltip content={<ChartTooltip />} />
                <Area
                  yAxisId="left"
                  type="monotone"
                  dataKey="unique_visitors"
                  name="Unique Visitors"
                  stroke="hsl(var(--primary))"
                  fill="url(#revenueGradient)"
                  strokeWidth={2.3}
                  dot={false}
                  activeDot={{ r: 4 }}
                />
                <Area
                  yAxisId="right"
                  type="monotone"
                  dataKey="page_views"
                  name="Page Views"
                  stroke="hsl(var(--foreground))"
                  fill="url(#ordersGradient)"
                  strokeWidth={1.8}
                  dot={false}
                  activeDot={{ r: 3 }}
                />
                <Area yAxisId="right" type="monotone" dataKey="mobile_visitors" name="Mobile Visitors" hide />
                <Area yAxisId="right" type="monotone" dataKey="desktop_visitors" name="Desktop Visitors" hide />
              </AreaChart>
            </ResponsiveContainer>
          </div>

          <DataTable>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Unique Visitors</TableHead>
                  <TableHead>Mobile</TableHead>
                  <TableHead>Desktop</TableHead>
                  <TableHead>Page Views</TableHead>
                  <TableHead>Revenue (USD)</TableHead>
                  <TableHead>Orders</TableHead>
                  <TableHead>Growth Rate</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {rows.map((row: DashboardAnalyticsPoint) => (
                  <TableRow key={row.date}>
                    <TableCell>{row.date}</TableCell>
                    <TableCell>{row.unique_visitors}</TableCell>
                    <TableCell>{row.mobile_visitors}</TableCell>
                    <TableCell>{row.desktop_visitors}</TableCell>
                    <TableCell>{row.page_views}</TableCell>
                    <TableCell>{formatCurrency(row.revenue_usd)}</TableCell>
                    <TableCell>{row.order_requests}</TableCell>
                    <TableCell>
                      <span
                        className={`font-medium ${row.growth_rate_pct >= 0 ? "text-emerald-700" : "text-rose-700"}`}
                      >
                        {row.growth_rate_pct.toFixed(2)}%
                      </span>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </DataTable>
        </CardContent>
      </Card>
    </div>
  );
}
