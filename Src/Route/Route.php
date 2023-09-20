<?php

namespace Extra\Src\Route;

use ArgumentCountError;
use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\HttpStatus;
use Extra\Src\Enum\Request;
use Extra\Src\Log\Log;
use ReflectionException;
use ReflectionMethod;
use TypeError;

/**
 *  Warframe collection
 *
 *  Route - routing system
 *
 * 	@version 17.0
 * 	@author itachi
 * 	@package Extra\Src
 */
class Route
{
    private static array $groups = [];

    public static function group(string $prefix, string $folder): void
    {
        self::$groups[$prefix] = $folder;
    }

    /**
     * Start routing system
     *
     * @return void
     */
    final static function start(): void
    {
        Request::setHeaders();
        self::route();
    }

    /**
     * Checking the post request
     *
     * @return void
     */
    final static function changePostSize(): void
    {
        if($_SERVER['REQUEST_METHOD'] === "POST" && intval($_SERVER['CONTENT_LENGTH']) > 0 && count($_POST) === 0)
            RouteError::throw(HttpCode::REQUEST_ENTITY_TOO_LARGE, 'PHP discarded POST data because of request exceeding post_max_size.');
    }

    /**
     * Routing
     *
     * @return never
     */
    final static function route(): never
    {
        Log::trace("Route uri:" . $_SERVER['REQUEST_URI']);
        $data = self::urlToArray($_SERVER['REQUEST_URI']);
        $routes = explode('/', $data['url']);

        $_GET = $data['get'];
        self::changePostSize();

        $controllerName = '';
        $actionName = 'index';
        $params = null;

        if (array_key_exists($routes[1], self::$groups)) {
            if ( !empty($routes[2]) ) $controllerName = ucfirst($routes[2]);
            if ( !empty($routes[3]) ) $actionName = ucfirst($routes[3]);
            if ( !empty($routes[4]) ) $params = array_slice($routes, 4);
            $controllerName = '\Controllers\\' . self::$groups[$routes[1]] . '\\' . $controllerName . 'Controller';
        } else {
            if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
            if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
            if ( !empty($routes[3]) ) $params = array_slice($routes, 3);
            $controllerName = '\Controllers\\' . $controllerName . 'Controller';
        }

        // Imports
        $funcPath = dirname(__DIR__, 2) . '/Config/functions.php';
        if ( file_exists($funcPath) ) require $funcPath;

        // Imitation
        if(!class_exists($controllerName ?? ''))
            RouteError::throw(HttpCode::NOT_FOUND, 'The "' . $controllerName .'" controller was not found');

        self::imitation($controllerName, $actionName, $params);
        exit;
    }


    /**
     * Start imitation controller
     *
     * @param string $controllerName controller name
     * @param string $actionName controller public method
     * @param array|string|null $params method params
     *
     * @return void
     */
    private static function imitation(string $controllerName, string $actionName, array|string|null $params): void
    {
        if (!method_exists($controllerName, $actionName))
            RouteError::throw(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function was not found or is not a public method');

        try {
            $reflectionMethod = new ReflectionMethod($controllerName, $actionName);
        } catch (ReflectionException $err) {
            RouteError::throw(HttpCode::INTERNAL_SERVER_ERROR, $err->getMessage());
        }

        if ($reflectionMethod->isStatic())
            RouteError::throw(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is static method');
        if ($reflectionMethod->isPrivate())
            RouteError::throw(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is private method');
        if ($reflectionMethod->isProtected())
            RouteError::throw(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is protected method');

        if (!is_array($params) && !is_null($params)) $params = [$params];

        Log::trace("Route imitation:" . $controllerName);
        try {
            $reflectionMethod->invokeArgs(new $controllerName(), $params ?? []);
        } catch (ReflectionException|ArgumentCountError|TypeError $err) {
            RouteError::throw(HttpCode::NOT_FOUND,
                $err->getMessage() . ' in ' . $err->getFile() . '(' . $err->getLine() . ')'
            );
        }
    }

    /**
     * URL to array
     *
     * @param string $url url address and params
     *
     * @return array<string, string|array>
     */
    final static function urlToArray(string $url): array
    {
        $code = explode('?', urldecode($url));
        $result = [];
        if (isset($code[1])) {
            foreach (explode('&', $code[1]) as $param) {
                if ($param) {
                    $value = explode('=', $param);
                    $result[$value[0]] = $value[1];
                }
            }
        }
        return ['url' => $code[0], 'get' => $result];
    }

    /**
     * Redirect
     *
     * @param ?string $url /url address
     * @param ?array $param get parameters
     *
     * @return never
     */
    final static function redirect(?string $url = null, ?array $param = null): never
    {
        if ($url) header('location: /' . $url . arrayToRequest($param));
        else header('location:' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    /**
     * Api Response
     *
     * @param HttpCode $httpCode
     * @param mixed $data message
     *
     * @return never
     */
    final static function ApiResponse(HttpCode $httpCode, mixed $data = null): never
    {
        $status = HttpStatus::status($httpCode);
        header_remove("X-Powered-By");
        header("HTTP/1.1 {$httpCode->value} " . $status);
        header("Status: {$httpCode->value} " . $status);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        Log::trace($_SERVER['REQUEST_URI'] . ' => ' . json_encode($data));
        $debug = self::debugApi();
        echo json_encode([
            'statusCode' => $httpCode->value,
            'statusDescription' => $status,
            'data' => $data,
            ...$debug
        ]);
        die;
    }

    private static function debugApi(): array
    {
        if (env('DEBUG', false)) {
            $delta = round(microtime(true)-$_SERVER['REQUEST_TIME'], 3);
            return [
                'debug' => [
                    'time' => ($delta < 0.001) ? 0.001 : $delta,
                    'date' => date(DATE_ATOM),
                    'timezone' => env('TIME_ZONE', 'UTC'),
                    'sapi' => PHP_SAPI,
                    'memory' => bytes(memory_get_usage(), 'MiB'),
                ]
            ];
        } else return [];
    }

}
