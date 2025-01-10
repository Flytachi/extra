<?php

namespace Extra\Src\Factory\Mapping\Common\Declaration;

use Extra\Src\Factory\Mapping\OpenApi\Schema\Spl;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

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
        return "Router::{$this->method}('{$this->url}', {$this->className}::class, '{$this->classMethod}');" . PHP_EOL;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<ReflectionAttribute>
     */
    public function getClassSpl(): array
    {
        $method = new ReflectionClass($this->className);
        return $method->getAttributes(Spl::class, ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * @return array<ReflectionAttribute>
     */
    public function getClassMethodSpl(): array
    {
        $method = new ReflectionMethod($this->className, $this->classMethod);
        return $method->getAttributes(Spl::class, ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * @return ReflectionMethod
     */
    public function getClassMethod(): ReflectionMethod
    {
        return new ReflectionMethod($this->className, $this->classMethod);
    }

}