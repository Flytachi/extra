<?php

namespace Extra\Src\Factory\Mapping\Common\Declaration;

class MappingDeclaration
{
    private string $name;
    /**
     * @var array<MappingDeclarationGroup|MappingDeclarationItem>
     */
    private array $children = [];

    /**
     * @param string $name
     * @param MappingDeclarationGroup[]|MappingDeclarationGroup|MappingDeclarationItem[]|MappingDeclarationItem $children
     */
    public function __construct(string $name, array $children = [])
    {
        $this->name = $name;
        $this->children = $children;
    }

    public function push(MappingDeclarationGroup|MappingDeclarationItem $newChild): void
    {
        $this->children = [
            ...$this->children,
            $newChild
        ];
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getMettaData(): string
    {
        $mettaData = "// " . $this->name . PHP_EOL;
        foreach ($this->children as $child) $mettaData .= $child->getMettaData();
        return $mettaData;
    }

}