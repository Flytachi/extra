<?php

namespace Extra\Src\Factory\Mapping\OpenApi;

use Extra\Src\Factory\Mapping\Common\Declaration\MappingDeclaration;
use Extra\Src\Factory\Mapping\Common\Declaration\MappingDeclarationGroup;
use Extra\Src\Factory\Mapping\Common\Declaration\MappingDeclarationItem;
use Extra\Src\Factory\Mapping\Mapping;
use Extra\Src\Factory\Mapping\OpenApi\Common\Specification\Schema\ServerObject;
use Extra\Src\Factory\Mapping\OpenApi\Common\Specification\SplObject;
use Extra\Src\Factory\Mapping\OpenApi\Schema\Spl;
use Extra\Src\Log\Log;
use Extra\Src\Sheath\File\JSON;
use SplObjectStorage;

/**
 * Class OpenApi
 *
 * @version 3.1.0
 * @author Flytachi
 */
class OpenApi
{
    const VERSION = '3.1.0';

    public static function generate(): SplObject
    {
        $spl = new SplObject(self::VERSION);
        $declaration = Mapping::scanningDeclaration();
        self::collectDeclaration($spl, $declaration);
        $spl->tags = array_values($spl->tags);
        return $spl;
    }

    private static function collectDeclaration(SplObject &$spl, MappingDeclaration|MappingDeclarationGroup $declaration, ?string $tagName = null): void
    {
        foreach ($declaration->getChildren() as $child) {
            if ($child instanceof MappingDeclarationGroup) {
                $fullUrl = '/' . trim($tagName . '/' . $child->getPrefix(), '/');
                self::collectDeclaration($spl, $child, $fullUrl);
            } elseif ($child instanceof MappingDeclarationItem) {
                $fullUrl = '/' . trim($tagName . '/' . $child->getUrl(), '/');
                if (!isset($spl->paths[$fullUrl])) $spl->paths[$fullUrl] = [];

                $path = [];
                if ($tagName != null) {
                    if (!isset($spl->tags[$tagName])) {
                        $tag = [];
                        foreach ($child->getClassSpl() as $childSplClass)
                            $childSplClass->newInstance()->modify($tag);
                        if (empty($tag)) {
                            $default = trim($tagName, '/');
                            $default = array_map('ucfirst', explode('/', $default));
                            $default = implode(' / ', $default);
                            $tag['name'] = $default;
                        }
                        $spl->tags[$tagName] = $tag;
                    } else $tag = $spl->tags[$tagName];

                    $path['tags'][] = $tag['name'];
                }

                foreach ($child->getClassMethodSpl() as $childSplMethod)
                    $childSplMethod->newInstance()->modify($path);

                $method = $child->getMethod() == 'request' ? 'get' : $child->getMethod();

                // params
                if (!isset($path['parameters'])) $path['parameters'] = [];
                foreach ($child->getClassMethod()->getParameters() as $parameter) {
                    $object = [
                        'name' => $parameter->getName(),
                        'in' => 'path',
                        'required' => !$parameter->isOptional(),
                        'description' => '',
                        'schema' => [
                            // 'type' => $parameter->getType()->getName(),
                        ]
                    ];
                    if ($parameter->isDefaultValueAvailable())
                        $object['schema']['default'] = $parameter->getDefaultValue();
                    foreach ($parameter->getAttributes(Spl::class, \ReflectionAttribute::IS_INSTANCEOF) as $paramSpl)
                        $paramSpl->newInstance()->modify($object);
                    $path['parameters'][] = $object;
                }

                $spl->paths[$fullUrl][$method] = $path;
            }
        }
    }
}