<?php

namespace Extra\Src\Annotation\Postman\Requests;

use Apis\PostmanApi;
use Exception;
use Extra\Src\Annotation\Postman\Postman;
use Extra\Src\Annotation\Postman\PostmanEvent;
use ReflectionClass;
use ReflectionMethod;

class PostmanItem implements Postman
{
    public function prepare(array &$arrayData): void {}

    public static function morph(): array
    {
        $items = [];
        foreach (getDirContent(PATH_APP . "/Apis") as $apiPath) {
            $apiName = str_replace('.php', '', str_replace('/', '\\', str_replace(PATH_APP . '/', '', $apiPath)));
            $apiUrl = str_replace('Api', '', basename($apiPath, '.php'));
            $apiData = [
                "name" => $apiUrl,
                "item" => [],
                "event" => [],
            ];

            if ($apiName != PostmanApi::class) {
                try {
                    $apiClass = new ReflectionClass($apiName);

                    // Annotation
                    foreach ($apiClass->getAttributes() as $annotation) {
                        $object = new ($annotation->getName())(
                            $annotation->getArguments()[0] ?? null,
                            $annotation->getArguments()[1] ?? null,
                            $annotation->getArguments()[2] ?? null
                        );
                        $object->prepare($apiData);
                    }

                    if (array_key_exists('request', $apiData) && array_key_exists('auth', $apiData['request'])) {
                        $apiData['auth'] = $apiData['request']['auth'];
                        unset($apiData['request']['auth']);
                    }

                    foreach ($apiClass->getMethods(ReflectionMethod::IS_PUBLIC) as $apiMethod) {
                        if ($apiMethod->name != '__construct') {
                            // params
                            $path = ["api", lcfirst($apiUrl), $apiMethod->name];
                            foreach ($apiMethod->getParameters() as $parameter)
                                $path[] = ($parameter->isDefaultValueAvailable())
                                    ? $parameter->getDefaultValue()
                                    : ('[' . $parameter->name . ']');

                            $apiItemData = [
                                "name" => 'Unknown',
                                "event" => [],
                                "request" => [
                                    'auth' => [],
                                    'method' => "GET",
                                    'header' => [],
                                    'url' => [
                                        'raw' => "{{wBaseUrl}}/" . implode('/', $path),
                                        'host' => ["{{wBaseUrl}}"],
                                        'path' => $path,
                                        'query' => []
                                    ],
                                    'body' => []
                                ],
                                "response" => [],
                            ];

                            // Annotation
                            foreach ($apiMethod->getAttributes() as $annotation) {
                                $object = new ($annotation->getName())(
                                    $annotation->getArguments()[0] ?? null,
                                    $annotation->getArguments()[1] ?? null,
                                    $annotation->getArguments()[2] ?? null
                                );
                                $object->prepare($apiItemData);
                            }

                            $apiData['item'][] = $apiItemData;
                        }
                    }
                    if (count($apiData['item']) > 0) $items[] = $apiData;
                } catch (Exception $e) {}
            }
        }
        return $items;
    }
}