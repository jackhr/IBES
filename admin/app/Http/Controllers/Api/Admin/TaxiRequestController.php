<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxiRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 50);
        $search = trim((string) ($validated['search'] ?? ''));

        $query = TaxiRequest::query()->orderByDesc('request_id');

        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                $inner
                    ->where('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$search.'%')
                    ->orWhere('pickup_location', 'like', '%'.$search.'%')
                    ->orWhere('dropoff_location', 'like', '%'.$search.'%');
            });
        }

        $page = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $page->items(),
                'meta' => [
                    'current_page' => $page->currentPage(),
                    'last_page' => $page->lastPage(),
                    'per_page' => $page->perPage(),
                    'total' => $page->total(),
                ],
            ],
        ]);
    }
}
