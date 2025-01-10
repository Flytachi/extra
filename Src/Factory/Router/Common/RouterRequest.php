<?php

namespace Extra\Src\Factory\Router\Common;

use Extra\Src\Controller\ApiBase;
use Extra\Src\Controller\ControllerBase;
use Extra\Src\Controller\Method;
use Extra\Src\Factory\Router\RouteError;
use Extra\Src\HttpCode;

trait RouterRequest
{

    /**
     * @param array{prefix: string, name: string} $attributes
     * @param callable $routes
     * @return void
     */
    public static function group(array $attributes, callable $routes): void
    {
        $prefix = $attributes['prefix'] ?? '';
        if ($prefix) self::$groupPrefix[] = trim($prefix, '/');
        $routes();
        if ($prefix) array_pop(self::$groupPrefix);
    }

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function get(string $route, string $class, string $classMethod = 'index'): void
    {
        static::request($route, $class, $classMethod, Method::GET);
    }

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function post(string $route, string $class, string $classMethod = 'index'): void
    {
        static::request($route, $class, $classMethod, Method::POST);
    }

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function put(string $route, string $class, string $classMethod = 'index'): void
    {
        static::request($route, $class, $classMethod, Method::PUT);
    }

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function patch(string $route, string $class, string $classMethod = 'index'): void
    {
        static::request($route, $class, $classMethod, Method::PATCH);
    }

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function delete(string $route, string $class, string $classMethod = 'index'): void
    {
        static::request($route, $class, $classMethod, Method::DELETE);
    }

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @param Method|null $method
     * @return void
     */
    public static function request(string $route, string $class, string $classMethod = 'index', ?Method $method = null): void
    {
        if (!is_subclass_of($class, ApiBase::class) && !is_subclass_of($class, ControllerBase::class)) {
            throw new \InvalidArgumentException("Class must be a subclass of ApiBase or ControllerBase");
        }

        $node = self::$root;
        $route = trim($route, '/');
        $fullRoute = trim((!empty(self::$groupPrefix) ? implode('/', self::$groupPrefix) . '/' : '') . $route, '/');
        $parts = explode('/', $fullRoute);

        foreach ($parts as $part) {
            $isParam = false;
            if (preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*}$/', $part)) {
                $part = '{param}';
                $isParam = true;
            }

            if (!isset($node->children[$part]))
                $node->children[$part] = new RouteNode($isParam);
            $node = $node->children[$part];
        }

        if ($method !== null) {
            if ($node->actions === null) {
                $node->actions = [];
            }
            $node->actions[$method->name] = ['class' => $class, 'method' => $classMethod];
        } else {
            $node->defaultAction = ['class' => $class, 'method' => $classMethod];
        }
    }

}