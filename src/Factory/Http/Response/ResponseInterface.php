<?php

declare(strict_types=1);

namespace Flytachi\Extra\Factory\Http\Response;

use Flytachi\Extra\Factory\Http\HttpCode;

interface ResponseInterface
{
    public function getHttpCode(): HttpCode;
    public function getHeader(): array;
    public function getBody(): string;
}
