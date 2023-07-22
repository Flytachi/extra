<?php

use Extra\Src\Api;
use Extra\Src\Route;

/**
 * PostmanApi API
 *
 * Template:
 *      postmanMethods => [ GET, POST ]
 *      postmanBodyType => [ rawJson, rawText, formdata ]
 *
 *
     * Examples:
     * -------------------------------------
        ! GET query no params
             * @postmanName Example Get Query
             * @postmanMethod GET
     * -------------------------------------
        ! GET query
             * @postmanName Example Get Query
             * @postmanMethod GET
             *
             * @postmanParam id -> 1
     * -------------------------------------
        ! POST query (JSON format)
             * @postmanName Example post Query
             * @postmanMethod POST
             *
             * @postmanBodyType rawJson
             * @postmanBodyItem name -> temp
             * @postmanBodyItem mail
     * -------------------------------------
     *
 *
 */
class PostmanApi extends Api
{
    const IMPORT_KEY = "03mf04yy";

    private function postmanInfo(): array
    {
        $FOLDER = basename(PATH_ROOT) . ' API';
        return [
            'name' => $FOLDER,
            'description' => "# **{$FOLDER}**\n\n**Update Collection:**\n" . SERVER_SCHEME . '/api/postman/collection/' . self::IMPORT_KEY,
            'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
        ];
    }

    private function postmanAuth(): array
    {
        return [];
    }

    private function postmanEvent(): array
    {
        return [];
    }

    private function postmanVariable(): array
    {
        return [
            [
                'key' => 'wBaseUrl',
                'value' => SERVER_SCHEME,
                'type' => 'string',
                'disabled' => false
            ]
        ];
    }

    private function postmanItem(): array
    {
        $items = [];
        foreach (getDirContent(PATH_APP . "/api") as $apiPath) {
            $apiName = basename($apiPath, '.php');
            $apiUrl = str_replace('Api', '', $apiName);
            $apiData = [
                "name" => $apiUrl,
                "item" => [],
                "event" => [],
            ];

            try {
                $apiClass = new ReflectionClass($apiName);
                foreach ($apiClass->getMethods(ReflectionMethod::IS_PUBLIC) as $apiMethod) {
                    if ($apiMethod->name != '__construct') {
                        $annotations = $this->getAnnotation($apiMethod->getDocComment());
                        $path = [ "api", lcfirst($apiUrl), $apiMethod->name ];
                        foreach ($apiMethod->getParameters() as $parameter) $path[] = '[' . $parameter->name . ']';

                        if (array_key_exists('@postmanName' , $annotations) & array_key_exists('@postmanMethod' , $annotations)) {
                            $apiMethodData = [
                                "name" => $annotations['@postmanName'],
                                "request" => [
                                    'method' => $annotations['@postmanMethod'],
                                    'header' => [],
                                    'url' => [
                                        'raw' => "{{wBaseUrl}}/" . implode('/', $path),
                                        'host' => ["{{wBaseUrl}}"],
                                        'path' => $path
                                    ]
                                ],
                                "response" => [],
                            ];

                            $this->postmanItemParamData($apiMethodData, $annotations);
                            $this->postmanItemBodyData($apiMethodData, $annotations);
                            $apiData['item'][] = $apiMethodData;
                        }
                    }
                }

                if (count($apiData['item']) > 0) $items[] = $apiData;
            } catch (Exception $e) {}
        }
        return $items;
    }

    private function postmanItemParamData(array &$apiMethodData, array $annotations): void
    {
        if (array_key_exists('@postmanParam' , $annotations)) {
            $apiMethodData['request']['url']['query'] = [];
            foreach ($annotations['@postmanParam'] as $itemName => $itemValue) {
                $apiMethodData['request']['url']['query'][] = [
                    'key' => $itemName,
                    'value' => $itemValue
                ];
            }
        }
    }

    private function postmanItemBodyData(array &$apiMethodData, array $annotations): void
    {
        if (array_key_exists('@postmanBodyType' , $annotations)) {
            switch (strtolower($annotations['@postmanBodyType'])) {
                case 'rawjson':
                    $apiMethodData['request']['body'] = [
                        'mode' => 'raw',
                        'raw' => json_encode((array_key_exists('@postmanBodyItem', $annotations)) ? $annotations['@postmanBodyItem'] : [], JSON_PRETTY_PRINT),
                        'options' => [
                            'raw' => ['language' => 'json']
                        ]
                    ];
                    break;
                case 'rawtext':
                    $apiMethodData['request']['body'] = [
                        'mode' => 'raw',
                        'raw' => json_encode((array_key_exists('@postmanBodyItem', $annotations)) ? $annotations['@postmanBodyItem'] : [], JSON_PRETTY_PRINT)
                    ];
                    break;
                case 'formdata':
                    $apiMethodData['request']['body'] = [
                        'mode' => 'formdata',
                        'formdata' => []
                    ];
                    foreach ($annotations['@postmanBodyItem'] as $itemName => $itemValue) {
                        $apiMethodData['request']['body']['formdata'][] = [
                            'key' => $itemName,
                            'value' => $itemValue,
                            'type' => 'text'
                        ];
                    }
                    break;
            }
        }
    }

    private function getAnnotation(string $docComment): array
    {
        preg_match_all('#@(.*?)\n#s', $docComment, $methodAnnotations);
        $annotations = [];
        foreach ($methodAnnotations[0] as $annotationValue) {
            if(str_starts_with($annotationValue, '@postman')) {
                preg_match_all("/@postman\w+/", $annotationValue, $key);
                $nKey = trim($key[0][0]);
                $nValue = trim(str_replace($nKey, '', $annotationValue));
                if (in_array($nKey, ['@postmanParam', '@postmanBodyItem'])) {
                    if (!array_key_exists($nKey, $annotations)) $annotations[$nKey] = [];
                    $ex = explode('->', $nValue);
                    $annotations[$nKey][trim($ex[0])] = (count($ex) > 1) ? trim($ex[1]) : '';
                } else $annotations[$nKey] = $nValue;
            }
        }
        return $annotations;
    }

    public function collection(string $key): void
    {
        $this->method(METHOD::GET);
        include PATH_APP . '/constants.php';
        if (self::IMPORT_KEY !== $key) Route::ThrowableApi(404, "Authorization failed.");

        $data = [
            'info' => $this->postmanInfo(),
            'item' => $this->postmanItem(),
            'auth' => $this->postmanAuth(),
            'event' => $this->postmanEvent(),
            'variable' => $this->postmanVariable()
        ];
        $fileBody = json_encode($data);
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $data['info']['name'] . '.postman_collection.json');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . strlen($fileBody));
        file_put_contents('php://output', $fileBody);
    }
}
