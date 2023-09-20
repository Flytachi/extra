<?php

namespace Extra\Src\Trait;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\HttpStatus;

trait JsonResponseTrait {
    final protected function renderJson(array $data): never
    {
        $httpCode = HttpCode::OK;
        $status = HttpStatus::status($httpCode);
        header("HTTP/1.1 {$httpCode->value} " . $status);
        header("Status: {$httpCode->value} " . $status);
        header('Content-type: application/json');
        echo json_encode( $data );
        die;
    }

    final protected function renderJsonSuccess(mixed $message = null): never
    {
        $httpCode = HttpCode::OK;
        $status = HttpStatus::status($httpCode);
        header("HTTP/1.1 {$httpCode->value} " . $status);
        header("Status: {$httpCode->value} " . $status);
        header('Content-type: application/json');
        echo json_encode( array('status' => 'success', 'message' => $message) );
        die;
    }

    final protected function renderJsonError(mixed $message): never
    {
        $httpCode = HttpCode::OK;
        $status = HttpStatus::status($httpCode);
        header("HTTP/1.1 {$httpCode->value} " . $status);
        header("Status: {$httpCode->value} " . $status);
        header('Content-type: application/json');
        echo json_encode( array('status' => 'error', 'message' => $message) );
        die;
    }
}