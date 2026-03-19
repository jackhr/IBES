<?php

declare(strict_types=1);

namespace App\Support;

use App\Controllers\ContactController;
use App\Controllers\ReservationController;
use App\Controllers\TaxiController;
use App\Controllers\VehicleRequestController;
use App\Repositories\RentalRepository;
use App\Services\ContactService;
use App\Services\ReservationService;
use App\Services\TaxiService;
use App\Services\VehicleRequestService;

final class ControllerFactory
{
    public static function contactController(): ContactController
    {
        return new ContactController(new ContactService());
    }

    public static function taxiController(): TaxiController
    {
        return new TaxiController(new TaxiService(self::rentalRepository()));
    }

    public static function reservationController(): ReservationController
    {
        return new ReservationController(new ReservationService(self::rentalRepository()));
    }

    public static function vehicleRequestController(): VehicleRequestController
    {
        return new VehicleRequestController(new VehicleRequestService(self::rentalRepository()));
    }

    private static function rentalRepository(): RentalRepository
    {
        return new RentalRepository(\Database::connection());
    }
}
