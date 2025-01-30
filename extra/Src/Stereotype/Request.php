<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Stereotype;

use ArgumentCountError;
use Error;
use Flytachi\Extra\Src\Factory\Entity\EntityException;
use Flytachi\Extra\Src\Factory\Entity\RequestBase;
use Flytachi\Extra\Src\Factory\Entity\RequestInterface;
use Flytachi\Extra\Src\Factory\Http\HttpCode;
use TypeError;

abstract class Request extends RequestBase
{
}
