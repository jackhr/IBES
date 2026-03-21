<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'status' => ['nullable', 'in:all,pending,confirmed'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 50);
        $status = (string) ($validated['status'] ?? 'all');
        $search = trim((string) ($validated['search'] ?? ''));

        $query = OrderRequest::query()
            ->with([
                'vehicle:id,name,slug,type',
                'contactInfo:id,first_name,last_name,email,phone,hotel,country_or_region',
                'addOnLinks.addOn:id,name,cost,fixed_price',
            ])
            ->orderByDesc('id');

        if ($status === 'pending') {
            $query->where('confirmed', 0);
        } elseif ($status === 'confirmed') {
            $query->where('confirmed', 1);
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                $inner->where('key', 'like', '%'.$search.'%')
                    ->orWhereHas('contactInfo', function ($contactQuery) use ($search): void {
                        $contactQuery
                            ->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search): void {
                        $vehicleQuery->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        $page = $query->paginate($perPage);

        $items = $page->getCollection()
            ->map(fn (OrderRequest $order): array => $this->toPayload($order))
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'meta' => [
                    'current_page' => $page->currentPage(),
                    'last_page' => $page->lastPage(),
                    'per_page' => $page->perPage(),
                    'total' => $page->total(),
                ],
            ],
        ]);
    }

    public function show(OrderRequest $orderRequest): JsonResponse
    {
        $orderRequest->load([
            'vehicle:id,name,slug,type',
            'contactInfo:id,first_name,last_name,email,phone,hotel,country_or_region,street,town_or_city,state_or_county,driver_license',
            'addOnLinks.addOn:id,name,cost,fixed_price',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->toPayload($orderRequest),
        ]);
    }

    public function updateStatus(Request $request, OrderRequest $orderRequest): JsonResponse
    {
        $validated = $request->validate([
            'confirmed' => ['required', 'boolean'],
        ]);

        $orderRequest->forceFill([
            'confirmed' => (bool) $validated['confirmed'],
        ])->save();

        $orderRequest->load([
            'vehicle:id,name,slug,type',
            'contactInfo:id,first_name,last_name,email,phone,hotel,country_or_region',
            'addOnLinks.addOn:id,name,cost,fixed_price',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order request status updated.',
            'data' => $this->toPayload($orderRequest),
        ]);
    }

    private function toPayload(OrderRequest $order): array
    {
        return [
            'id' => $order->id,
            'key' => $order->key,
            'pick_up' => $order->pick_up,
            'drop_off' => $order->drop_off,
            'pick_up_location' => $order->pick_up_location,
            'drop_off_location' => $order->drop_off_location,
            'confirmed' => (bool) $order->confirmed,
            'sub_total' => (float) $order->sub_total,
            'days' => (int) $order->days,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'vehicle' => $order->vehicle,
            'contact_info' => $order->contactInfo,
            'add_ons' => $order->addOnLinks->map(static function ($link): array {
                return [
                    'id' => $link->id,
                    'add_on_id' => $link->add_on_id,
                    'quantity' => $link->quantity,
                    'add_on' => $link->addOn,
                ];
            })->values()->all(),
        ];
    }
}
