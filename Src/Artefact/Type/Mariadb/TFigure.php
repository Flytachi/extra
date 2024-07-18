<?php

namespace Extra\Src\Artefact\Type\Mariadb;

use Extra\Src\Artefact\Type\Type;

class TFigure extends TLineString implements Type
{
    /** @var TPoint[] */
    public array $points;

    public function __construct(TPoint ...$points)
    {
        parent::__construct(...$points);
        if (count($this->points) < 3) throw new \TypeError(self::class . " the shape must consist of more than 3 points.");
        if (!$this->getPointFirst()->equal($this->getPointLast()))
            throw new \TypeError(self::class . " the start and end points must be identical.");
    }

}