<?php

namespace Extra\Src;

use Warframe;

class Route
{
	/**
     * 
     * Route
     * 
     * @version 7.0
     */

	
	static array $httpStatus = array(
		200 => 'Success',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		419 => 'Authentication Timeout (not in RFC 2616)',
		423 => 'Locked',
		500 => 'Internal Server Error',
		503 => 'Service Unavailable',
	);

	final static function start(): void
	{
		if (ROUTE_PLUGIN_SYSTEM) Route::routePlugin();
		else Route::routeApp();	
	}

	final static function loader()
	{
		spl_autoload_register(function($class) {
            $file = dirname(__FILE__, 3) . '/controllers/' . $class . '.php';
			if (file_exists($file)) require $file;
        });
	}

	/* final static function pluginLoader($pluginName)
	{
		spl_autoload_register(function($class) {
			$file = dirname(__FILE__, 3) . FOLDER_PLUGIN . "/Frame.$pluginName/controllers/" . $class . '.php';
			if (file_exists($file)) require $file;
        });
	} */
	
	final static function routeApp(): never
	{
		Route::loader();
		$controllerName = ROUTE_MAIN_CONTROLLER;
		$actionName = ROUTE_MAIN_ACTION;
		$params = null;
		
		$data = Route::urlToArray($_SERVER['REQUEST_URI']);
		$routes = explode('/', $data['url']);

		if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
		if ( $controllerName === 'Api' ) Route::routeApi($data);
		if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
		if ( !empty($routes[3]) ) $params = ucfirst($routes[3]);
		$_GET = $data['get'];

		// Prefix
		$controllerName = $controllerName . 'Controller';
		
		// Imports
		$funcPath = dirname(__DIR__, 2) . '/functions.php';
		if ( file_exists($funcPath) ) require $funcPath;
		
		// Imitation
		if(!class_exists($controllerName)) Route::ErrorPage(404);
		try {
			$controller = new $controllerName;
			$controller->$actionName($params);
		} catch (\Throwable $e) {
			if (cfgGet()['GLOBAL_SETTING']['DEBUG']) dd($e);
			else Route::ErrorPage(400);
		}
		exit;
	}

	final static function routePlugin(): never
	{
		$pluginName = "";
		$controllerName = ROUTE_PLUGIN_MAIN_CONTROLLER;
		$actionName = ROUTE_MAIN_ACTION;
		$params = null;

		$data = Route::urlToArray($_SERVER['REQUEST_URI']);
		$routes = explode('/', $data['url']);

		if ( !empty($routes[1]) ) $pluginName = ucfirst($routes[1]);
		if ( $pluginName === 'Api' ) Route::routeApi($data);

		// Checking
		if (checkPlugin($pluginName)) {
			/*
			Route::pluginLoader($pluginName);
			$path = dirname(__DIR__, 4) . '/' . FOLDER_PLUGIN . "/Frame.$pluginName/__frame__.php";
			if ( file_exists($path) ) require $path;
			if ( !empty($routes[2]) ) $controllerName = ucfirst($routes[2]);
			if ( !empty($routes[3]) ) $actionName = ucfirst($routes[3]);
			if ( !empty($routes[4]) ) $params = ucfirst($routes[4]);
			$_GET = $data['get'];
			
			// Prefix
			$controllerName = $controllerName . 'Controller';
		
			// Imports
			$funcPath = dirname(__DIR__, 4) . '/' . FOLDER_PLUGIN . "/Frame.$pluginName/functions.php";
			if ( file_exists($funcPath) ) require $funcPath;
			// importPluginRepository(PLUGIN_NAME, $repositoryName);
			// importPluginController(PLUGIN_NAME, $controllerName);
			*/
		} else {
			Route::loader();
			if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
			if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
			if ( !empty($routes[3]) ) $params = ucfirst($routes[3]);
			$_GET = $data['get'];
			
			// Prefix
			$controllerName = $controllerName . 'Controller';

			// Imports
			$funcPath = dirname(__DIR__, 2) . '/functions.php';
			if ( file_exists($funcPath) ) require $funcPath;
		}
		
		// Imitation
		try {
			$controller = new $controllerName;
			$controller->$actionName($params);
		} catch (\Throwable $e) {
			if (cfgGet()['GLOBAL_SETTING']['DEBUG']) dd($e);
			else Route::ErrorPage(400);
		}
		exit;
	}

