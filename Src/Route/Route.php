<?php

namespace Extra\Src\Route;

use Extra\Src\Artefact\ArtefactError;
use Extra\Src\Artefact\CDO\CDOError;
use Extra\Src\Entity\Request\Request;
use Extra\Src\Error\ExtraException;
use Extra\Src\HttpCode;
use Extra\Src\Log\Log;
use Extra\Src\Repo\RepositoryError;
use Extra\Src\Sheath\SheathException;
use ReflectionException;
use ReflectionMethod;
use TypeError;

/**
 * Class Route
 *
 * `Route` is the class responsible for routing in the system. It uses static methods to process URL routes,
 * groups, options, and performs URL redirections as needed.
 *
 * The methods provided by `Route` include:
 *
 * - `group(string $prefix, string $folder): void`: Used to define a grouping of routes with a common prefix and an associated folder.
 * - `inGroup(string &$url): string|null`: Used to check if a provided URL path has any group specified by the `group` function.
 * - `start(): void`: Begins the routing process.
 * - `changePostSize(): void`: Checks the posted request size and throws an exception if it's too large.
 * - `route(): never`: Resolves the routes and directs to the account controller.
 * - `imitation(string $controllerName, string $actionName, array|string|null $params): void`: Simulates opening the specific controller with the specified method.
 *
 * @version 19.7
 * @author Flytachi
 */
class Route
{
    private static array $groups = [];

    /**
     * Add a group to the routing
     *
     * @param string $prefix The group prefix
     * @param string $folder The folder to associate with the group
     * @return void
     */
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
     * This method starts the application by setting the headers for the incoming request and routing it to the appropriate controller and action.
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
            RouteError::throw(HttpCode::REQUEST_ENTITY_TOO_LARGE, 'PHP discarded POST data because of request exceeding post_max_size');
    }

    /**
     * This method handles the routing of incoming requests to the appropriate controller and action.
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
     * This method imitates the execution of a controller action.
     *
     * @param string $controllerName The name of the controller class.
     * @param string $actionName The name of the action method.
     * @param array|string|null $params The parameters to pass to the action method (optional).
     * @return void
     */
    private static function imitation(string $controllerName, string $actionName, array|string|null $params): void
    {
        try {
            $reflectionMethod = new ReflectionMethod($controllerName, $actionName);

            if (env('DEBUG')) {
                if ($reflectionMethod->isStatic())
                    RouteError::throw(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is static method');
                if ($reflectionMethod->isPrivate())
                    RouteError::throw(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is private method');
                if ($reflectionMethod->isProtected())
                    RouteError::throw(HttpCode::NOT_FOUND, 'The "' . $actionName . '" function is protected method');
            } else {
                if (!$reflectionMethod->isPublic() || $reflectionMethod->isStatic())
                    RouteError::throw(HttpCode::NOT_FOUND,  $_SERVER['REQUEST_URI'] . ' url not found');
            }

            if (!is_array($params) && !is_null($params)) $params = [$params];
            Log::trace("Route imitation:" . $controllerName);
            try {
                $reflectionMethod->invokeArgs(new $controllerName(), $params ?? []);
            } catch (TypeError $exception) {
                RouteError::throw(HttpCode::BAD_REQUEST, (env('DEBUG'))
                    ? $exception->getMessage() . ' in ' . $exception->getFile() . '(' . $exception->getLine() . ')'
                    : 'Invalid argument data'
                );
            }

        } catch (ReflectionException $exception) {
            RouteError::throw(HttpCode::NOT_FOUND,(env('DEBUG'))
                ? $exception->getMessage() . ' in ' . $exception->getFile() . '(' . $exception->getLine() . ')'
                : $_SERVER['REQUEST_URI'] . ' url not found',
                $exception
            );
        } catch (CDOError|RepositoryError|ArtefactError|SheathException $exception) {
            RouteError::throw(HttpCode::INTERNAL_SERVER_ERROR, (env('DEBUG'))
                ? $exception->getMessage()
                : 'Server error',
                $exception
            );
        } catch (ExtraException $exception) {
            $code = HttpCode::tryFrom((int) $exception->getCode());
            RouteError::throw($code ?: HttpCode::INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception);
        } catch (\Throwable $exception) {
            RouteError::throw(HttpCode::INTERNAL_SERVER_ERROR, (env('DEBUG'))
                ? $exception->getMessage()
                : 'Server error'
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
     * This method redirects the user to a specified URL or to the previous page.
     *
     * @param string|null $url The URL to redirect to. Default is null.
     * @param array|null $param The parameters to append to the URL. Default is null.
     *
     * @return never
     */
    final static function redirect(?string $url = null, ?array $param = null): never
    {
        if ($url) header('location: /' . $url . arrayToRequest($param));
        else header('location:' . $_SERVER['HTTP_REFERER']);
        exit();
    }

}
