<?php

namespace Extra\Src;

use ApiRepository;
use Extra\Src\CDO\CDN;
use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\Method;
use Extra\Src\Repo\Repository;
use ReflectionClass;


/**
 *  Warframe collection
 *
 *  Api - api controller
 *
 *  @version 8.0
 *  @author itachi
 *  @package Extra\Src
 */
abstract class Api
{
    /** @var bool $isSecure check request header data */
    protected bool $isSecure = false;

    /** @var array $headers request header data */
    private array $headers;

    /**
     * Constructor
     *
     * @return void
     */
    function __construct()
    {
        Route::$isApi = true;
        $this->AuthorizationCORS();
        $this->AuthorizationHeader();

        $reflect = new ReflectionClass($this);
        foreach ($reflect->getProperties() as $property) {
            if (strrpos($property->getType(), 'Service'))
                $this->{$property->getName()} = new ($property->getType()->getName());
        }
    }

    /**
     * Authorization CORS
     *
     * @return void
     */
    private function AuthorizationCORS(): void
    {
        if ($_SERVER['REQUEST_METHOD'] == Method::OPTIONS->name) $this->responseOk();
    }

    /**
     * Authorization Header
     *
     * @return void
     */
    private function AuthorizationHeader(): void
    {
        if (function_exists('apache_request_headers')) {
            $this->headers = apache_request_headers();
            $this->headers = array_combine(array_map('ucwords', array_keys($this->headers)), array_values($this->headers));
            if ($this->isSecure && empty($this->headers['Authorization']))
                Route::Throwable(HttpCode::BAD_REQUEST, 'The request is missing header data.');
        }
        else Route::Throwable(HttpCode::INTERNAL_SERVER_ERROR, 'The request is missing header data.');
    }

    /**
     * Headers
     *
     * @param string|null $headerKey
     *
     * @return array|string
     */
    final protected function getHeaders(?string $headerKey = null): array|string
    {
        return ($headerKey) ? $this->headers[$headerKey] : $this->headers;
    }

    /**
     * Bearer Token
     *
     * @return string|null
     */
    final protected function getBearerToken(): string|null
    {
        if (array_key_exists('Authorization', $this->headers)) {
            if (preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) return $matches[1];
            else return null;
        } else return null;
    }

    /**
     * Basic Token
     *
     * @return string|null
     */
    final protected function getBasicToken(): string|null
    {
        if (array_key_exists('Authorization', $this->headers)) {
            if (preg_match('/Basic\s(\S+)/', $this->headers['Authorization'], $matches)) return base64_decode($matches[1]);
            else return null;
        } else return null;
    }

    /**
     * Allow method
     *
     * @param Method ...$allowMethods allowed methods
     *
     * @return void
     */
    final protected function method(Method ...$allowMethods): void
    {
        foreach ($allowMethods as $method) if($method->name === $_SERVER['REQUEST_METHOD']) return;
        Route::Throwable(HttpCode::METHOD_NOT_ALLOWED, 'Method ' . $_SERVER['REQUEST_METHOD'] . ' not allowed!');
    }

    /**
     * Validate Method
     *
     * Checking the existence of a value in the data.
     *
     * If you set the argument "validateFunc" will check the
     * data on the function with the condition that the
     * function returns a bool value, and takes 1 argument
     *
     * @param array $data data -> array data
     * @param string $field field name -> array key
     * @param callable|null $validateFunc validation func returned bool!
     * @param string|null $message message with incorrect validation in func
     *
     * @return void
     */
    protected final function valid(array $data, string $field, callable $validateFunc = null, string $message = null): void
    {
        if (!array_key_exists($field, $data))
            Route::Throwable(HttpCode::BAD_REQUEST, "Field \"{$field}\" not found!");
        if ($validateFunc !== null) {
            if (!$validateFunc($data[$field]))
                Route::Throwable(HttpCode::BAD_REQUEST, $message ?? "The \"{$field}\" field has the wrong data type!");
        }
    }

    /**
     * Api Response
     *
     * @param HttpCode $httpCode
     * @param mixed $data message
     *
     * @return void
     */
    protected function response(HttpCode $httpCode, mixed $data = null): void
    {
        Route::ApiResponse($httpCode, $data);
    }

    /**
     * Api Ok Response
     *
     * HTTP code - 200
     *
     * @param mixed $data message
     *
     * @return void
     */
    protected function responseOk(mixed $data = null): void
    {
        Route::ApiResponse(HttpCode::OK, $data);
    }

}
