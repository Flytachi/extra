<?php

namespace Extra\Src\Artefact\Type\Mariadb;

use Extra\Src\Artefact\Type\Type;

class TPoint implements Type
{
    public float|int $x;
    public float|int $y;

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
     * @param static $point
     * @param string $unit (kilometers or miles)
     * @param int $round
     * @return float
     */
    public function distance(self $point, string $unit = 'km', int $round = 2): float
    {
        $theta = $this->y - $point->y;
        $distance = (sin(deg2rad($this->x)) * sin(deg2rad($point->x))) +
            (cos(deg2rad($this->x)) * cos(deg2rad($point->x)) * cos(deg2rad($theta)));
        $distance = rad2deg(acos($distance)) * 60 * 1.1515;
        switch($unit) {
            case 'ml': break;
            case 'km': $distance *= 1.609344;
        }
        return (round($distance, $round));
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

    public static function parse(mixed $value): static|null
    {
        if ($value instanceof static) return $value;
        elseif (is_null($value)) return null;
        else {
            $dataParsed = explode(' ', str_replace(['(','POINT',')'], '', $value));
            return new static($dataParsed[0], $dataParsed[1]);
        }
    }

    public static function selectionLabel(): string
    {
        return 'AsText(%1$s) \'%1$s\'';
    }

    public static function prepairing(): string
    {
        return "GeomFromText(%s)";
    }

    public function __toString() {
        return "POINT($this->x $this->y)";
    }

}