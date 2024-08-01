<?php

namespace Extra\Src\Unit\Postman;

use Attribute;
use Extra\Src\Controller\Method;


#[Attribute(Attribute::TARGET_METHOD)]
class PostmanDescription implements Postman
{
    private string $description;

    /**
     * @param string $description
     */
    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function prepare(array &$arrayData): void
    {
        $arrayData['request']['description'] = $this->description;
    }

}