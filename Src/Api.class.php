<?php

namespace Extra\Src;

use ApiRepository;
use METHOD;

abstract class Api 
{
    /**
     * 
     * Api
     * 
     * @version 5.4 betta
     */
    
    private string $headers = '';
    public Repository $repo;

    public array $uploadFileFormat;
    public int $uploadFileSize;

    function __construct()
    {
        $this->AuthorizationHeader();
        // if (empty($this->getHeaders())) Route::ApiError(400);
        $this->repo = new ApiRepository;
    }

    final function __call($name, $arguments)
    {
        $this->responseError(404);
    }

    final protected function uploadFile(array $file): string
    {
        $uploadFolder = str_replace('Api', '', get_class($this));
        // $uploadDir = PATH_MEDIA . $uploadFolder;
        if( !is_dir(PATH_MEDIA . '/' . $uploadFolder) ) mkdir(PATH_MEDIA . '/' . $uploadFolder);

        if ( $file['name'] ) {
            // Upload File
            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileNameCms = explode(".", $file['name']);
                $fileExtension = strtolower(end($fileNameCms));
                $newFileName = sha1(time() . $file['name']) . '.' . $fileExtension;
                // $fileType = $file['type'];

                // File size
                if ($this->uploadFileSize > 0 and $this->uploadFileSize < $fileSize) 
                    $this->responseError(400, 'Error file is too big!');
        
                // File format
                if (empty($this->uploadFileFormat) or ($this->uploadFileFormat > 0 and (in_array($fileExtension, $this->uploadFileFormat) or $this->uploadFileFormat == $fileExtension)) ) {

                    if(move_uploaded_file($fileTmpPath, PATH_MEDIA . "/$uploadFolder/$newFileName")) return "$uploadFolder/$newFileName";
                    else $this->responseError(400, 'Error writing to database!');
        
                } else $this->responseError(400, 'Error unsupported file format!');

            } else $this->responseError(400, 'Error loading to temporary folder!'); 
        
        }
    }

    /*
    ---------------------------------------------
        AUTHORIZATION
    ---------------------------------------------
    */
    final protected function getHeaders(): string
    {
        return $this->headers;
    }

    final protected function getBearerToken(): string|null
    {
        if (!empty($this->headers)) {
            if (preg_match('/Bearer\s(\S+)/', $this->headers, $matches)) return $matches[1];
        }
        return null;
    }

    final protected function getBasicToken(): string|null
    {
        if (!empty($this->headers)) {
            if (preg_match('/Basic\s(\S+)/', $this->headers, $matches)) return $matches[1];
        }
        return null;
    }

    /* --------------------------------------------- */

    private function AuthorizationHeader(): void
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) $this->headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        elseif (isset($_SERVER['Authorization'])) $this->headers = trim($_SERVER["Authorization"]);
        elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) $this->headers = trim($requestHeaders['Authorization']);
        }
    }

    final protected function authorizationBearer(): void
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
    final protected function method(METHOD ...$allowMethods): void
    {
        foreach ($allowMethods as $method) {
            if($method->name === $_SERVER['REQUEST_METHOD']) return;
        }
        $this->responseError(405);
    }

    final protected function requestJson(): mixed
    {
        return json_decode(file_get_contents('php://input'));
    }
    /*
    ---------------------------------------------
    */

    /*
    ---------------------------------------------
        RESPONSE
    ---------------------------------------------
    */
    protected function responseSuccess(mixed $data = null): void
    {
        Route::ApiSuccess($data);
    }

    protected function responseError(int $code, mixed $data = null): void
    {
        Route::ApiError($code, $data);
    }
    /*
    ---------------------------------------------
    */
}
