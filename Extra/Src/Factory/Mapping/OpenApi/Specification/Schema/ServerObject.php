<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\OpenApi\Specification\Schema;

class ServerObject
{
    public string $url;
    public string $description;
    public array $variables;

    /**
     * @param string $url
     * @param string $description
     * @param array $variables
     */
    public function __construct(string $url, string $description, array $variables = [])
    {
        $this->url = $url;
        $this->description = $description;
        $this->variables = $variables;
    }
}
