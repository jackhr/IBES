<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\AddOnService;
use Throwable;

final class AddOnController extends ApiController
{
    public function __construct(private AddOnService $addOnService)
    {
    }

    public function index()
    {
        try {
            return $this->success([
                'addOns' => $this->addOnService->list(),
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $addOn = $this->addOnService->find($id);

            if ($addOn === null) {
                return $this->error('Add-on not found.', 404);
            }

            return $this->success([
                'addOn' => $addOn,
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
