<?php

namespace Extra\Src\Factory\Router\Common;

use Extra\Src\Controller\ApiBase;
use Extra\Src\Controller\ControllerBase;
use Extra\Src\Controller\Method;


interface RouterInterface
{
    /**
     * @param array{prefix: string, name: string} $attributes
     * @param callable $routes
     * @return void
     */
    public static function group(array $attributes, callable $routes): void;

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @param Method|null $method
     * @return void
     */
    public static function request(string $route, string $class, string $classMethod = 'index', ?Method $method = null): void;

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function get(string $route, string $class, string $classMethod = 'index'): void;

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function post(string $route, string $class, string $classMethod = 'index'): void;

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function put(string $route, string $class, string $classMethod = 'index'): void;

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function patch(string $route, string $class, string $classMethod = 'index'): void;

    /**
     * @param string $route
     * @param class-string<ApiBase>|class-string<ControllerBase> $class
     * @param string $classMethod
     * @return void
     */
    public static function delete(string $route, string $class, string $classMethod = 'index'): void;
}