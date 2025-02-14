<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\OpenApi\Schema;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class SplParam implements Spl
{
    private ?string $description = null;

    /**
     * @param null|string $description
     */
    public function __construct(?string $description = null)
    {
        $this->description = $description;
    }

    public function modify(array &$path): void
    {
        if ($this->description != null) {
            $path['description'] = $this->description;
        }
    }
}
