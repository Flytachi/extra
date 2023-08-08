<?php

namespace Extra\Src\Type;

use Extra\Src\ModelInterface;
use Extra\Src\Route;
use ReflectionProperty;

class Cluster {
    public static function meta(ReflectionProperty $property): string
    {
        if (array_key_exists('0', $property->getAttributes())) {
            return self::logic($property, $property->getAttributes()[0]->getName());
        } else return $property->getName();
    }

    public static function transform(ModelInterface $model): array
    {
        try {
            $wrapper = [];
            $data = [];
            foreach ((new \ReflectionClass($model::class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (
                    array_key_exists('0', $property->getAttributes())
                    && $model->{$property->getName()}
                ) {
                    $prWrapper = $property->getAttributes()[0]->getName()::sqlDecode();
                    if (!is_null($prWrapper)) $wrapper[$property->getName()] = $prWrapper;
                    $data[$property->getName()] = $property->getAttributes()[0]->getName()::sqlEncode($model->{$property->getName()});
                } else $data[$property->getName()] = $model->{$property->getName()};
            }

            return [
                'data' => $data,
                'wrapper' => $wrapper
            ];
        } catch (\Throwable $e) {
            Route::Throwable(500, $e->getMessage());
        }

    }

    private static function logic(ReflectionProperty $property, string $type): string
    {
        switch ($type) {
            case PolygonType::class:
            case LineStringType::class:
            case FigureType::class:
            case PointType::class:
            case MultiPointType::class:
            case MultiLineStringType::class:
            case MultiPolygonType::class:
                return "AsText(`" . $property->getName() . "`) '" . $property->getName() .  "'";
                break;
            default:
                return  '`' . $property->getName() . '`';
                break;
        }
    }
}