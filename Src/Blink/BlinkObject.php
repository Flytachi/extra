<?php

namespace Extra\Src\Blink;

use Extra\Src\Enum\HttpCode;
use ReflectionProperty;

class BlinkObject extends \stdClass
{
    public final function __construct(array $data)
    {
        try {
            $reflection = new \ReflectionClass($this);
            $property1 = [];
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty)
                $property1[$reflectionProperty->getName()] = (string)$reflectionProperty->getType();
            $properties = $property1;
            if ($data) {
                foreach ($data as $key => $value) {
                    $this->{$key} = $value;
                    unset($properties[$key]);
                }
            }
        } catch (\Throwable $exception) {
            BlinkError::throw(HttpCode::BAD_REQUEST, $exception->getMessage());
        }
    }

    public function status(): int
    {
        return $this->http_code;
    }

    public function response(): mixed
    {
        return $this->response ?? '';
    }

    public function responseAsJson(): array|null
    {
        return json_decode($this->response(), true);
    }

    public function responseAsXML(): false|\SimpleXMLElement
    {
        return simplexml_load_string($this->response());
    }

    public function responseAsXMLToJson(): false|array
    {
        $xml = $this->responseAsXML();
        return ($xml
            ? json_decode(json_encode($xml), true)
            : false);
    }

}