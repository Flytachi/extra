<?php

namespace Extra\Src\Factory\Entity\Request\Common;

use Extra\Src\Factory\Entity\EntityError;
use Extra\Src\HttpCode;

trait RequestObjectTrait
{
    /**
     * Retrieves the GET data from the request.
     *
     * @param bool $required (Optional) Specifies whether the GET data is required. Default is true.
     *
     * @return static A new instance of the class representing the GET data from the request.
     */
    public static function get(bool $required = true): static
    {
        if ($required && !$_GET) EntityError::throw(HttpCode::BAD_REQUEST, "There is no GET data in the request.");
        return new static($_GET);
    }

    /**
     * Retrieves the POST data from the request.
     *
     * @param bool $required (Optional) Specifies whether the POST data is required. Default is true.
     *
     * @return static A new instance of the class representing the POST data from the request.
     */
    public static function post(bool $required = true): static
    {
        if ($required && !$_POST) EntityError::throw(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return new static($_POST);
    }

    /**
     * Retrieves the POST data from the request.
     *
     * @param bool $required (Optional) Specifies whether the POST data is required. Default is true.
     *
     * @return static A new instance of the class representing the POST data from the request.
     */
    public static function form(bool $required = true): static
    {
        if ($required && !$_POST) EntityError::throw(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return new static($_POST);
    }

    /**
     * Retrieves the JSON data from the request.
     *
     * @param bool $required (Optional) Specifies whether the JSON data is required. Default is true.
     *
     * @return static A new instance of the class representing the JSON data from the request.
     */
    public static function json(bool $required = true): static
    {
        $data = file_get_contents('php://input');
        if ($required && !$data) EntityError::throw(HttpCode::BAD_REQUEST, "There is no JSON data in the request.");
        return new static(json_decode($data, true) ?? []);
    }

    /**
     * Retrieves the FILE data from the request.
     *
     * @return static A new instance of the class representing the FILE data from the request.
     */
    public static function files(): static
    {
        if (!$_FILES) EntityError::throw(HttpCode::BAD_REQUEST, "There is no FILE data in the request.");
        $data = [];
        foreach ($_FILES as $fileName => $fileData) {
            $data[$fileName] = [];
            foreach ($fileData as $fileDataKey => $fileDataItem) {
                if (is_array($fileDataItem)) {
                    foreach ($fileDataItem as $iKey => $iValue)
                        $data[$fileName][$iKey][$fileDataKey] = $iValue;
                } else $data[$fileName][$fileDataKey] = $fileDataItem;
            }
        }
        return new static($data);
    }
}