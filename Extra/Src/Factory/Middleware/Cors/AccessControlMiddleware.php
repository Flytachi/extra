<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Middleware\Cors;

use Flytachi\Extra\Src\Factory\Http\Method;
use Flytachi\Extra\Src\Factory\Middleware\AbstractMiddleware;
use Flytachi\Extra\Src\Factory\Middleware\MiddlewareInterface;

abstract class AccessControlMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    protected array $origin = [];
    protected array $headers = [];
    protected bool $credentials = false;
    protected int $maxAge = 0;

    final public static function passport(): array
    {
        $self = new static();
        return [
            'origin' => $self->origin,
            'headers' => $self->headers,
            'credentials' => $self->credentials,
            'maxAge' => $self->maxAge,
        ];
    }

    final public function using(): void
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
            header('Access-Control-Allow-Methods: ' . Method::OPTIONS->name . ', ' . $_SERVER['REQUEST_METHOD']);
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
