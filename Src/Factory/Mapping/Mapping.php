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
        $declarations = self::scanningDeclaration();
        self::routeInit($declarations);
    }

    /**
     * @return MappingDeclaration[]
     */
    public static function scanningDeclaration(): array
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
     * @return array<MappingDeclaration>
     */
    private static function scanDeclarationFilter(array $reflectionClasses): array
    {
        $declarations = [];
        foreach ($reflectionClasses as $reflectionClass) {
            $declaration = new MappingDeclaration(lcfirst(str_replace('Controller', '', $reflectionClass->getShortName())));
            $declarationGroup = null;

            // group annotation
            foreach ($reflectionClass->getAttributes() as $annotation) {
                if (in_array($annotation->getName(), [
                    DeleteMapping::class,
                    GetMapping::class,
                    PatchMapping::class,
                    PostMapping::class,
                    PutMapping::class,
                    RequestMapping::class,
                ])) {
                    /** @var MappingRequestInterface $mappingGroup */
                    $mappingGroup = $annotation->newInstance();
                    $declarationGroup = new MappingDeclarationGroup($mappingGroup->getUrl());
                }
            }

            // annotation
            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                if ($reflectionMethod->name != '__construct') {

                    foreach ($reflectionMethod->getAttributes() as $annotation) {
                        if (in_array($annotation->getName(), [
                            DeleteMapping::class,
                            GetMapping::class,
                            PatchMapping::class,
                            PostMapping::class,
                            PutMapping::class,
                            RequestMapping::class,
                        ])) {
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
            }

            if ($declarationGroup != null) $declaration->push($declarationGroup);
            if (!empty($declaration->getChildren())) $declarations[] = $declaration;
        }

        return $declarations;
    }

    /**
     * @param array<MappingDeclaration> $declarations
     * @return void
     */
    private static function routeInit(array $declarations): void
    {
        $mettaData = "<?php" . PHP_EOL . PHP_EOL;
        $mettaData .= "/**" . PHP_EOL . " * Router configurations"
            . PHP_EOL . " * - Created on: " . date(DATE_RFC822)
            . PHP_EOL . " * - Version: 1.0"
            . PHP_EOL . " */" . PHP_EOL . PHP_EOL;
        $mettaData .= "use " . Router::class . ";" . PHP_EOL . PHP_EOL;


        foreach ($declarations as $declaration) $mettaData .= $declaration->getMettaData() . PHP_EOL . PHP_EOL;
        $mettaData = trim($mettaData);

        // write
        $file = fopen(ROUTE_FILE_PATH, 'w');
        if ($file) {
            fwrite($file, $mettaData);
            fclose($file);
        } else {
            MappingError::throw(HttpCode::INTERNAL_SERVER_ERROR, "Не удалось");
        }
    }

}