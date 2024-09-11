<?php

namespace Extra\Src\Factory\Mapping\Common\Declaration;

class MappingDeclaration
{
    private string $title;
    /**
     * @var array<MappingDeclarationGroup|MappingDeclarationItem>
     */
    private array $children = [];

    /**
     * @param string $title
     * @param MappingDeclarationGroup[]|MappingDeclarationGroup|MappingDeclarationItem[]|MappingDeclarationItem $children
     */
    public function __construct(string $title, array $children = [])
    {
        $this->title = $title;
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
        $mettaData = "// " . $this->title . PHP_EOL;
        foreach ($this->children as $child) $mettaData .= $child->getMettaData();
        return $mettaData;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

}