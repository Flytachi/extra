<?php

namespace Extra\Src\Type;

use Attribute;

#[Attribute] class MultiLineStringType implements Type
{
    private array $lineStrings;

    public function __construct(array|LineStringType ...$lineStrings)
    {
        if ($lineStrings[0] instanceof LineStringType) $this->lineStrings = $lineStrings;
        else $this->lineStrings = $lineStrings[0];
    }

    /**
     * @return array
     */
    public function getLineStrings(): array
    {
        return $this->lineStrings;
    }

    /**
     * @return LineStringType
     */
    public function getLineStringFirst(): LineStringType
    {
        return $this->lineStrings[0];
    }

    /**
     * @return LineStringType
     */
    public function getLineStringLast(): LineStringType
    {
        return $this->lineStrings[array_key_last($this->lineStrings)];
    }

    public static function parse(string|null|self $value): self|null
    {
        if ($value instanceof self) return $value;
        elseif (is_null($value)) return null;
        else {
            $dataParsed = explode('),(', trim(str_replace('MULTILINESTRING', '', $value), '()'));
            $lineStrings = [];
            foreach ($dataParsed as $data) {
                $points = [];
                foreach (explode(',', $data) as $point) {
                    $pointData = explode(' ', $point);
                    $points[] = new PointType($pointData[0], $pointData[1]);
                }
                $lineStrings[] = new LineStringType($points);
            }
            return new MultiLineStringType($lineStrings);
        }
    }

    public static function sqlEncode(mixed $value): string
    {
        if ($value instanceof self) {
            $lineStrings = '';
            foreach ($value->getLineStrings() as $figure) {
                $points = '';
                foreach ($figure->getPoints() as $point)
                    $points .= "{$point->getX()} {$point->getY()},";
                $lineStrings .= '(' . rtrim($points, ',') . '),';
            }

            return 'MULTILINESTRING(' . rtrim($lineStrings, ',') . ')';
        }
        elseif (is_string($value)) return $value;
        else throw new \TypeError(self::class . " data type error.");
    }

    public static function sqlDecode(): string|null
    {
        return "ST_LineStringFromText(?)";
    }
}