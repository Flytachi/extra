<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Entity;

use ArgumentCountError;
use Error;
use Flytachi\Extra\Src\Factory\Http\HttpCode;
use TypeError;

abstract class RequestBase implements RequestInterface
{
    /**
     * Retrieves the GET data from the request.
     *
     * @param bool $required (Optional) Specifies whether the GET data is required. Default is true.
     *
     * @return static A new instance of the class representing the GET data from the request.
     * @throws EntityException
     */
    final public static function params(bool $required = true): static
    {
        if ($required && !$_GET) {
            throw new EntityException("Missing required data for request", HttpCode::BAD_REQUEST->value);
        }
        return self::from($_GET);
    }

    /**
     * Retrieves the POST data from the request.
     *
     * @param bool $required (Optional) Specifies whether the POST data is required. Default is true.
     *
     * @return static A new instance of the class representing the POST data from the request.
     * @throws EntityException
     */
    final public static function formData(bool $required = true): static
    {
        if ($required && !$_POST) {
            throw new EntityException("Missing required data for request", HttpCode::BAD_REQUEST->value);
        }
        return self::from($_POST);
    }

    /**
     * Retrieves the JSON data from the request.
     *
     * @param bool $required (Optional) Specifies whether the JSON data is required. Default is true.
     *
     * @return static A new instance of the class representing the JSON data from the request.
     * @throws EntityException
     */
    final public static function json(bool $required = true): static
    {
        $data = file_get_contents('php://input');
        if ($required && (!$data || !json_validate($data))) {
            throw new EntityException("Missing required data for request", HttpCode::BAD_REQUEST->value);
        }
        return self::from(json_decode($data, true));
    }

    /**
     * @param mixed $data
     * @return static
     * @throws EntityException
     */
    private static function from(mixed $data): static
    {
        try {
            if (empty($data)) {
                return new static();
            } else {
                return new static(...$data);
            }
        } catch (ArgumentCountError $e) {
            $errorMessage = preg_replace(
                '/.*Argument #\d+ \(\$(\w+)\) not passed/',
                'Required field \'$1\' not found',
                $e->getMessage()
            );
            $errorMessage = preg_replace(
                '/Too few arguments to function .*, (\d+) passed .*/',
                'Missing required data for request',
                $errorMessage
            );

            throw new EntityException($errorMessage, HttpCode::BAD_REQUEST->value, $e);
        } catch (TypeError $e) {
            $errorMessage = preg_replace(
                '/.*Argument #\d+ \(\$(\w+)\) must be of type (\w+), (\w+) given.*/',
                "Invalid type field '$1' (required: '$2', given: '$3')",
                $e->getMessage()
            );
            throw new EntityException($errorMessage, HttpCode::BAD_REQUEST->value, $e);
        } catch (Error $e) {
            $errorMessage = preg_replace(
                '/Unknown named parameter \$(\w+)/',
                "Undefined field '$1'",
                $e->getMessage()
            );
            throw new EntityException($errorMessage, HttpCode::BAD_REQUEST->value, $e);
        }
    }
}
