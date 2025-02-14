<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\OpenApi\Schema;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SplOperation implements Spl
{
    private string $summary;
    private ?string $description = null;

    /**
     * @param string $summary
     * @param null|string $description
     */
    public function __construct(string $summary, ?string $description = null)
    {
        $this->summary = $summary;
        $this->description = $description;
    }

    public function modify(array &$path): void
    {
        $path['summary'] = $this->summary;
        $path['description'] = $this->description;
    }
}
