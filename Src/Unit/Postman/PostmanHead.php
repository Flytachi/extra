<?php

namespace Extra\Src\Unit\Postman;

use Attribute;
use Extra\Src\Controller\Method;

#[Attribute(Attribute::TARGET_METHOD)]
class PostmanHead implements Postman
{
    private string $name;
    private Method $method;
    private ?array $params;

    /**
     * @param string $name
     * @param Method $method
     * @param array|null $params
     */
    public function __construct(string $name, Method $method, ?array $params = null)
    {
        $this->name = $name;
        $this->method = $method;
        $this->params = $params;
    }

    public function prepare(array &$arrayData): void
    {
        $arrayData['name'] = $this->name;
        $arrayData['request']['method'] = $this->method->name;
        if (!is_null($this->params)) {
            foreach ($this->params as $itemName => $itemValue) {
                $arrayData['request']['url']['query'][] = [
                    'key' => $itemName,
                    'value' => (string) $itemValue
                ];
            }
        }
    }

}