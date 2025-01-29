<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\Declaration;

use Flytachi\Extra\Src\Factory\Mapping\MappingException;

class MappingDeclaration
{
    /**
     * @var array<MappingDeclarationItem>
     */
    private array $children = [];

    /**
     * @param MappingDeclarationItem[]|MappingDeclarationItem $children
     */
    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    public function push(MappingDeclarationItem $newChild): void
    {
        foreach ($this->children as $child) {
            if (
                $child->getUrl() == $newChild->getUrl()
                && $child->getMethod() == $newChild->getMethod()
            ) {
                throw new MappingException(
                    "Duplicate mapping declaration {$newChild->getReflectionMethod()->getFileName()}"
                    . " ({$newChild->getReflectionMethod()->getStartLine()})"
                );
            }
        }
        $this->children[] = $newChild;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function sorting()
    {
        // sorting
    }
}
