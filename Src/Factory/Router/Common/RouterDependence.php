<?php

namespace Extra\Src\Factory\Router\Common;

use Extra\Src\Controller\ApiBase;
use Extra\Src\Controller\ControllerBase;
use Extra\Src\Factory\Router\RouteError;
use Extra\Src\HttpCode;

trait RouterDependence
{
    protected final static function splitUrlAndParams(string $url): array
    {
        $parsedUrl = parse_url($url);
        $urlWithoutParams = $parsedUrl['path'];
        $params = [];
        if (isset($parsedUrl['query'])) parse_str($parsedUrl['query'], $params);

        return [
            'url' => $urlWithoutParams,
            'params' => $params
        ];
    }

    /**
     * @param array{class: class-string<ApiBase>|class-string<ControllerBase>, method: string} $action
     * @param array<int, string> $params
     * @param string $stringUrl
     * @return mixed
     */
    protected final static function callNodeAction(array $action, array $params = [], string $stringUrl = ''): mixed
    {
        $controller = new $action['class']();
        $methods = get_class_methods($controller);

        if (!in_array($action['method'], $methods)) RouteError::throw(HttpCode::BAD_GATEWAY,
            "{$_SERVER['REQUEST_METHOD']} '{$stringUrl}' url realization '{$action['method']}' not found");

        try {
            return call_user_func_array([$controller, $action['method']], $params);
        } catch (\TypeError $exception) {
            $temp = $controller::class . "::" . $action['method'] . '():';
            if (str_starts_with($exception->getMessage(), $temp)) RouteError::throw(
                HttpCode::BAD_REQUEST, str_replace($temp . " ", '', $exception->getMessage()));
            else throw $exception;
        }
    }

}