<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\OpenApi;

enum DataType
{
    case JSON;
    case FORM;
    case QUERY;
}
