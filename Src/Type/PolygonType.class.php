<?php

namespace Extra\Src\Type;

use Attribute;

#[Attribute] class PolygonType implements Type
{
    private array $figures;

    public function __construct(array|FigureType ...$figures)
    {
        if ($figures[0] instanceof FigureType) $this->figures = $figures;
        else $this->figures = $figures[0];
    }

    /**
     * @return array
     */
    public function getFigures(): array
    {
        return $this->figures;
    }

    /**
     * @return FigureType
     */
    public function getFigureFirst(): FigureType
    {
        return $this->figures[0];
    }

    /**
     * @return FigureType
     */
    public function getFigureLast(): FigureType
    {
        return $this->figures[array_key_last($this->figures)];
    }

    public static function parse(string|null|self $value): self|null
    {
        if ($value instanceof self) return $value;
        elseif (is_null($value)) return null;
        else {
            $dataParsed = explode('),(', trim(str_replace('POLYGON', '', $value), '()'));
            $figures = [];
            foreach ($dataParsed as $data) {
                $points = [];
                foreach (explode(',', $data) as $point) {
                    $pointData = explode(' ', $point);
                    $points[] = new PointType($pointData[0], $pointData[1]);
                }
                $figures[] = new FigureType($points);
            }
            return new PolygonType($figures);
        }
    }

    public static function sqlEncode(mixed $value): string
    {
        if ($value instanceof self) {
            $figures = '';
            foreach ($value->getFigures() as $figure) {
                $points = '';
                foreach ($figure->getPoints() as $point)
                    $points .= "{$point->getX()} {$point->getY()},";
                $figures .= '(' . rtrim($points, ',') . '),';
            }

            return 'POLYGON(' . rtrim($figures, ',') . ')';
        }
        elseif (is_string($value)) return $value;
        else throw new \TypeError(self::class . " data type error.");
    }

    public static function sqlDecode(): string|null
    {
        return "PolygonFromText(?)";
    }
}