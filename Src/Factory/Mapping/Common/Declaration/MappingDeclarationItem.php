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

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMettaData(): string
    {
        return "Router::{$this->method}('{$this->url}', {$this->className}::class, '{$this->classMethod}'); // {$this->classTitle}" . PHP_EOL;
    }

    public function getClassTitle(): string
    {
        return $this->classTitle;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

}