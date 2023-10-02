<?php

namespace Extra\Src\Controller;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\Method;
use Extra\Src\Log\Log;
use Extra\Src\Request\Request;
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
