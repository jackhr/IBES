<?php

declare(strict_types=1);

namespace App\Support;

use App\Controllers\AddOnController;
use App\Controllers\ContactController;
use App\Controllers\ContactInfoController;
use App\Controllers\OrderRequestController;
use App\Controllers\ReservationController;
use App\Controllers\TaxiController;
use App\Controllers\TaxiRequestController;
use App\Controllers\VehicleController;
use App\Controllers\VehicleDiscountController;
use App\Controllers\VehicleRequestController;
use App\Repositories\RentalRepository;
use App\Services\AddOnService;
use App\Services\ContactService;
use App\Services\ContactInfoService;
use App\Services\OrderRequestService;
use App\Services\ReservationService;
use App\Services\TaxiService;
use App\Services\TaxiRequestService;
use App\Services\VehicleDiscountService;
use App\Services\VehicleService;
use App\Services\VehicleRequestService;

final class ControllerFactory
{
    private static ?RentalRepository $rentalRepository = null;

    public static function contactController(): ContactController
    {
        return new ContactController(new ContactService());
    }

    public static function addOnController(): AddOnController
    {
        return new AddOnController(new AddOnService(self::rentalRepository()));
    }

    public static function contactInfoController(): ContactInfoController
    {
        return new ContactInfoController(new ContactInfoService(self::rentalRepository()));
    }

    public static function orderRequestController(): OrderRequestController
    {
        return new OrderRequestController(new OrderRequestService(self::rentalRepository()));
    }

    public static function taxiController(): TaxiController
    {
        return new TaxiController(new TaxiService(self::rentalRepository()));
    }

    public static function taxiRequestController(): TaxiRequestController
    {
        return new TaxiRequestController(new TaxiRequestService(self::rentalRepository()));
    }

    public static function reservationController(): ReservationController
    {
        return new ReservationController(new ReservationService(self::rentalRepository()));
    }

    public static function vehicleController(): VehicleController
    {
        return new VehicleController(new VehicleService(self::rentalRepository()));
    }

    public static function vehicleDiscountController(): VehicleDiscountController
    {
        return new VehicleDiscountController(new VehicleDiscountService(self::rentalRepository()));
    }

    public static function vehicleRequestController(): VehicleRequestController
    {
        return new VehicleRequestController(new VehicleRequestService(self::rentalRepository()));
    }

    private static function rentalRepository(): RentalRepository
    {
        if (self::$rentalRepository instanceof RentalRepository) {
            return self::$rentalRepository;
        }

        self::$rentalRepository = new RentalRepository(\Database::connection());

        return self::$rentalRepository;
    }
}
