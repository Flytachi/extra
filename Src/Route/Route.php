<?php

namespace Extra\Src\Route;

use ArgumentCountError;
use Extra\Src\Enum\HttpCode;
use Extra\Src\Error\ExtraException;
use Extra\Src\Log\Log;
use Extra\Src\Request\Request;
use ReflectionException;
use ReflectionMethod;
use TypeError;

/**
 *  Warframe collection
 *
 *  Route - routing system
 *
 * 	@version 19.0
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
     * @param string $url
     * @return string|null
     */
    private static function inGroup(string &$url): string|null
    {
        if (self::$groups) {
            foreach (self::$groups as $group => $folder) {
                if (str_starts_with($url, '/' . $group)) {
                    $url = str_replace('/' . $group, '', $url);
                    return $folder;
                }
            }
        }
        return null;
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
        $_GET = $data['get'];
        // self::changePostSize();

        $controllerName = '';
        $actionName = 'index';
        $params = null;

        $folder = self::inGroup($data['url']);
        $routes = explode('/', $data['url']);
        if ( !empty($routes[1]) ) $controllerName = ($folder
                ? (str_replace('/', '\\', $folder) . '\\')
                : '') . ucfirst($routes[1]);
        if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
        if ( !empty($routes[3]) ) $params = array_slice($routes, 3);
        $controllerName = '\Controllers\\' . $controllerName . 'Controller';

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
            } catch (ReflectionException|ArgumentCountError|TypeError $exception) {
                RouteError::throw(HttpCode::BAD_REQUEST,
                    $exception->getMessage() . ' in ' . $exception->getFile() . '(' . $exception->getLine() . ')'
                );
            }

        } catch (ExtraException $exception) {
            $code = HttpCode::tryFrom((int) $exception->getCode());
            RouteError::throw($code ?: HttpCode::INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception);
        } catch (\Throwable $exception) {
            RouteError::throw(HttpCode::INTERNAL_SERVER_ERROR, $exception->getMessage());
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
     * @param mixed $data data
     *
     * @return never
     */
    final static function ApiResponse(HttpCode $httpCode, mixed $data = null): never
    {
        $status = $httpCode->message();
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

    /**
     * Api Response Message
     *
     * @param HttpCode $httpCode
     * @param string $message
     *
     * @return never
     */
    final static function ApiResponseMessage(HttpCode $httpCode, string $message = ''): never
    {
        $status = $httpCode->message();
        header_remove("X-Powered-By");
        header("HTTP/1.1 {$httpCode->value} " . $status);
        header("Status: {$httpCode->value} " . $status);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        Log::trace($_SERVER['REQUEST_URI'] . ' => ' . json_encode($message));
        $debug = self::debugApi();
        echo json_encode([
            'statusCode' => $httpCode->value,
            'statusDescription' => $status,
            'message' => $message,
            ...$debug
        ]);
        die;
    }

    /**
     * Text Response
     *
     * @param HttpCode $httpCode
     * @param string $text
     * @param bool $htmlEntities
     *
     * @return never
     */
    final static function TextResponse(HttpCode $httpCode, string $text = '', bool $htmlEntities = false): never
    {
        $status = $httpCode->message();
        header_remove("X-Powered-By");
        header("HTTP/1.1 {$httpCode->value} " . $status);
        header("Status: {$httpCode->value} " . $status);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: text/plain");

        Log::trace($_SERVER['REQUEST_URI'] . ' => ' . json_encode($text));
        echo ($htmlEntities) ? htmlentities($text) : $text;
        die;
    }

    private static function debugApi(): array
    {
        if (env('DEBUG', false)) {
            $delta = round(microtime(true) - WARFRAME_STARTUP_TIME, 3);
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
