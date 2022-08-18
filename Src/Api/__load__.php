<?php

namespace Extra\Src;

abstract class Api 
{
    /**
     * 
     * Api
     * 
     * @version 2.0
     */
    
    private string $headers = '';
    public Repository $repo;

    function __construct()
    {
        $this->AuthorizationHeader();
        if (empty($this->getHeaders())) Route::ApiError(400);
    }

    final public function setRepository(string $repositoryName): void
    {
        $this->repo = new $repositoryName;
    }

    /*
    ---------------------------------------------
        AUTHORIZATION
    ---------------------------------------------
    */
    final public function getHeaders(): string
    {
        return $this->headers;
    }

    final public function getBearerToken(): string|null
    {
        if (!empty($this->headers)) {
            if (preg_match('/Bearer\s(\S+)/', $this->headers, $matches)) return $matches[1];
        }
        return null;
    }

    final public function getBasicToken(): string|null
    {
        if (!empty($this->headers)) {
            if (preg_match('/Basic\s(\S+)/', $this->headers, $matches)) return $matches[1];
        }
        return null;
    }
    
    /* --------------------------------------------- */

    private function AuthorizationHeader(): void
    {
        if (isset($_SERVER['Authorization'])) $this->headers = trim($_SERVER["Authorization"]);
        elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) $this->headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) $this->headers = trim($requestHeaders['Authorization']);
        }
    }

    final public function authorizationBearer(): void
    {
        $token = $this->getBearerToken();
        if (empty($token)) Route::ApiError(400);
        if (empty($this->repo->getBy(array('token' => $token)))) Route::ApiError(401);
    }
    /*
    ---------------------------------------------
    */

    /*  
    ---------------------------------------------
        REQUEST
    ---------------------------------------------
    */
    final public function requestJson(): mixed
    {
        return json_decode(file_get_contents('php://input'));
    }
    /*
    ---------------------------------------------
    */
}

?>