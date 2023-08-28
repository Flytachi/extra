<?php

namespace Extra\Src;

use ArgumentCountError;
use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\HttpStatus;
use ReflectionException;
use ReflectionMethod;
use Throwable;
use TypeError;
use Warframe;

/**
 *  Warframe collection
 *
 *  Route - routing system
 *
 * 	@version 15.0
 * 	@author itachi
 * 	@package Extra\Src
 */
class Route
{
    /** @var bool $isApi api status */
    static bool $isApi = false;

    /**
     * Start routing system
     *
     * @return void
     */
    final static function start(): void
    {
        self::routeApp();
    }

    /**
     * Checking the post request
     *
     * @return void
     */
    final static function changePostSize(): void
    {
        if($_SERVER['REQUEST_METHOD'] === "POST" && intval($_SERVER['CONTENT_LENGTH']) > 0 && count($_POST) === 0) {
            self::Throwable(HttpCode::REQUEST_ENTITY_TOO_LARGE, 'PHP discarded POST data because of request exceeding post_max_size.');
        }
    }

    /**
     * Standard routing
     *
     * @return never
     */
    final static function routeApp(): never
    {
        $controllerName = ROUTE_MAIN_CONTROLLER;
        $actionName = ROUTE_MAIN_ACTION;
        $params = null;

        $data = self::urlToArray($_SERVER['REQUEST_URI']);
        $routes = explode('/', $data['url']);

        if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
        if ( $controllerName === 'Api' ) self::routeApi($data);
        if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
        if ( !empty($routes[3]) ) $params = array_slice($routes, 3);

        $_GET = $data['get'];
        self::changePostSize();

        // Prefix
        $controllerName = '\Controllers\\' . $controllerName . 'Controller';

        // Imports
        $funcPath = dirname(__DIR__, 2) . '/functions.php';
        if ( file_exists($funcPath) ) require $funcPath;

        // Imitation
        if(!class_exists($controllerName))
            self::Throwable(HttpCode::NOT_FOUND, 'The "' . $controllerName .'" controller was not found');
        try {
            self::imitation($controllerName, $actionName, $params);
        } catch (Throwable $e) {
            self::Throwable(HttpCode::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
        exit;
    }

    /**
     * Routing for api requests
     *
     * @param array $data
     *  * array[url] URL string
     *  * array[get] GET params array
     *
     * @return never
     */
    final static function routeApi(array $data): never
    {
        self::$isApi = true;
        if (!ROUTE_API_SYSTEM) self::Throwable(HttpCode::NOT_FOUND, 'Api system off');
        $routes = explode('/', $data['url']);
        $_GET = $data['get'];

        $controllerName = (!empty($routes[2])) ? ucfirst($routes[2]) : null;
        $actionName = (!empty($routes[3])) ? ucfirst($routes[3]) : null;
        if (!empty($routes[4])) $params = array_slice($routes, 4);
        else $params = null;

        // Prefix
        $controllerName = '\Apis\\' . $controllerName . 'Api';

        // Imports
        $funcPath = dirname(__DIR__, 2) . '/functions.php';
        if ( file_exists($funcPath) ) require $funcPath;

        // Imitation
        if(!class_exists($controllerName))
            self::Throwable(HttpCode::NOT_FOUND, 'The "' . $controllerName .'" controller was not found');
        try {
            self::imitation($controllerName, $actionName, $params);
        } catch (Throwable $e) {
            self::Throwable(HttpCode::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
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
     *
     * @throws ReflectionException
     */
    private static function imitation(string $controllerName, string $actionName, array|string|null $params): void
    {
        if (!method_exists($controllerName, $actionName))
            self::Throwable(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function was not found or is not a public method');

        $reflectionMethod = new ReflectionMethod($controllerName, $actionName);
        if ($reflectionMethod->isStatic())
            self::Throwable(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is static method');
        if ($reflectionMethod->isPrivate())
            self::Throwable(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is private method');
        if ($reflectionMethod->isProtected())
            self::Throwable(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is protected method');

        if (!is_array($params) && !is_null($params)) $params = [$params];

        try {
            $reflectionMethod->invokeArgs(new $controllerName(), $params ?? []);
        } catch (ArgumentCountError|TypeError $e) {
            self::Throwable(HttpCode::NOT_FOUND, $e->getMessage());
        }
    }

    /**
     * URL to array
     *
     * @param string $url url address and params
     *
     * @return array
     *  * @return array[url] URL string
     *  * @return array[get] GET params array
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
     * Change Session key 'id'
     *
     * @param bool|int $redirect status action redirect
     *
     * @return void
     */
    static function isAuth(bool $redirect = false): void
    {
        if ($redirect) {
            if (empty($_SESSION['id'])) self::redirect('auth/login');
        } else {
            if (empty($_SESSION['id'])) self::Throwable(HttpCode::LOCKED, 'You are not authorized');
        }
    }

    /**
     * Change Session key 'is_admin'
     *
     * @param bool|int $redirect status action redirect
     *
     * @return void
     */
    static function isAuthAdmin(bool|int $redirect = false): void
    {
        self::isAuth($redirect);
        if (empty($_SESSION['is_admin']) or $_SESSION['is_admin'] !== 1) self::Throwable(HttpCode::LOCKED, 'You are not logged in as an administrator');
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

        Logger::logging($httpCode->value, $_SERVER['REQUEST_URI'] . ' => ' . json_encode($data));
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
     * Throwable Warframe function
     *
     * If debugging is enabled, then it will show in detail where the error is located,
     * as well as output its own description of the error.
     *
     * If debugging is disabled it will return an error page with the specified code
     *
     * @param HttpCode $httpCode
     * @param string $title error description
     *
     * @return never
     */
    final static function Throwable(HttpCode $httpCode, string $title): never
    {
        $status = HttpStatus::status($httpCode);
        header("HTTP/1.1 {$httpCode->value} " . $status);
        header("Status: {$httpCode->value} " . $status);
        header_remove("X-Powered-By");

        if (self::$isApi) {
            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Headers: *");
            header("Access-Control-Allow-Methods: *");
            header("Content-Type: application/json");

            Logger::logging($httpCode->value, $_SERVER['REQUEST_URI'] . ' => ' . $title);
            $debug = self::debugApi();
            $message = self::getThrowableMessage($httpCode->value, $title, true);
            $debug['debug'] = [...$debug['debug'], ...['exception' => $message['body']]];
            echo json_encode([
                'statusCode' => $httpCode->value,
                'statusDescription' => $status,
                'data' => $title,
                ...$debug
            ]);

        } else {
            Logger::logging($httpCode->value, $_SERVER['REQUEST_URI'] . ' => ' . $title);

            if (Warframe::$env['DEBUG']) {
                $message = self::getThrowableMessage($httpCode->value, $title);
                echo $message['before'];
                echo "<strong style=\"font-size:16px; color: #ffffff;\"> Warframe Debug Message</strong><hr>";
                print_r($message['title']);
                print_r($message['body']);
                echo '<hr>' . $message['after'];
            } else {
                $page = PATH_RESOURCE . "/exception/{$httpCode->value}.php";
                if (file_exists($page)) die( include $page );
                else {
                    $_error = $httpCode->value . ' ' . $status;
                    die( include PATH_RESOURCE . '/exception/system.php' );
                }
            }
        }
        die;
    }

    /**
     * Throwable Message Warframe function
     *
     * @param int $httpCodeValue
     * @param string $title
     * @param bool $formatDetail
     *
     * @return array error message
     */
    private static function getThrowableMessage(int $httpCodeValue, string $title, bool $formatDetail = false): array
    {
        $tColor = match ((int)($httpCodeValue / 100)) {
            1 => "00ffff",
            2 => "00ff00",
            3 => "ff00e0",
            4 => "ffff00",
            5 => "ff0000",
            default => "dddddd",
        };

        if ($formatDetail) {
            $message = [];
            foreach (debug_backtrace() as $key => $value) {
                if ($key != 0) {
                    $ms = "#{$key}";
                    if (isset($value['file'])) $ms .= $value['file'];
                    if (isset($value['line'])) $ms .= ' (' . $value['line'] . '): ';
                    if (isset($value['class'])) $ms .= $value['class'];
                    if (isset($value['type'])) $ms .= $value['type'];
                    if (isset($value['function'])) $ms .= $value['function'];
                    $message[] = $ms;
                }
            }

            return [
                'title' => $title,
                'body' => $message,
            ];
        } else {
            $message = "";
            foreach (debug_backtrace() as $key => $value) {
                if ($key != 0) {
                    $message .= "\n\t\t#" . $key . ' ';
                    if (isset($value['file'])) $message .= $value['file'];
                    if (isset($value['line'])) $message .= ' (' . $value['line'] . '): ';
                    if (isset($value['class'])) $message .= "\t" . $value['class'];
                    if (isset($value['type'])) $message .= $value['type'];
                    if (isset($value['function'])) $message .= $value['function'];
                }
            }

            return [
                'before' => "<pre style=\"background-color: black; color: #{$tColor}; border-style: solid; border-color: #ff0000; border-width: medium; padding:7px; padding-top:13px\">",
                'title' => "\t <strong style=\"font-size:14px;\">" . $title . '</strong>',
                'body' => $message,
                'after' => "</pre>"
            ];
        }
    }

    private static function debugApi(): array
    {
        if (Warframe::$env['DEBUG']) {
            $delta = round(microtime(true)-$_SERVER['REQUEST_TIME'], 3);
            return [
                'debug' => [
                    'time' => ($delta < 0.001) ? 0.001 : $delta,
                    'date' => date(DATE_ATOM),
                    'timezone' => Warframe::$env['TIME_ZONE'],
                    'sapi' => PHP_SAPI,
                    'memory' => bytes(memory_get_usage(), 'MiB'),
                ]
            ];
        } else return [];
    }

}
