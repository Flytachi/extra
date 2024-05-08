<?php

namespace Extra\Src\Artefact\Type\Mariadb;

use Extra\Src\Artefact\Type\Type;

class TPolygon implements Type
{
    /** @var TFigure[] */
    public array $figures;

    public function __construct(TFigure ...$figures)
    {
        $this->figures = $figures;
    }

    /**
     * @return array
     */
    public function getFigures(): array
    {
        return $this->figures;
    }

    /**
     * @return TFigure
     */
    public function getFigureFirst(): TFigure
    {
        return $this->figures[0];
    }

    /**
     * @return TFigure
     */
    public function getFigureLast(): TFigure
    {
        return $this->figures[array_key_last($this->figures)];
    }

    public static function parse(mixed $value): static|null
    {
        if ($value instanceof static) return $value;
        elseif (is_null($value)) return null;
        else {
            $dataParsed = explode('),(', trim(str_replace('POLYGON', '', $value), '()'));
            $figures = [];
            foreach ($dataParsed as $data) {
                $points = [];
                foreach (explode(',', $data) as $point) {
                    $pointData = explode(' ', $point);
                    $points[] = new TPoint($pointData[0], $pointData[1]);
                }
                $figures[] = new TFigure(...$points);
            }
            return new static(...$figures);
        }
    }

    public static function selectionLabel(): string
    {
        return 'AsText(%1$s) \'%1$s\'';
    }

    public static function prepairing(): string
    {
        return 'PolygonFromText(%s)';
    }

    public function __toString() {
        $figures = '';
        foreach ($this->getFigures() as $figure) {
            $points = '';
            foreach ($figure->getPoints() as $point)
                $points .= "{$point->getX()} {$point->getY()},";
            $figures .= '(' . rtrim($points, ',') . '),';
        }
        return 'POLYGON(' . rtrim($figures, ',') . ')';
    }
}