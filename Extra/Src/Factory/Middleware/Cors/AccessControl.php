<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Middleware\Cors;

use Flytachi\Extra\Src\Factory\Http\Method;

final class AccessControl
{
    protected array $origin = [];
    protected array $methods = [Method::OPTIONS->name];
    protected array $headers = [];
    protected bool $credentials = false;
    protected int $maxAge = 0;

    public static function processed(array $options): never
    {
        $router = new static();
        foreach ($options['actions'] as $httpMethod => $action) {
            $router->pushMethod($httpMethod);
            foreach ($action['middlewares'] as $middlewareClass) {
                if (
                    $middlewareClass == AccessControlMiddleware::class ||
                    is_subclass_of($middlewareClass, AccessControlMiddleware::class)
                ) {
                    $data = $middlewareClass::passport();
                    $router->pushOrigin($data['origin']);
                    $router->pushHeaders($data['headers']);
                    $router->pushCredentials($data['credentials']);
                    $router->pushMaxAge($data['maxAge']);
                }
            }
        }
        if (isset($options['defaultAction'])) {
            $router->pushMethod(Method::GET->name);
            $router->pushMethod(Method::POST->name);
            $router->pushMethod(Method::DELETE->name);
            $router->pushMethod(Method::PATCH->name);
            $router->pushMethod(Method::PUT->name);
            foreach ($options['defaultAction']['middlewares'] as $middlewareClass) {
                if (
                    $middlewareClass == AccessControlMiddleware::class ||
                    is_subclass_of($middlewareClass, AccessControlMiddleware::class)
                ) {
                    $data = $middlewareClass::passport();
                    $router->pushOrigin($data['origin']);
                    $router->pushHeaders($data['headers']);
                    $router->pushCredentials($data['credentials']);
                    $router->pushMaxAge($data['maxAge']);
                }
            }
        }
        $router->using();
        exit;
    }

    final protected function pushOrigin(array $origins): void
    {
        foreach ($origins as $origin) {
            if (!in_array($origin, $this->origin)) {
                $this->origin[] = $origin;
            }
        }
    }

    final protected function pushMethod(string $httpMethod): void
    {
        if (!in_array($httpMethod, $this->methods)) {
            $this->methods[] = $httpMethod;
        }
    }

    final protected function pushHeaders(array $headers): void
    {
        foreach ($headers as $header) {
            if (!in_array($header, $this->headers)) {
                $this->headers[] = $header;
            }
        }
    }

    final protected function pushCredentials(bool $credentials): void
    {
        if ($credentials) {
            $this->credentials = true;
        }
    }

    final protected function pushMaxAge(int $maxAge): void
    {
        if ($maxAge > 0) {
            if ($maxAge > $this->maxAge) {
                $this->maxAge = $maxAge;
            }
        }
    }

    final protected function using(): void
    {
        header_remove("X-Powered-By");
        header("HTTP/1.1 200 OK");
        header("Status: 200 OK");
        if (!empty($this->origin)) {
            if (count($this->origin) == 1) {
                header('Access-Control-Allow-Origin: ' . $this->origin[0]);
            } elseif (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $this->origin)) {
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            }
        } else {
            header('Access-Control-Allow-Origin: *');
        }
        if (!empty($this->methods)) {
            header('Access-Control-Allow-Methods: ' . implode(', ', $this->methods));
        }
        if (!empty($this->headers)) {
            header('Access-Control-Allow-Headers: ' . implode(', ', $this->headers));
        }
        if ($this->credentials) {
            header('Access-Control-Allow-Credentials: ' . $this->credentials);
        }
        if ($this->maxAge > 0) {
            header('Access-Control-Max-Age: ' . $this->maxAge);
        }
    }
}