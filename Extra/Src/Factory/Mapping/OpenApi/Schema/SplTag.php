<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\OpenApi\Schema;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SplTag implements Spl
{
    private string $name;
    private ?string $description = null;

    /**
     * @param string $name
     * @param null|string $description
     */
    public function __construct(string $name, ?string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function modify(array &$path): void
    {
        $path['name'] = $this->name;
        $path['description'] = $this->description;
    }
}
