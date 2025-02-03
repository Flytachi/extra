<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Routing;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Src\Factory\Http\Header;
use Flytachi\Extra\Src\Factory\Http\HttpCode;
use Flytachi\Extra\Src\Factory\Http\Rendering;
use Flytachi\Extra\Src\Factory\Mapping\Mapping;
use Flytachi\Extra\Src\Stereotype\ControllerInterface;
use Monolog\Logger;

class Router
{
    /**
     * An array to store registered routes in a tree structure.
     *
     * @var array
     */
    private static array $routes = [];
    private static Logger $logger;

    final public static function run(bool $isDevelop = false): void
    {
        self::$logger = Extra::$logger->withName("Router");
        self::registrar($isDevelop);
        Header::setHeaders();
        self::route();
    }

    private static function route(): void
    {
        self::$logger->debug(
            'route: ' . $_SERVER['REQUEST_METHOD']
            . ' ' . $_SERVER['REQUEST_URI']
            . ' IP: ' . Header::getIpAddress()
        );
        $data = self::splitUrlAndParams($_SERVER['REQUEST_URI']);
        $_GET = $data['params'];

        $render = new Rendering();
        try {
            $resolve =  self::resolve($data['url'], $_SERVER['REQUEST_METHOD']);
            if (!$resolve) {
                throw new RouterException(
                    "{$_SERVER['REQUEST_METHOD']} '{$data['url']}' url not found",
                    HttpCode::NOT_FOUND->value
                );
            }

            $result = self::callResolveAction($resolve['action'], $resolve['params'], $resolve['url'] ?? '');
            $render->setResource($result);
        } catch (\Throwable $e) {
            $render->setResource($e);
        }

        $render->render();

        // original
//        try {
//            self::callResolveAction($resolve['action'], $resolve['params'], $resolve['url'] ?? '');
//        } catch (\TypeError $exception) {
//            RouteError::throw(HttpCode::BAD_REQUEST, (env('DEBUG'))
//                ? $exception->getMessage()
//                : 'Invalid data');
//        } catch (CDOError | RepositoryError | ArtefactError | SheathException $exception) {
//            RouteError::throw(
//                HttpCode::INTERNAL_SERVER_ERROR,
//                (env('DEBUG'))
//                ? $exception->getMessage()
//                : 'Server error',
//                $exception
//            );
//        } catch (ExtraException | RouteError $exception) {
//            $code = HttpCode::tryFrom((int) $exception->getCode());
//            RouteError::throw($code ?: HttpCode::INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception);
//        } catch (\Throwable $exception) {
//            RouteError::throw(HttpCode::INTERNAL_SERVER_ERROR, (env('DEBUG'))
//                ? $exception->getMessage()
//                : 'Server error');
//        }
    }

    /**
     * Resolves a given URL and HTTP method to a registered route.
     *
     * This method searches the registered routes for a match to the provided URL and HTTP method.
     * If a match is found, it returns an array containing the associated controller action and any dynamic parameters.
     * If no match is found, it returns null.
     *
     * @param string $url The requested URL to resolve.
     * @param string $httpMethod The HTTP method used in the request (e.g., "GET").
     * @return array|null Returns an array with the action and
     * parameters if a route is found, or null if no route matches.
     */
    final public static function resolve(string $url, string $httpMethod): ?array
    {
        $node = self::$routes;
        $params = [];
        $parts = explode('/', trim($url, '/'));

        // Traverse the route tree to find a match
        foreach ($parts as $part) {
            if (isset($node[$part])) {
                $node = $node[$part];
            } elseif (isset($node['{param}'])) {
                $node = $node['{param}'];
                $params[] = $part;
            } else {
                return null; // No matching route found
            }
        }

        // Return the action and parameters if a match is found
        if (isset($node['actions'][$httpMethod])) {
            return ['action' => $node['actions'][$httpMethod], 'params' => $params];
        }
        if (isset($node['defaultAction'])) {
            return ['action' => $node['defaultAction'], 'params' => $params];
        }

        return null; // No action found for the route
    }

    private static function registrar(bool $isDevelop): void
    {
        if ($isDevelop) {
            if (file_exists(Extra::$pathFileMapping)) {
                unlink(Extra::$pathFileMapping);
            }
            $declaration = Mapping::scanningDeclaration();
            foreach ($declaration->getChildren() as $item) {
                self::request($item->getUrl(), $item->getClassName(), $item->getClassMethod(), $item->getMethod());
            }
        } else {
            if (!file_exists(Extra::$pathFileMapping)) {
                self::generateMappingRoutes();
            } else {
                self::$routes = require Extra::$pathFileMapping;
            }
        }
    }

