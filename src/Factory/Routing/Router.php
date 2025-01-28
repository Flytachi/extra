<?php

declare(strict_types=1);

namespace Flytachi\Extra\Factory\Routing;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Factory\Mapping\Mapping;
use Flytachi\Extra\Unit\Method;

class Router
{
    /**
     * An array to store registered routes in a tree structure.
     *
     * @var array
     */
    private static array $routes = [];
    private static string $pathMapping;


    final public static function run(bool $isDevelop = false, ?string $pathMapping = null): void
    {
        self::setPaths($pathMapping);
        self::registrar($isDevelop);
//        Request::setHeaders();
//        self::route();
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

    private static function setPaths(?string $pathMapping): void
    {
        if ($pathMapping === null) {
            self::$pathMapping = Extra::pathStorage() . '/cache/mapping.php';
        } else {
            self::$pathMapping = $pathMapping;
        }
    }

    private static function registrar(bool $isDevelop): void
    {
        if ($isDevelop) {
            if (file_exists(self::$pathMapping)) {
                unlink(self::$pathMapping);
            }
            $declaration = Mapping::scanningDeclaration();
            foreach ($declaration->getChildren() as $item) {
                self::request($item->getUrl(), $item->getClassName(), $item->getClassMethod(), $item->getMethod());
            }
        } else {
            if (!file_exists(self::$pathMapping)) {
                self::generateMappingRoutes();
            } else {
                self::$routes = require self::$pathMapping;
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
     * @throws RoutingException If the route is already registered with the same HTTP method or as a default action.
     * @return void
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
                throw new RoutingException("Route '$route' with method '$method' is already registered.");
            }
            $node['actions'][$method] = ['class' => $class, 'method' => $classMethod];
        } else {
            if (isset($node['defaultAction'])) {
                throw new RoutingException("Route '$route' (default) is already registered.");
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
        file_put_contents(self::$pathMapping, $fileData);
    }
}
