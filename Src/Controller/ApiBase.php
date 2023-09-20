<?php

namespace Extra\Src\Controller;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\Method;
use Extra\Src\Enum\Request;
use Extra\Src\Error\ExtraException;
use Extra\Src\Log\Log;
use Extra\Src\Route\Route;
use ReflectionClass;

/**
 *  Warframe collection
 *
 *  ApiBase - api controller
 *
 *  @version 9.0
 *  @author itachi
 *  @package Extra\Src
 */
abstract class ApiBase
{
    /** @var bool $isSecure check request header data */
    protected bool $isSecure = false;

    /**
     * Constructor
     *
     * @return void
     */
    function __construct()
    {
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
        Log::trace('Api authorization: CORS');
        if ($_SERVER['REQUEST_METHOD'] == Method::OPTIONS->name) $this->responseOk();
    }

    /**
     * Authorization Header
     *
     * @return void
     */
    private function AuthorizationHeader(): void
    {
        Log::trace('Api authorization: Header');
        if ($this->isSecure && !Request::getHeader('Authorization'))
            ControllerError::throw(HttpCode::BAD_REQUEST, 'The request is missing header data.');
    }


    /**
     * Bearer Token
     *
     * @return string|null
     */
    final protected function getBearerToken(): string|null
    {
        if ($auth = Request::getHeader('Authorization')) {
            if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) return $matches[1];
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
        if ($auth = Request::getHeader('Authorization')) {
            if (preg_match('/Basic\s(\S+)/', $auth, $matches)) return base64_decode($matches[1]);
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
        Log::trace('Api method: change method');
        foreach ($allowMethods as $method) if($method->name === $_SERVER['REQUEST_METHOD']) return;
        ControllerError::throw(HttpCode::METHOD_NOT_ALLOWED, 'Method ' . $_SERVER['REQUEST_METHOD'] . ' not allowed!');
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
        Log::trace('Api valid: field' . $field);
        if (!array_key_exists($field, $data))
            ControllerError::throw(HttpCode::BAD_REQUEST, "Field \"{$field}\" not found!");
        if ($validateFunc !== null) {
            if (!$validateFunc($data[$field]))
                ControllerError::throw(HttpCode::BAD_REQUEST, $message ?? "The \"{$field}\" field has the wrong data type!");
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
        if($httpCode->value >= 400) ControllerError::throw($httpCode, $data ?? $httpCode->name);
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
