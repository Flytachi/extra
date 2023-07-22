<?php

namespace Extra\Src\Type;

use Attribute;

#[Attribute] class PointType implements Type
{
    private float|int $x;
    private float|int $y;

    /**
     * @param float|int $x
     * @param float|int $y
     */
    public function __construct(float|int $x = 0, float|int $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param self $point
     * @return bool
     */
    public function equal(self $point): bool
    {
        return $this->x === $point->x && $this->y === $point->y;
    }

    /**
     * @return float|int
     */
    public function getX(): float|int
    {
        return $this->x;
    }

    /**
     * @return float|int
     */
    public function getY(): float|int
    {
        return $this->y;
    }

    public static function parse(string|null|self $value): self|null
    {
        if ($value instanceof self) return $value;
        elseif (is_null($value)) return null;
        else {
            $dataParsed = explode(' ', trim(str_replace('POINT', '', $value), '()'));
            return new self($dataParsed[0], $dataParsed[1]);
        }
    }

    public static function sqlEncode(mixed $value): string
    {
        if ($value instanceof self) return "POINT({$value->x} $value->y)";
        elseif (is_string($value)) return $value;
        else throw new \TypeError(self::class . " data type error.");
    }

    public static function sqlDecode(): string|null
    {
        return "ST_GeomFromText(?)";
    }
}