<?php

namespace Extra\Src;

use ApiRepository;
use METHOD;


enum API_DATA
{
    case GET;
    case POST;
    case JSON;
    case FILE;
}

/**
 *  Warframe collection
 *
 *  Api - api controller
 *
 *  @version 7.0
 *  @author itachi
 *  @package Extra\Src
 */
abstract class Api
{
    /** @var bool $isSecure check request header data */
    protected bool $isSecure = false;
    /** @var bool $cleanData status cleaning request data */
    protected bool $cleanData = true;

    /** @var array $headers request header data */
    private array $headers;
    /** @var int $pk token id */
    private int $pk;

    /** @var Repository $repo ApiRepository */
    public Repository $repo;
    /** @var array $uploadFileFormat upload file format */
    public array $uploadFileFormat;
    /** @var int $uploadFileSize upload file size (byte) */
    public int $uploadFileSize;

    /**
     * Constructor
     *
     * @return void
     */
    function __construct()
    {
        $this->AuthorizationHeader();
    }

    /**
     * Get Token id
     *
     * @return int
     */
    public function getPk(): int
    {
        return $this->pk;
    }

    /**
     * Set Token id
     *
     * @param int $pk
     */
    public function setPk(int $pk): void
    {
        $this->pk = $pk;
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

        } else Route::ThrowableApi(507, 'UploadFile: Error file not name.');
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
        } else return null;
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
                Route::ApiResponseError(400, 'The request is missing header data.');
        }
        else Route::ApiResponseError(500, 'The request is missing header data.');
        /*  ! Remove

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) $this->headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        elseif (isset($_SERVER['Authorization'])) $this->headers = trim($_SERVER["Authorization"]);
        elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) $this->headers = trim($requestHeaders['Authorization']);
        }
        if ($this->isSecure && empty($this->getHeaders())) Route::ApiResponseError(400, 'The request is missing header data.');

        */
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
        if (empty($token)) Route::ApiResponseError(400, 'Authorization data not found.');

        $object = $this->repo->getBy(['type' => 'Bearer', 'token' => $token]);
        if ($object) $this->pk = $object->id;
        else Route::ApiResponseError(401, 'Authorization failed.');
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
        if (empty($token)) Route::ApiResponseError(400, 'Authorization data not found.');
        $this->repo->Where("type = 'Basic' AND CONCAT(username, ':', password) = '{$token}'");

        $object = $this->repo->get();
        if ($object) $this->pk = $object->id;
        else Route::ApiResponseError(401, 'Authorization failed.');
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
     * Request raw data to ...(format API_DATA)
     *
     * @param API_DATA $apiDataType
     *
     * @return array
     */
    final protected function request(API_DATA $apiDataType): array
    {
        switch ($apiDataType) {
            case API_DATA::GET:
                if ($_GET) $data = $_GET;
                else Route::ThrowableApi(400, "There is no GET data in the request.");
                break;
            case API_DATA::POST:
                if ($_POST) $data = $_POST;
                else Route::ThrowableApi(400, "There is no POST data in the request.");
                break;
            case API_DATA::JSON:
                $data = file_get_contents('php://input');
                if ($data) $data = json_decode($data, true);
                else Route::ThrowableApi(400, "There is no JSON data in the request.");
                break;
            case API_DATA::FILE:
                if ($_FILES) $data = $_FILES;
                else Route::ThrowableApi(400, "There is no FILE data in the request.");
                break;

        }
        if ($this->cleanData) $this->cleaner($data);
        return $data;
    }

    /**
     * Request raw data to API_DATA::JSON
     *
     * @return mixed
     */
    final protected function requestJson(): mixed
    {
        return json_decode(file_get_contents('php://input'));
    }

    /**
     * Cleaner Method By Data
     *
     * The method cleans all data in the array from html tags and special characters
     *
     * @param array &$data array data
     *
     * @return void
     */
    protected final function cleaner(array &$data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) $this->cleaner($value);
            else $value = CDO::clean($value);
            $data[$key] = $value;
        }
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
            Route::ThrowableApi(400, "Field \"{$field}\" not found!");
        if ($validateFunc !== null) {
            if (!$validateFunc($data[$field]))
                Route::ThrowableApi(400, $message ?? "The \"{$field}\" field has the wrong data type!");
        }
    }

    /**
     * Api Response
     *
     * @param int $code HTTP code
     * @param mixed $data message
     *
     * @return void
     */
    protected function response(int $code, mixed $data = null): void
    {
        Route::ApiResponse($code, $data);
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
        Route::ApiResponseOk($data);
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
        Route::ApiResponseError($code, $data);
    }
}
