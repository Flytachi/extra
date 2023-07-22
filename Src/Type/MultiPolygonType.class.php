<?php

namespace Extra\Src\Type;

use Attribute;

#[Attribute] class MultiPolygonType implements Type
{
    private array $polygons;

    public function __construct(array|PolygonType ...$polygons)
    {
        if ($polygons[0] instanceof PolygonType) $this->polygons = $polygons;
        else $this->polygons = $polygons[0];
    }

    /**
     * @return array
     */
    public function getPolygons(): array
    {
        return $this->polygons;
    }

    /**
     * @return PolygonType
     */
    public function getPolygonFirst(): PolygonType
    {
        return $this->polygons[0];
    }

    /**
     * @return PolygonType
     */
    public function getPolygonLast(): PolygonType
    {
        return $this->polygons[array_key_last($this->polygons)];
    }

    public static function parse(string|null|self $value): self|null
    {
        if ($value instanceof self) return $value;
        elseif (is_null($value)) return null;
        else {
            $dataParsed = explode(')),((', trim(str_replace('MULTIPOLYGON', '', $value), '()'));
            $polygons = [];
            foreach ($dataParsed as $dataOn) {
                $dataOn = explode('),(', $dataOn);
                $figures = [];
                foreach ($dataOn as $data) {
                    $points = [];
                    foreach (explode(',', $data) as $point) {
                        $pointData = explode(' ', $point);
                        $points[] = new PointType($pointData[0], $pointData[1]);
                    }
                    $figures[] = new FigureType($points);
                }
                $polygons[] = new PolygonType($figures);
            }

            return new MultiPolygonType($polygons);
        }
    }

    public static function sqlEncode(mixed $value): string
    {
        if ($value instanceof self) {
            $polygons = '';
            foreach ($value->getPolygons() as $polygon) {
                $figures = '';
                foreach ($polygon->getFigures() as $figure) {
                    $points = '';
                    foreach ($figure->getPoints() as $point)
                        $points .= "{$point->getX()} {$point->getY()},";
                    $figures .= '(' . rtrim($points, ',') . '),';
                }
                $polygons .= '(' . rtrim($figures, ',') . '),';
            }

            return 'MULTIPOLYGON(' . rtrim($polygons, ',') . ')';
        }
        elseif (is_string($value)) return $value;
        else throw new \TypeError(self::class . " data type error.");
    }

    public static function sqlDecode(): string|null
    {
        return "PolygonFromText(?)";
    }
}