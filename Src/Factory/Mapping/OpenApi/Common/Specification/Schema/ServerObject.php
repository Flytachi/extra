<?php

namespace Extra\Src\Factory\Mapping\OpenApi\Common\Specification\Schema;

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