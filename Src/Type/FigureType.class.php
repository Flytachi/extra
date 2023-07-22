<?php

namespace Extra\Src\Type;

use Attribute;

#[Attribute] class FigureType implements Type {
    private array $points;

    public function __construct(array|PointType ...$points)
    {
        if ($points[0] instanceof PointType) $this->points = $points;
        else $this->points = $points[0];

        if (count($this->points) < 3) throw new \TypeError(self::class . " the shape must consist of more than 3 points.");
        if (!$this->getPointFirst()->equal($this->getPointLast()))
            throw new \TypeError(self::class . " the start and end points must be identical.");
    }

    /**
     * @return array
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    /**
     * @return PointType
     */
    public function getPointFirst(): PointType
    {
        return $this->points[0];
    }

    /**
     * @return PointType
     */
    public function getPointLast(): PointType
    {
        return $this->points[array_key_last($this->points)];
    }

    public static function parse(string|null|self $value): self|null
    {
        if ($value instanceof self) return $value;
        elseif (is_null($value)) return null;
        else {
            $dataParsed = explode(',', trim(str_replace('LINESTRING', '', $value), '()'));
            $points = [];
            foreach ($dataParsed as $data) {
                $pointData = explode(' ', $data);
                $points[] = new PointType($pointData[0], $pointData[1]);
            }
            return new self($points);
        }
    }

    public static function sqlEncode(mixed $value): string
    {
        if ($value instanceof self) {
            $points = "";
            foreach ($value->points as $point)
                $points .= "{$point->getX()} {$point->getY()},";
            return 'LINESTRING(' . rtrim($points, ',') . ')';
        }
        elseif (is_string($value)) return $value;
        else throw new \TypeError(self::class . " data type error.");
    }

    public static function sqlDecode(): string|null
    {
        return "ST_LineStringFromText(?)";
    }
}