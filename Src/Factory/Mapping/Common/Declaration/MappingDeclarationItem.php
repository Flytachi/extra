<?php

namespace Extra\Src\Factory\Mapping\Common\Declaration;

class MappingDeclarationItem
{
    private string $method;
    private string $url;
    private string $className;
    private string $classMethod;

    /**
     * @param string $method
     * @param string $url
     * @param string $className
     * @param string $classMethod
     */
    public function __construct(string $method, string $url, string $className, string $classMethod)
    {
        $this->method = $method;
        $this->url = $url;
        $this->className = $className;
        $this->classMethod = $classMethod;
    }

    public function getMettaData(): string
    {
        return "Router::{$this->method}('{$this->url}', {$this->className}::class, '{$this->classMethod}');" . PHP_EOL;
    }
}