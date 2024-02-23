<?php

namespace Extra\Src\Controller;

use Extra\Src\HttpCode;
use Extra\Src\Log\Log;
use Extra\Src\Request\Request;
use Extra\Src\Route\Response;

/**
 * Class ApiBase
 *
 * `ApiBase` is an abstract class serving as the base for all API controllers in the application.
 * It provides basic methods for handling API requests that can be inherited by child controller classes.
 *
 * The methods provided by `ApiBase` include:
 *
 * - `__construct(): void`: Constructor that initializes specific services and authorizes HEADER data and CORS.
 * - `method(Method ...$allowMethods): void`: Allows certain HTTP methods for the request.
 * - `getBearerToken(): string|null`: Extracts the Bearer token from the Authorization header, if present.
 * - `getBasicToken(): string|null`: Extracts the Basic token from the Authorization header and decodes it, if present.
 * - `response(HttpCode $httpCode, mixed $data = null): void`: Sends a json response with provided HTTP code and data.
 * - `responseOk(mixed $data = null): void`: Sends a json response with HTTP code 200.
 * - `responseMessage(HttpCode $httpCode, string $message = ''): void`: Sends a json response with a message and provided HTTP code.
 * - `textResponse(HttpCode $httpCode, string $text = '', bool $htmlEntities = false): void`: Sends a text response with provided HTTP code and text.
 *
 * @version 9.5
 * @author Flytachi
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
        Response::json($httpCode, $data);
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
        Response::json(HttpCode::OK, $data);
    }

    /**
     * Api Response Message
     *
     * @param HttpCode $httpCode
     * @param string $message
     *
     * @return void
     */
    protected function responseMessage(HttpCode $httpCode, string $message = ''): void
    {
        Response::jsonMessage($httpCode, $message);
    }

    /**
     * Text Response
     *
     * @param HttpCode $httpCode
     * @param string $text
     * @param bool $htmlEntities
     *
     * @return void
     */
    protected function textResponse(HttpCode $httpCode, string $text = '', bool $htmlEntities = false): void
    {
        Response::text($httpCode, $text, $htmlEntities);
    }

}
