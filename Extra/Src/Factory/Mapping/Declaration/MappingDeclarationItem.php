<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\Declaration;

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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getClassMethod(): string
    {
        return $this->classMethod;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

//    /**
//     * @return array<ReflectionAttribute>
//     */
//    public function getClassSpl(): array
//    {
//        $method = new ReflectionClass($this->className);
//        return $method->getAttributes(Spl::class, ReflectionAttribute::IS_INSTANCEOF);
//    }
//
//    /**
//     * @return array<ReflectionAttribute>
//     */
//    public function getClassMethodSpl(): array
//    {
//        $method = new ReflectionMethod($this->className, $this->classMethod);
//        return $method->getAttributes(Spl::class, ReflectionAttribute::IS_INSTANCEOF);
//    }

    /**
     * @return ReflectionMethod
     */
    public function getReflectionMethod(): ReflectionMethod
    {
        return new ReflectionMethod($this->className, $this->classMethod);
    }
}
