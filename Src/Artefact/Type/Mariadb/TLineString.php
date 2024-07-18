<?php

namespace Extra\Src\Artefact\Type\Mariadb;

use Extra\Src\Artefact\Type\Type;

class TLineString implements Type
{
    /** @var TPoint[] */
    public array $points;

    public function __construct(TPoint ...$points)
    {
        $this->points = $points;
    }

    /**
     * @return array
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    /**
     * @return TPoint
     */
    public function getPointFirst(): TPoint
    {
        return $this->points[0];
    }

    /**
     * @return TPoint
     */
    public function getPointLast(): TPoint
    {
        return $this->points[array_key_last($this->points)];
    }

    public static function parse(mixed $value): static|null
    {
        if ($value instanceof static) return $value;
        elseif (is_null($value)) return null;
        else {
            $dataParsed = explode(',', trim(str_replace('LINESTRING', '', $value), '()'));
            $points = [];
            foreach ($dataParsed as $data) {
                $pointData = explode(' ', $data);
                $points[] = new TPoint($pointData[0], $pointData[1]);
            }
            return new static(...$points);
        }
    }

    public static function selectionLabel(): string
    {
        return 'AsText(%1$s) \'%1$s\'';
    }

    public static function prepairing(): string
    {
        return 'ST_LineStringFromText(%s)';
    }

    public function __toString() {
        $points = '';
        foreach ($this->points as $point)
            $points .= "{$point->getX()} {$point->getY()},";
        return 'LINESTRING(' . rtrim($points, ',') . ')';
    }
}