<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Entity;

use Flytachi\Extra\Src\Factory\Http\HttpCode;

class Request extends \stdClass implements RequestInterface
{
    /**
     * Retrieves the GET data from the request.
     *
     * @param bool $required (Optional) Specifies whether the GET data is required. Default is true.
     *
     * @return static A new instance of the class representing the GET data from the request.
     * @throws EntityException
     */
    public static function params(bool $required = true): static
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
    public static function formData(bool $required = true): static
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
            $class = new static();
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $class->$key = $value;
                }
            }
            return $class;
        } catch (\Exception $e) {
            throw new EntityException($e->getMessage(), HttpCode::BAD_REQUEST->value, $e);
        }
    }
}
