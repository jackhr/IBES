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
use App\Controllers\VisitorAnalyticsController;
use App\Controllers\VehicleController;
use App\Controllers\VehicleDiscountController;
use App\Controllers\VehicleRequestController;
use App\Repositories\AddOnRepository;
use App\Repositories\BookingRepository;
use App\Repositories\TaxiRequestRepository;
use App\Repositories\VisitorAnalyticsRepository;
use App\Repositories\VehicleRepository;
use App\Services\AddOnService;
use App\Services\ContactService;
use App\Services\ContactInfoService;
use App\Services\OrderRequestService;
use App\Services\ReservationService;
use App\Services\TaxiService;
use App\Services\TaxiRequestService;
use App\Services\VisitorAnalyticsService;
use App\Services\VehicleDiscountService;
use App\Services\VehicleService;
use App\Services\VehicleRequestService;
use PDO;

final class ControllerFactory
{
    private static ?PDO $pdo = null;
    private static ?AddOnRepository $addOnRepository = null;
    private static ?BookingRepository $bookingRepository = null;
    private static ?TaxiRequestRepository $taxiRequestRepository = null;
    private static ?VisitorAnalyticsRepository $visitorAnalyticsRepository = null;
    private static ?VehicleRepository $vehicleRepository = null;

    public static function contactController(): ContactController
    {
        return new ContactController(new ContactService());
    }

    public static function addOnController(): AddOnController
    {
        return new AddOnController(new AddOnService(self::addOnRepository()));
    }

    public static function contactInfoController(): ContactInfoController
    {
        return new ContactInfoController(new ContactInfoService(self::bookingRepository()));
    }

    public static function orderRequestController(): OrderRequestController
    {
        return new OrderRequestController(new OrderRequestService(self::bookingRepository()));
    }

    public static function taxiController(): TaxiController
    {
        return new TaxiController(new TaxiService(self::taxiRequestRepository()));
    }

    public static function taxiRequestController(): TaxiRequestController
    {
        return new TaxiRequestController(new TaxiRequestService(self::taxiRequestRepository()));
    }

    public static function visitorAnalyticsController(): VisitorAnalyticsController
    {
        return new VisitorAnalyticsController(new VisitorAnalyticsService(self::visitorAnalyticsRepository()));
    }

    public static function reservationController(): ReservationController
    {
        return new ReservationController(
            new ReservationService(
                self::vehicleRepository(),
                self::addOnRepository()
            )
        );
    }

    public static function vehicleController(): VehicleController
    {
        return new VehicleController(new VehicleService(self::vehicleRepository()));
    }

    public static function vehicleDiscountController(): VehicleDiscountController
    {
        return new VehicleDiscountController(new VehicleDiscountService(self::vehicleRepository()));
    }

    public static function vehicleRequestController(): VehicleRequestController
    {
        return new VehicleRequestController(
            new VehicleRequestService(
                self::bookingRepository(),
                self::vehicleRepository()
            )
        );
    }

    private static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        self::$pdo = \Database::connection();

        return self::$pdo;
    }

    private static function addOnRepository(): AddOnRepository
    {
        if (self::$addOnRepository instanceof AddOnRepository) {
            return self::$addOnRepository;
        }

        self::$addOnRepository = new AddOnRepository(self::pdo());

        return self::$addOnRepository;
    }

    private static function bookingRepository(): BookingRepository
    {
        if (self::$bookingRepository instanceof BookingRepository) {
            return self::$bookingRepository;
        }

        self::$bookingRepository = new BookingRepository(self::pdo());

        return self::$bookingRepository;
    }

    private static function taxiRequestRepository(): TaxiRequestRepository
    {
        if (self::$taxiRequestRepository instanceof TaxiRequestRepository) {
            return self::$taxiRequestRepository;
        }

        self::$taxiRequestRepository = new TaxiRequestRepository(self::pdo());

        return self::$taxiRequestRepository;
    }

    private static function vehicleRepository(): VehicleRepository
    {
        if (self::$vehicleRepository instanceof VehicleRepository) {
            return self::$vehicleRepository;
        }

        self::$vehicleRepository = new VehicleRepository(self::pdo());

        return self::$vehicleRepository;
    }

    private static function visitorAnalyticsRepository(): VisitorAnalyticsRepository
    {
        if (self::$visitorAnalyticsRepository instanceof VisitorAnalyticsRepository) {
            return self::$visitorAnalyticsRepository;
        }

        self::$visitorAnalyticsRepository = new VisitorAnalyticsRepository(self::pdo());

        return self::$visitorAnalyticsRepository;
    }
}
