<?php

declare(strict_types=1);

namespace App\Core;

use App\Support\ControllerFactory;

final class ApiKernel
{
    public static function handle(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            JsonResponse::send([
                'success' => false,
                'message' => 'Not Found',
                'status' => 404,
                'data' => [],
            ], 404);

            return;
        }

        $routePath = self::normalizeRoutePath($path);

        $routes = [
            '/contact' => ControllerFactory::contactController(),
            '/contact-send' => ControllerFactory::contactController(),
            '/taxi-request' => ControllerFactory::taxiController(),
            '/taxi-request-send' => ControllerFactory::taxiController(),
            '/reservation' => ControllerFactory::reservationController(),
            '/vehicle-request' => ControllerFactory::vehicleRequestController(),
            '/vehicle-request-send' => ControllerFactory::vehicleRequestController(),
        ];

        if (!isset($routes[$routePath])) {
            JsonResponse::send([
                'success' => false,
                'message' => 'Not Found',
                'status' => 404,
                'data' => [
                    'route' => $routePath,
                ],
            ], 404);

            return;
        }

        if (strtoupper($method) !== 'POST') {
            JsonResponse::send([
                'success' => false,
                'message' => 'Method Not Allowed',
                'status' => 405,
                'data' => [
                    'method' => $method,
                    'allowed' => ['POST'],
                ],
            ], 405);

            return;
        }

        ($routes[$routePath])();
    }

    private static function normalizeRoutePath(string $requestPath): string
    {
        $path = preg_replace('#^/api#', '', $requestPath);
        $path = is_string($path) ? trim($path, '/') : '';

        return '/' . $path;
    }
}
