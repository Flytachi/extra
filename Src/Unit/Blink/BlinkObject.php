<?php

namespace Extra\Src\Unit\Blink;

use Extra\Src\HttpCode;
use ReflectionProperty;

/**
 * Class BlinkObject
 *
 * `BlinkObject` is a standard class object with additional methods.
 * It is used to hold data and provide specific functionalities tied with its stored properties like converting them into different formats.
 *
 * The methods provided by `BlinkObject` include:
 *
 * - `__construct(array $data)`: Constructor that builds the BlinkObject by using an associative array on instantiation.
 * - `status(): int`: Provides the status or HTTP response code of the `BlinkObject` instance.
 * - `response(): mixed`: Fetches the response property of the `BlinkObject` instance.
 * - `responseAsJson(): array|null`: Returns the `response` of the `BlinkObject` instance as a JSON-decoded array.
 * - `responseAsXML(): false|SimpleXMLElement`: Returns the `response` as an `SimpleXMLElement` instance.
 * - `responseAsXMLToJson(): false|array`: Converts the `response` from XML-formatted string to a JSON-decoded array.
 *
 * @version 1.0
 * @author Flytachi
 */
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

    /**
     * Retrieves the status code.
     *
     * @return int The status code.
     */
    public function status(): int
    {
        return $this->http_code;
    }

    /**
     * Retrieves the response from the object.
     *
     * @return mixed The response value stored in the object, or an empty string if no response is set.
     */
    public function response(): mixed
    {
        return $this->response ?? '';
    }

    /**
     * Convert the response to JSON format.
     *
     * @return array|null The response as an associative array if it can be successfully decoded from JSON,
     *                   or null if decoding fails or the response is empty.
     */
    public function responseAsJson(): array|null
    {
        return json_decode($this->response(), true);
    }

    /**
     * Convert the response to XML format.
     *
     * @return false|\SimpleXMLElement False if the response cannot be converted to XML or the response is empty.
     *                                \SimpleXMLElement object representing the response if the XML conversion was successful.
     */
    public function responseAsXML(): false|\SimpleXMLElement
    {
        return simplexml_load_string($this->response());
    }

    /**
     * Convert the response to JSON format.
     *
     * @return false|array The response as an associative array if it can be successfully encoded from XML,
     *                    false if the XML response is empty.
     */
    public function responseAsXMLToJson(): false|array
    {
        $xml = $this->responseAsXML();
        return ($xml
            ? json_decode(json_encode($xml), true)
            : false);
    }

}