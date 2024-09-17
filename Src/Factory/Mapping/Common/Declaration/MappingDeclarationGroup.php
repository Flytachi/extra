<?php

namespace Extra\Src\Factory\Mapping\Common\Declaration;

class MappingDeclarationGroup
{
    private string $title;
    private string $prefix;
    /**
     * @var array<MappingDeclarationItem>
     */
    private array $children = [];

    /**
     * @param string $title
     * @param string $prefix
     * @param MappingDeclarationItem[]|MappingDeclarationItem $children
     */
    public function __construct(string $title, string $prefix, array $children = [])
    {
        $this->title = $title;
        $this->prefix = $prefix;
        $this->children = $children;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function push(MappingDeclarationGroup|MappingDeclarationItem $newChild): void
    {
        if ($newChild instanceof MappingDeclarationGroup) {
            $prefixParts = explode('/', $newChild->getPrefix());
            if (count($prefixParts) > 1) {
                $parentPrefix = array_shift($prefixParts);
                $childPrefix = implode('/', $prefixParts);
                $parentGroup = $this->getOrCreateGroup($parentPrefix);
                $newChild->setPrefix($childPrefix);
                $parentGroup->push($newChild);
            } else $this->children[] = $newChild;
        } else {
            $urlParts = explode('/', $newChild->getUrl());
            if (count($urlParts) > 1) {
                $parentPrefix = array_shift($urlParts);
                $childUrl = implode('/', $urlParts);
                $parentGroup = $this->getOrCreateGroup($parentPrefix);
                $newChild->setUrl($childUrl);
                $parentGroup->push($newChild);
            } else $this->children[] = $newChild;
        }
    }

    private function getOrCreateGroup(string $prefix): MappingDeclarationGroup
    {
        foreach ($this->children as $child) {
            if ($child instanceof MappingDeclarationGroup
                && $child->getPrefix() == $prefix) return $child;
        }
        $newGroup = new MappingDeclarationGroup($prefix, $prefix);
        $this->children[] = $newGroup;
        return $newGroup;
    }

    public function getMettaData(): string
    {
        $childMettaData = "";
        foreach ($this->children as $child) {
            if ($child instanceof MappingDeclarationGroup) {
                $groupMettaData = explode(PHP_EOL,  $child->getMettaData());
                foreach ($groupMettaData as $key => $groupMettaDataItem) $groupMettaData[$key] = "\t" . $groupMettaDataItem;
                $childMettaData .= implode(PHP_EOL, $groupMettaData);
            } else $childMettaData .= "\t" . $child->getMettaData();
        }
        return PHP_EOL . "// {$this->title}" . PHP_EOL
            . "Router::group(['prefix' => '{$this->prefix}'], function() {"
            . PHP_EOL . rtrim($childMettaData)
            . PHP_EOL . "});" . PHP_EOL;
    }

}