    /**
     * Registers a route with the router.
     *
     * This method allows you to define a route, associate it with a controller class and method,
     * and optionally specify an HTTP method. The route can include dynamic parameters (e.g., `/user/{id}`).
     *
     * @param string $route The URL route pattern (e.g., "/user/{id}").
     * @param string $class The controller class to handle the route.
     * @param string $classMethod The method within the controller class to call (defaults to 'index').
     * @param string|null $method The HTTP method for the route (e.g., 'GET', 'POST', ...).
     * If null, the route will be treated as a default action.
     * @return void
     * @throws RouterException If the route is already registered with the same HTTP method or as a default action.
     */
    private static function request(
        string $route,
        string $class,
        string $classMethod = 'index',
        ?string $method = null
    ): void {
        // Normalize the URL by trimming slashes
        $route = trim($route, '/');
        $parts = explode('/', $route);

        // Build the route tree
        $node = &self::$routes;
        foreach ($parts as $part) {
            $isParam = preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*}$/', $part) === 1;
            $key = $isParam ? '{param}' : $part;

            if (!isset($node[$key])) {
                $node[$key] = [];
            }
            $node = &$node[$key];
        }

        // Register the route with the specified HTTP method or as a default action
        if ($method !== null) {
            if (isset($node['actions'][$method])) {
                throw new RouterException("Route '$route' with method '$method' is already registered.");
            }
            $node['actions'][$method] = ['class' => $class, 'method' => $classMethod];
        } else {
            if (isset($node['defaultAction'])) {
                throw new RouterException("Route '$route' (default) is already registered.");
            }
            $node['defaultAction'] = ['class' => $class, 'method' => $classMethod];
        }
    }

    private static function generateMappingRoutes(): void
    {
        $declaration = Mapping::scanningDeclaration();
        foreach ($declaration->getChildren() as $item) {
            self::request($item->getUrl(), $item->getClassName(), $item->getClassMethod(), $item->getMethod());
        }
        $mapString = var_export(json_decode(json_encode(self::$routes), true), true);
        $fileData = "<?php" . PHP_EOL . PHP_EOL;
        $fileData .= "/**" . PHP_EOL . " * Mapping configurations"
            . PHP_EOL . " * - Created on: " . date(DATE_RFC822)
            . PHP_EOL . " * - Version: 1.5"
            . PHP_EOL . " */" . PHP_EOL . PHP_EOL
            . "return {$mapString};";
        file_put_contents(Extra::$pathFileMapping, $fileData);
    }


    final protected static function splitUrlAndParams(string $url): array
    {
        $parsedUrl = parse_url($url);
        $urlWithoutParams = $parsedUrl['path'];
        $params = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $params);
        }

        return [
            'url' => $urlWithoutParams,
            'params' => $params
        ];
    }

    /**
     * @param array{class: class-string<ControllerInterface>, method: string} $action
     * @param array<int, string> $params
     * @param string $stringUrl
     * @return mixed
     * @throws RouterException
     */
    final protected static function callResolveAction(array $action, array $params = [], string $stringUrl = ''): mixed
    {
        $controller = new $action['class']();
        $methods = get_class_methods($controller);

        if (!in_array($action['method'], $methods)) {
            throw new RouterException(
                "{$_SERVER['REQUEST_METHOD']} '{$stringUrl}' url realization '{$action['method']}' not found"
            );
//            RouteError::throw(
//                HttpCode::BAD_GATEWAY,
//                "{$_SERVER['REQUEST_METHOD']} '{$stringUrl}' url realization '{$action['method']}' not found"
//            );
        }

        try {
            return call_user_func_array([$controller, $action['method']], $params);
        } catch (\TypeError $exception) {
            $temp = $controller::class . "::" . $action['method'] . '():';
            if (str_starts_with($exception->getMessage(), $temp)) {
                throw new RouterException(
                    str_replace($temp . " ", '', $exception->getMessage())
                );
//                RouteError::throw(
//                    HttpCode::BAD_REQUEST,
//                    str_replace($temp . " ", '', $exception->getMessage())
//                );
            } else {
                throw $exception;
            }
        }
    }
}
