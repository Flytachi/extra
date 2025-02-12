<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Http\Response;

use Flytachi\Extra\Src\Factory\Http\HttpCode;

interface ViewInterface
{
    public function getHttpCode(): HttpCode;
    public function getHeader(): array;
    public function getResource(): string;
    public function getData(): mixed;
    public function getHandle(): ?string;
}
