<?php

namespace Extra\Src\Factory\Mapping\Common\Declaration;

class MappingDeclaration
{
    /**
     * @var array<MappingDeclarationGroup|MappingDeclarationItem>
     */
    private array $children = [];

    /**
     * @param MappingDeclarationGroup[]|MappingDeclarationGroup|MappingDeclarationItem[]|MappingDeclarationItem $children
     */
    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    public function push(MappingDeclarationGroup|MappingDeclarationItem $newChild): void
    {
        if ($newChild instanceof MappingDeclarationGroup)
            $this->pushGroup($newChild);
        else $this->pushItem($newChild);
    }

    private function pushGroup(MappingDeclarationGroup $newGroup): void
    {
        $prefixParts = explode('/', $newGroup->getPrefix());
        if (count($prefixParts) > 1) {
            $parentPrefix = array_shift($prefixParts);
            $childPrefix = implode('/', $prefixParts);
            $parentGroup = $this->getOrCreateGroup($parentPrefix);
            $newGroup->setPrefix($childPrefix);
            $parentGroup->push($newGroup);
        } else $this->children[$newGroup->getPrefix()] = $newGroup;
    }

    private function pushItem(MappingDeclarationItem $newItem): void
    {
        $urlParts = explode('/', $newItem->getUrl());
        if (count($urlParts) > 1) {
            $parentPrefix = array_shift($urlParts);
            $childUrl = implode('/', $urlParts);
            $parentGroup = $this->getOrCreateGroup($parentPrefix);
            $newItem->setUrl($childUrl);
            $parentGroup->push($newItem);
        } else $this->children[$newItem->getUrl()] = $newItem;
    }

    private function getOrCreateGroup(string $prefix): MappingDeclarationGroup
    {
        if (isset($this->children[$prefix]) && $this->children[$prefix] instanceof MappingDeclarationGroup)
            return $this->children[$prefix];
        $newGroup = new MappingDeclarationGroup($prefix, $prefix);
        $this->children[$prefix] = $newGroup;
        return $newGroup;
    }

    public function getMettaData(): string
    {
        $mettaData = "";
        foreach ($this->children as $child) $mettaData .= $child->getMettaData();
        return $mettaData;
    }

}