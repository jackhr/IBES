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
        $requestMethod = strtoupper($method);
        $routes = self::routes();

        $matchedPath = false;
        $allowedMethods = [];

        foreach ($routes as $route) {
            $params = self::matchRoutePattern($routePath, $route['path']);

            if ($params === null) {
                continue;
            }

            $matchedPath = true;
            $allowedMethods[] = $route['method'];

            if ($requestMethod !== $route['method']) {
                continue;
            }

            ($route['handler'])($params);

            return;
        }

        if (!$matchedPath) {
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

        $allowedMethods = array_values(array_unique($allowedMethods));
        sort($allowedMethods);

        JsonResponse::send([
            'success' => false,
            'message' => 'Method Not Allowed',
            'status' => 405,
            'data' => [
                'method' => $requestMethod,
                'allowed' => $allowedMethods,
            ],
        ], 405);
    }

    /** @return array<int, array{method: string, path: string, handler: callable(array<string, string>): void}> */
    private static function routes(): array
    {
        return [
            [
                'method' => 'GET',
                'path' => '/add-ons',
                'handler' => static function (array $_params): void {
                    ControllerFactory::addOnController()->index();
                },
            ],
            [
                'method' => 'GET',
                'path' => '/add-ons/{id}',
                'handler' => static function (array $params): void {
                    ControllerFactory::addOnController()->show((int) ($params['id'] ?? 0));
                },
            ],
            [
                'method' => 'POST',
                'path' => '/contact-info',
                'handler' => static function (array $_params): void {
                    ControllerFactory::contactInfoController()->store();
                },
            ],
            [
                'method' => 'GET',
                'path' => '/contact-info/{id}',
                'handler' => static function (array $params): void {
                    ControllerFactory::contactInfoController()->show((int) ($params['id'] ?? 0));
                },
            ],
            [
                'method' => 'POST',
                'path' => '/order-requests',
                'handler' => static function (array $_params): void {
                    ControllerFactory::orderRequestController()->store();
                },
            ],
            [
                'method' => 'GET',
                'path' => '/order-requests/{key}',
                'handler' => static function (array $params): void {
                    ControllerFactory::orderRequestController()->showByKey((string) ($params['key'] ?? ''));
                },
            ],
            [
                'method' => 'POST',
                'path' => '/taxi-requests',
                'handler' => static function (array $_params): void {
                    ControllerFactory::taxiRequestController()->store();
                },
            ],
            [
                'method' => 'GET',
                'path' => '/taxi-requests/{id}',
                'handler' => static function (array $params): void {
                    ControllerFactory::taxiRequestController()->show((int) ($params['id'] ?? 0));
                },
            ],
            [
                'method' => 'GET',
                'path' => '/vehicles',
                'handler' => static function (array $_params): void {
                    ControllerFactory::vehicleController()->index();
                },
            ],
            [
                'method' => 'GET',
                'path' => '/vehicles/landing',
                'handler' => static function (array $_params): void {
                    ControllerFactory::vehicleController()->landing();
                },
            ],
            [
                'method' => 'GET',
                'path' => '/vehicles/{id}',
                'handler' => static function (array $params): void {
                    ControllerFactory::vehicleController()->show((int) ($params['id'] ?? 0));
                },
            ],
            [
                'method' => 'GET',
                'path' => '/vehicle-discounts',
                'handler' => static function (array $_params): void {
                    ControllerFactory::vehicleDiscountController()->index();
                },
            ],
            [
                'method' => 'POST',
                'path' => '/contact',
                'handler' => static function (array $_params): void {
                    ControllerFactory::contactController()();
                },
            ],
            [
                'method' => 'POST',
                'path' => '/contact-send',
                'handler' => static function (array $_params): void {
                    ControllerFactory::contactController()();
                },
            ],
            [
                'method' => 'POST',
                'path' => '/taxi-request',
                'handler' => static function (array $_params): void {
                    ControllerFactory::taxiController()();
                },
            ],
            [
                'method' => 'POST',
                'path' => '/taxi-request-send',
                'handler' => static function (array $_params): void {
                    ControllerFactory::taxiController()();
                },
            ],
            [
                'method' => 'POST',
                'path' => '/reservation',
                'handler' => static function (array $_params): void {
                    ControllerFactory::reservationController()();
                },
            ],
            [
                'method' => 'POST',
                'path' => '/vehicle-request',
                'handler' => static function (array $_params): void {
                    ControllerFactory::vehicleRequestController()();
                },
            ],
            [
                'method' => 'POST',
                'path' => '/vehicle-request-send',
                'handler' => static function (array $_params): void {
                    ControllerFactory::vehicleRequestController()();
                },
            ],
        ];
    }

    /** @return array<string, string>|null */
    private static function matchRoutePattern(string $requestPath, string $routePattern): ?array
    {
        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static fn(array $match): string => '(?P<' . $match[1] . '>[^/]+)',
            $routePattern
        );

        if (!is_string($regex)) {
            return null;
        }

        $regex = '#^' . str_replace('/', '\\/', $regex) . '$#';

        if (preg_match($regex, $requestPath, $matches) !== 1) {
            return null;
        }

        $params = [];

        foreach ($matches as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $params[$key] = $value;
        }

        return $params;
    }

    private static function normalizeRoutePath(string $requestPath): string
    {
        $path = preg_replace('#^/api#', '', $requestPath);
        $path = is_string($path) ? trim($path, '/') : '';

        return '/' . $path;
    }
}
