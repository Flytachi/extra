<?php

namespace Extra\Src\Factory\Router\Common;

use Extra\Src\Controller\ApiBase;
use Extra\Src\Controller\ControllerBase;
use Extra\Src\Controller\Method;

class RouteNode
{
    /** @var array<static|Method, RouteNode> */
    public array $children = [];
    /** @var array<string, array{class: class-string<ApiBase>|class-string<ControllerBase>, method: string}>|null */
    public ?array $actions = null;
    /** @var array{class: class-string<ApiBase>|class-string<ControllerBase>, method: string}|null */
    public ?array $defaultAction = null;
    public bool $isParam;

    public function __construct(bool $isParam = false)
    {
        $this->isParam = $isParam;
    }
}