import { CarFront, CircleDollarSign, ClipboardList, Tags } from "lucide-react";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../components/ui/card";
import type { DashboardSummary } from "../types";

type OverviewPageProps = {
  summary: DashboardSummary;
};

export default function OverviewPage({ summary }: OverviewPageProps) {
  return (
    <>
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <Card className="border-border/70 shadow-sm">
          <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
            <CardDescription>Total Vehicles</CardDescription>
            <CarFront className="text-muted-foreground h-4 w-4" />
          </CardHeader>
          <CardContent className="space-y-1">
            <CardTitle className="text-2xl">{summary.vehicles_total}</CardTitle>
            <p className="text-muted-foreground text-sm">{summary.vehicles_showing} visible on the website.</p>
          </CardContent>
        </Card>

        <Card className="border-border/70 shadow-sm">
          <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
            <CardDescription>Add-Ons & Discounts</CardDescription>
            <Tags className="text-muted-foreground h-4 w-4" />
          </CardHeader>
          <CardContent className="space-y-1">
            <CardTitle className="text-2xl">{summary.add_ons_total}</CardTitle>
            <p className="text-muted-foreground text-sm">{summary.vehicle_discounts_total} discount rows configured.</p>
          </CardContent>
        </Card>

        <Card className="border-border/70 shadow-sm">
          <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
            <CardDescription>Order Requests</CardDescription>
            <ClipboardList className="text-muted-foreground h-4 w-4" />
          </CardHeader>
          <CardContent className="space-y-1">
            <CardTitle className="text-2xl">{summary.order_requests_total}</CardTitle>
            <p className="text-muted-foreground text-sm">{summary.order_requests_pending} pending confirmations.</p>
          </CardContent>
        </Card>

        <Card className="border-border/70 shadow-sm">
          <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
            <CardDescription>Revenue (USD)</CardDescription>
            <CircleDollarSign className="text-muted-foreground h-4 w-4" />
          </CardHeader>
          <CardContent className="space-y-1">
            <CardTitle className="text-2xl">${summary.order_requests_revenue.toFixed(2)}</CardTitle>
            <p className="text-muted-foreground text-sm">Order subtotal aggregate.</p>
          </CardContent>
        </Card>
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
    </>
  );
}
