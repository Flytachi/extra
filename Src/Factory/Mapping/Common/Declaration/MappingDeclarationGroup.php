<?php

namespace Extra\Src\Factory\Mapping\Common\Declaration;

class MappingDeclarationGroup
{
    private string $prefix;
    /**
     * @var array<MappingDeclarationItem>
     */
    private array $children = [];

    /**
     * @param string $prefix
     * @param MappingDeclarationItem[]|MappingDeclarationItem $children
     */
    public function __construct(string $prefix, array $children = [])
    {
        $this->prefix = $prefix;
        $this->children = $children;
    }

    public function push(MappingDeclarationGroup|MappingDeclarationItem $newChild): void
    {
        $this->children = [
            ...$this->children,
            $newChild
        ];
    }

    public function getMettaData(): string
    {
        $childMettaData = "";
        foreach ($this->children as $child) {
            if ($child instanceof MappingDeclarationGroup) {
                $groupMettaData = explode(PHP_EOL,  $child->getMettaData());
                foreach ($groupMettaData as $key => $groupMettaDataItem) $groupMettaData[$key] = "\t" . $groupMettaDataItem;
                $childMettaData .= PHP_EOL . implode(PHP_EOL, $groupMettaData);
            } else $childMettaData .= "\t" . $child->getMettaData();
        }
        return "Router::group(['prefix' => '{$this->prefix}'], function() {" . PHP_EOL . rtrim($childMettaData) . PHP_EOL . "});";
    }

}