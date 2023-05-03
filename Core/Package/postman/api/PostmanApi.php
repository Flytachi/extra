<?php

use Extra\Src\Api;
use Extra\Src\Route;

class PostmanApi extends Api
{
    const IMPORT_KEY = "03mf04yy";

    private function postmanInfo(): array
    {
        return [
            'name' => basename(PATH_ROOT) . " API Test",
            'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
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

                        if (array_key_exists('@postmanName' , $annotations) & array_key_exists('@postmanMethod' , $annotations)) {
                            $apiMethodData = [
                                "name" => $annotations['@postmanName'],
                                "request" => [
                                    'method' => $annotations['@postmanMethod'],
                                    'header' => [],
                                    'url' => [
                                        'raw' => "",
                                        'host' => ["{{wBaseUrl}}"],
                                        'path' => [
                                            "api",
                                            lcfirst($apiUrl),
                                            $apiMethod->name
                                        ]
                                    ]
                                ],
                                "response" => [],
                            ];
                            $apiData['item'][] = $apiMethodData;
                        }
                    }
                }

                if (count($apiData['item']) > 0) $items[] = $apiData;
            } catch (Exception $e) {}
        }
        return $items;
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
            'key' => 'wBaseUrl',
            'value' => Warframe::$cfg['HOSTS'][0],
            'type' => 'string',
        ];
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
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename=' . $data['info']['name'] . '.postman_collection.json');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . strlen($fileBody));
        file_put_contents('php://output', $fileBody);
    }

    private function getAnnotation(string $docComment): array
    {
        preg_match_all('#@(.*?)\n#s', $docComment, $methodAnnotations);
        $annotations = [];
        foreach ($methodAnnotations[0] as $annotationValue) {
            if(str_starts_with($annotationValue, '@postman')) {
                preg_match_all("/@postman\w+/", $annotationValue, $key);
                $annotations[trim($key[0][0])] = trim(str_replace($key[0][0], '', $annotationValue));
            }
        }
        return $annotations;
    }

}