	final static function routeApi(array $data): never
	{
		if (!ROUTE_API_SYSTEM) Route::ApiError(400);
		$routes = explode('/', $data['url']);
		$_GET = $data['get'];
		$chP = checkPlugin(ucfirst($routes[2]));
		if (ROUTE_PLUGIN_SYSTEM and $chP) {
			$pluginName     = ( !empty($routes[2]) ) ? ucfirst($routes[2]) : null;
			$controllerName = ( !empty($routes[3]) ) ? ucfirst($routes[3]) : null;
			$actionName     = ( !empty($routes[4]) ) ? ucfirst($routes[4]) : null;
			$params         = ( !empty($routes[5]) ) ? ucfirst($routes[5]) : null;
		} else {
			$controllerName = ( !empty($routes[2]) ) ? ucfirst($routes[2]) : null;
			$actionName     = ( !empty($routes[3]) ) ? ucfirst($routes[3]) : null;
			$params         = ( !empty($routes[4]) ) ? ucfirst($routes[4]) : null;
		}
		
		// Prefix
		$controllerName = $controllerName . 'Api';
		
		// Imports
		if (ROUTE_PLUGIN_SYSTEM and $chP) {
			$path = dirname(__DIR__, 4) . '/' . FOLDER_PLUGIN . "/Frame.$pluginName/__frame__.php";
			if ( file_exists($path) ) require $path;
			// importPluginApi(PLUGIN_NAME, $controllerName);
		} else {
			spl_autoload_register(function($class) {
				$file = dirname(__FILE__, 3) . '/api/' . $class . '.php';
				if (file_exists($file)) require $file;
			});
			$funcPath = dirname(__DIR__, 3) . '/functions.php';
			if ( file_exists($funcPath) ) require $funcPath;
		}
		
		// Imitation
		try {
			$controller = new $controllerName;
			$controller->$actionName($params);
		} catch (\Throwable $e) {
			if (cfgGet()['GLOBAL_SETTING']['DEBUG']) dd($e);
			else Route::ApiError(500);
		}
		exit;
	}

	final static function urlToArray(string $url): array
    {
        $code = explode('?', $url);
        $result = [];
		if (isset($code[1])) {
			foreach (explode('&', $code[1]) as $param) {
				if ($param) {
					$value = explode('=', $param);
					$result[$value[0]] = $value[1];
				}
			}
		}
        return array('url' => $code[0], 'get' => $result);
    }

	static function isAuth(bool|int $redirect = false):void
	{
		if ($redirect) {
			if (empty($_SESSION['id'])) Route::redirect('auth/login');
		} else {
			if (empty($_SESSION['id'])) Route::ErrorPage(423);
		}
	}

	static function isAuthAdmin(bool|int $redirect = false):void
	{
		if ($redirect) {
			if (empty($_SESSION['id'])) Route::redirect('auth/login');
		} else {
			if (empty($_SESSION['id'])) Route::ErrorPage(423);
		}
		if (empty($_SESSION['is_admin']) or $_SESSION['is_admin'] !== 1) Route::ErrorPage(423);
	}
	
	final static function redirect(string $url = null, array $param = null): never
	{
		if ($url) header('location: /' . $url . arrayToRequest($param));
		else header('location:' . $_SERVER['HTTP_REFERER']);
		exit();
	}
	
	final static function ErrorPage(int $code): never
	{
        header("HTTP/1.1 $code " . Route::$httpStatus[$code]);
		header("Status: $code " . Route::$httpStatus[$code]);
		if(explode('/', $_SERVER['PHP_SELF'])[1] != 'error') die( include VIEW_FOLDER . "/error/$code.php" );
		die;
	}

	final static function ErrorResponseJson(array $data): never
	{
        header('Content-type: application/json');
		echo json_encode($data);
		die;
	}

	final static function ApiSuccess(mixed $data = null): never
	{
		$code = 200;
		$status = Route::$httpStatus['200'];
		header("HTTP/1.1 $code " . $status);
		header("Status: $code " . $status);
		header('Content-type: application/json');
		echo json_encode(array(
			'statusCode' => $code,
			'statusDescription' => $status,
			'result' => $data
		));
		die;
	}

	final static function ApiError(int $code, array $data = []): never
	{
		$status = Route::$httpStatus[$code];
		header("HTTP/1.1 $code " . $status);
		header("Status: $code " . $status);
		header('Content-type: application/json');
		echo json_encode(array(
			'statusCode' => $code,
			'statusDescription' => $status,
			'result' => $data
		));
		die;
	}
}

?>