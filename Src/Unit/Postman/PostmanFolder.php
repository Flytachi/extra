<?php

namespace Extra\Src\Unit\Postman;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PostmanFolder implements Postman
{
    private string $name;
    private ?string $description;

    /**
     * @param string $name
     * @param string|null $description
     */
    public function __construct(string $name, ?string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function prepare(array &$arrayData): void {
        $arrayData['name'] = $this->name;
        $arrayData['description'] = $this->description;
    }

}