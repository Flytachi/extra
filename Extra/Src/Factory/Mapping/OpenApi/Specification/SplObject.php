<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\OpenApi\Specification;

use Flytachi\Extra\Src\Factory\Mapping\OpenApi\Specification\Schema\ServerObject;

class SplObject
{
    public string $openapi;
    public array $info = [];
    /**
     * @var array<ServerObject>
     */
    public array $servers = [];
    public array $tags = [];
    public array $paths = [];

    /**
     * @param string $openapi
     */
    public function __construct(string $openapi)
    {
        $this->openapi = $openapi;
    }
}
