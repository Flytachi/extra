<?php

namespace Extra\Src;

use ApiRepository;
use METHOD;

/**
 *  Warframe collection
 * 
 *  Api - api controller
 * 
 *  @version 6.5
 *  @author itachi
 *  @package Extra\Src
 */
abstract class Api 
{   
    /** @var bool $isSecure check request header data */
    protected bool $isSecure = false;
    /** @var Repository $repo ApiRepository */
    public Repository $repo;
    /** @var string $repo request header data */
    private string $headers = '';

    /** @var array $uploadFileFormat upload file format */
    public array $uploadFileFormat;
    /** @var int $uploadFileFormat upload file size (byte) */
    public int $uploadFileSize;

    /**
     * Call
     */
    final function __call($name, $arguments)
    {
        Route::ThrowableApi(404, 'The "' . $name . '" function was not found or is not a public method');
        $this->AuthorizationHeader();
    }

    /**
     * Upload File
     * 
     * Saves the file in the folder PATH_MEDIA/'the name of the api controller'.
     * 
     * @param array $file variable from from array $_FILES[?]
     * 
     * @return string the path to the saved file
     */
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
                    Route::ThrowableApi(507, 'UploadFile: Error file is too big.');
        
                // File format
                if (empty($this->uploadFileFormat) or ($this->uploadFileFormat > 0 and (in_array($fileExtension, $this->uploadFileFormat) or $this->uploadFileFormat == $fileExtension)) ) {

                    if(move_uploaded_file($fileTmpPath, PATH_MEDIA . "/$uploadFolder/$newFileName")) return "$uploadFolder/$newFileName";
                    else Route::ThrowableApi(507, 'UploadFile: Error writing to storage.');
        
                } else Route::ThrowableApi(507, 'UploadFile: Error unsupported file format.');

            } else Route::ThrowableApi(507, 'UploadFile: Error loading to temporary folder.');
        
        }
    }

    /**
	 * Headers
	 * 
	 * @return string
	 */
    final protected function getHeaders(): string
    {
        return $this->headers;
    }

    /**
	 * Bearer Token
	 * 
	 * @return string|null
	 */
    final protected function getBearerToken(): string|null
    {
        if (!empty($this->headers)) {
            if (preg_match('/Bearer\s(\S+)/', $this->headers, $matches)) return $matches[1];
        }
        return null;
    }

    /**
	 * Basic Token
	 * 
	 * @return string|null
	 */
    final protected function getBasicToken(): string|null
    {
        if (!empty($this->headers)) {
            if (preg_match('/Basic\s(\S+)/', $this->headers, $matches)) return base64_decode($matches[1]);
        }
        return null;
    }

    /**
	 * Authorization Header
	 * 
	 * @return void
	 */
    private function AuthorizationHeader(): void
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) $this->headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        elseif (isset($_SERVER['Authorization'])) $this->headers = trim($_SERVER["Authorization"]);
        elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) $this->headers = trim($requestHeaders['Authorization']);
        }

        if($this->isSecure && empty($this->getHeaders())) Route::ApiError(400, 'The request is missing header data.');
    }

    /**
	 * Authorization Bearer
     * 
     * Bearer token authentication method
	 * 
	 * @return void
	 */
    final protected function authorizationBearer(): void
    {
        $this->repo = new ApiRepository;
        $token = $this->getBearerToken();
        if (empty($token)) Route::ApiError(400, 'Authorization data not found.');
        if (empty($this->repo->getBy(['type' => 'Bearer', 'token' => $token]))) Route::ApiError(401, 'Authorization failed.');
    }

    /**
	 * Authorization Basic
     * 
     * Basic authentication method
	 * 
	 * @return void
	 */
    final protected function authorizationBasic(): void
    {
        $this->repo = new ApiRepository;
        $token = $this->getBasicToken();
        if (empty($token)) Route::ApiError(400, 'Authorization data not found.');
        $this->repo->Where("type = 'Basic' AND CONCAT(username, ':', password) = '{$token}'");
        if (empty($this->repo->get())) Route::ApiError(401, 'Authorization failed.');
    }

    /**
	 * Allow method
     * 
     * @param METHOD ...$allowMethods allowed methods
	 * 
	 * @return void
	 */
    final protected function method(METHOD ...$allowMethods): void
    {
        foreach ($allowMethods as $method) {
            if($method->name === $_SERVER['REQUEST_METHOD']) return;
        }
        Route::ThrowableApi(405, 'Method ' . $_SERVER['REQUEST_METHOD'] . ' not allowed!');
    }

    /**
	 * Request raw data to json
	 * 
	 * @return mixed
	 */
    final protected function requestJson(): mixed
    {
        return json_decode(file_get_contents('php://input'));
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
        Route::ApiSuccess($data);
    }

    /**
	 * Api Error Response
	 * 
	 * @param int $code HTTP code
	 * @param mixed $data message
	 * 
	 * @return void
	 */
    protected function responseError(int $code, mixed $data = null): void
    {
        Route::ApiError($code, $data);
    }
}
