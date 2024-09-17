<?php

namespace Extra\Src\Factory\Mapping\OpenApi;

use Extra\Src\Factory\Mapping\Mapping;
use Extra\Src\Sheath\File\JSON;

/**
 * Class OpenApi
 *
 * @version 3.1.0
 * @author Flytachi
 */
class OpenApi
{
    const VERSION = '3.1.0';

    public static function generate(): void
    {
        $collection = self::collection();
        $collection['servers']['url'] = 'https://petstore3.swagger.io/api/v3';

        dd(
            Mapping::scanningDeclaration(),
            $collection
        );
    }

    private static function collection(): array
    {
        return [
            'openapi' => self::VERSION,
            'info' => [
                'title' => 'Test Title',
                'version' => '1.0.0',
                'description' => 'Description test ...'
            ],
            'paths' => []
        ];
    }
}