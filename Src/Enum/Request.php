<?php

namespace Extra\Src\Enum;

use Extra\Src\Route;

class Request
{
    public static function get(bool $required = true): array
    {
        if ($required && !$_GET) Route::Throwable(HttpCode::BAD_REQUEST, "There is no GET data in the request.");
        return $_GET;
    }

    public static function post(bool $required = true): array
    {
        if ($required && !$_POST) Route::Throwable(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return $_POST;
    }

    public static function form(bool $required = true): array
    {
        if ($required && !$_POST) Route::Throwable(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return $_POST;
    }

    public static function json(bool $required = true): array
    {
        $data = file_get_contents('php://input');
        if ($required && !$data) Route::Throwable(HttpCode::BAD_REQUEST, "There is no JSON data in the request.");
        return json_decode($data, true);
    }

    public static function files(): array
    {
        if (!$_FILES) Route::Throwable(HttpCode::BAD_REQUEST, "There is no FILE data in the request.");
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
        return $data;
    }
}