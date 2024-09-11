<?php

namespace Extra\Src\Factory\Mapping\Common\Declaration;

class MappingDeclarationItem
{
    private string $method;
    private string $url;
    private string $className;
    private string $classMethod;
    private string $classTitle;

    /**
     * @param string $method
     * @param string $url
     * @param string $className
     * @param string $classMethod
     * @param string $classTitle
     */
    public function __construct(string $method, string $url, string $className, string $classMethod, string $classTitle = '')
    {
        $this->method = $method;
        $this->url = $url;
        $this->className = $className;
        $this->classMethod = $classMethod;
        $this->classTitle = $classTitle;
    }

    public function getMettaData(): string
    {
        return "Router::{$this->method}('{$this->url}', {$this->className}::class, '{$this->classMethod}'); // {$this->classTitle}" . PHP_EOL;
    }
}