<?php

namespace Extra\Src\Factory\Mapping;

use Extra\Src\Controller\Common\ControllerInterface;
use Extra\Src\Factory\Mapping\Annotation\DeleteMapping;
use Extra\Src\Factory\Mapping\Annotation\GetMapping;
use Extra\Src\Factory\Mapping\Annotation\PatchMapping;
use Extra\Src\Factory\Mapping\Annotation\PostMapping;
use Extra\Src\Factory\Mapping\Annotation\PutMapping;
use Extra\Src\Factory\Mapping\Annotation\RequestMapping;
use Extra\Src\Factory\Mapping\Common\Declaration\MappingDeclaration;
use Extra\Src\Factory\Mapping\Common\Declaration\MappingDeclarationGroup;
use Extra\Src\Factory\Mapping\Common\Declaration\MappingDeclarationItem;
use Extra\Src\Factory\Mapping\Common\MappingRequestInterface;
use Extra\Src\Factory\Router\Router;
use Extra\Src\HttpCode;
use Extra\Src\Log\Log;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class Mapping
 *
 * The Mapping class is responsible for scanning controller classes and initializing routes.
 *
 * @version 1.0
 * @author Flytachi
 */
class Mapping
{
    public static function scanning(): void
    {
        $declaration = self::scanningDeclaration();
        self::routeExecute($declaration);
    }

    /**
     * @return MappingDeclaration
     */
    public static function scanningDeclaration(): MappingDeclaration
    {
        $resources = scanFindAllFile(PATH_APP . "/Controllers");
        $reflectionClasses = self::scanReflectionFilter($resources);
        return self::scanDeclarationFilter($reflectionClasses);
    }

    /**
     * @param array $resources
     * @return array<ReflectionClass>
     */
    private static function scanReflectionFilter(array $resources): array
    {
        $reflectionClasses = [];
        foreach ($resources as $resource) {
            $className = str_replace('.php', '', str_replace('/', '\\', str_replace(PATH_APP . '/', '', $resource)));
            $reflectionClass = new ReflectionClass($className);
            if ($reflectionClass->implementsInterface(ControllerInterface::class))
                $reflectionClasses[] = $reflectionClass;
        }
        return $reflectionClasses;
    }

    /**
     * @param array<ReflectionClass> $reflectionClasses
     * @return MappingDeclaration
     */
    private static function scanDeclarationFilter(array $reflectionClasses): MappingDeclaration
    {
        $declaration = new MappingDeclaration();

        foreach ($reflectionClasses as $reflectionClass) {
            $declarationGroup = null;

            // group annotation
            $groupAnnotation = $reflectionClass->getAttributes(RequestMapping::class);
            if (isset($groupAnnotation[0])) {
                $groupAnnotation = $groupAnnotation[0];
                /** @var MappingRequestInterface $mappingGroup */
                $mappingGroup = $groupAnnotation->newInstance();
                $declarationGroup = new MappingDeclarationGroup($mappingGroup->getUrl());
            }

            // annotation
            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                if ($reflectionMethod->name != '__construct') {

                    foreach ($reflectionMethod->getAttributes(MappingRequestInterface::class, \ReflectionAttribute::IS_INSTANCEOF) as $annotation) {
                        /** @var MappingRequestInterface $mapping */
                        $mapping = $annotation->newInstance();
                        $declarationItem = new MappingDeclarationItem(
                            $mapping->getCallback(), $mapping->getUrl(),
                            $reflectionClass->getName(), $reflectionMethod->getName()
                        );
                        if ($declarationGroup != null) $declarationGroup->push($declarationItem);
                        else $declaration->push($declarationItem);

                    }
                }
            }

            if ($declarationGroup != null) $declaration->push($declarationGroup);
        }

        return $declaration;
    }

    /**
     * @param MappingDeclaration $declaration
     * @return void
     */
    private static function routeExecute(MappingDeclaration $declaration): void
    {
        $mettaData = "use " . Router::class . ";" . PHP_EOL;
        $mettaData .= $declaration->getMettaData() . PHP_EOL . PHP_EOL;
        $mettaData = trim($mettaData);
        eval($mettaData);
    }

}