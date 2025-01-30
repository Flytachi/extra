<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Entity;

interface RequestInterface
{
    public static function params(bool $required = true): static;
    public static function formData(bool $required = true): static;
    public static function json(bool $required = true): static;
}
