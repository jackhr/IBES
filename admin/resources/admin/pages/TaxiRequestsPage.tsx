import { Button } from "../components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../components/ui/card";
import DataTable from "../components/dashboard/DataTable";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "../components/ui/table";
import type { TaxiRequest } from "../types";

type TaxiRequestsPageProps = {
  taxiRequests: TaxiRequest[];
  busy: boolean;
  onOpenDetail: (request: TaxiRequest) => void;
  paginationLabel: string;
  currentPage: number;
  lastPage: number;
  canGoPrevious: boolean;
  canGoNext: boolean;
  onPreviousPage: () => void;
  onNextPage: () => void;
};

export default function TaxiRequestsPage({
  taxiRequests,
  busy,
  onOpenDetail,
  paginationLabel,
  currentPage,
  lastPage,
  canGoPrevious,
  canGoNext,
  onPreviousPage,
  onNextPage
}: TaxiRequestsPageProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Taxi Requests</CardTitle>
        <CardDescription>Paginated transfer requests from the public taxi form.</CardDescription>
      </CardHeader>
      <CardContent>
        <DataTable>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Phone</TableHead>
                <TableHead>Pickup</TableHead>
                <TableHead>Dropoff</TableHead>
                <TableHead>Time</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {taxiRequests.map((request) => (
                <TableRow
                  key={request.request_id}
                  role="button"
                  tabIndex={0}
                  className="cursor-pointer"
                  onClick={() => onOpenDetail(request)}
                  onKeyDown={(event) => {
                    if (event.key === "Enter" || event.key === " ") {
                      event.preventDefault();
                      onOpenDetail(request);
                    }
                  }}
                >
                  <TableCell>{request.customer_name}</TableCell>
                  <TableCell>{request.customer_phone}</TableCell>
                  <TableCell>{request.pickup_location}</TableCell>
                  <TableCell>{request.dropoff_location}</TableCell>
                  <TableCell>{request.pickup_time}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </DataTable>
        <div className="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
          <p className="text-muted-foreground">Showing {paginationLabel}</p>
          <div className="flex items-center gap-2">
            <Button type="button" variant="outline" size="sm" disabled={busy || !canGoPrevious} onClick={onPreviousPage}>
              Previous
            </Button>
            <span className="text-muted-foreground">
              Page {currentPage} of {Math.max(1, lastPage)}
            </span>
            <Button type="button" variant="outline" size="sm" disabled={busy || !canGoNext} onClick={onNextPage}>
              Next
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
