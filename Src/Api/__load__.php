<?php

namespace Extra\Src;

abstract class Api 
{
    /**
     * 
     * Api
     * 
     * @version 1.7
     */
    
    private string $headers = '';

    function __construct()
    {
        $this->AuthorizationHeader();
        if (!$this->headers) Route::ApiError(400);
    }

    final public function AuthorizationHeader(): void
    {
        if (isset($_SERVER['Authorization'])) $this->headers = trim($_SERVER["Authorization"]);
        elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) $this->headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) $this->headers = trim($requestHeaders['Authorization']);
        }
    }

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
    
}

?>