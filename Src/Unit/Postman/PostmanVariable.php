<?php

namespace Extra\Src\Unit\Postman;

class PostmanVariable implements Postman
{
    private string $key;
    private string $value;
    private string $type;
    private bool $disabled;

    /**
     * @param string $key
     * @param string $value
     * @param string $type
     * @param bool $disabled
     */
    public function __construct(string $key, string $value, string $type = 'string', bool $disabled = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;
        $this->disabled = $disabled;
    }

    public function prepare(array &$arrayData): void {
        $arrayData[] = [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'disabled' => $this->disabled
        ];
    }

    public static function morph(self ...$variables): array
    {
        $result = [];
        foreach ($variables as $variable) $variable->prepare($result);
        return $result;
    }

}