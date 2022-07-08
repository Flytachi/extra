<?php

class Route
{

	static $ErrorStatus = array(
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		419 => 'Authentication Timeout (not in RFC 2616)',
		423 => 'Locked',
		500 => 'Internal Server Error',
		503 => 'Service Unavailable',
	);

	static function start()
	{
		if (ROUTE_PLUGIN_SYSTEM) Route::routePlugin();
		else Route::routeApp();	
	}

	static function routePlugin()
	{
		$pluginName = "";
		$controllerName = ROUTE_PLUGIN_MAIN_CONTROLLER;
		$actionName = ROUTE_MAIN_ACTION;
		$params = null;

		$data = Route::urlToArray($_SERVER['REQUEST_URI']);
		$routes = explode('/', $data['url']);

		if ( !empty($routes[1]) ) $pluginName = ucfirst($routes[1]);
		
		// Imports
		if (checkPlugin($pluginName)) {
			if ( !empty($routes[2]) ) $controllerName = ucfirst($routes[2]);
			if ( !empty($routes[3]) ) $actionName = ucfirst($routes[3]);
			if ( !empty($routes[4]) ) $params = ucfirst($routes[4]);
			$_GET = $data['get'];
			
			// Prefix
			$modelName = $controllerName . 'Model';
			$controllerName = $controllerName . 'Controller';

			importPluginModel($pluginName, $modelName);
			importPluginController($pluginName, $controllerName);
		} else {
			if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
			if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
			if ( !empty($routes[3]) ) $params = ucfirst($routes[3]);
			$_GET = $data['get'];
			
			// Prefix
			$modelName = $controllerName . 'Model';
			$controllerName = $controllerName . 'Controller';

			importModel($modelName);
			importController($controllerName);
		}
		
		// Imitation
		$controller = new $controllerName;
		if(class_exists($modelName)) $controller->setModel($modelName);
		if(is_callable([$controller, $actionName])) {
			// вызываем действие контроллера
			try {
				$controller->$actionName($params);
			} catch (Throwable $e) {
				if (cfgGet()['GLOBAL_SETTING']['DEBUG']) dd($e);
				else Route::ErrorPage(400);
			}
		} else {
			// здесь также разумнее было бы кинуть исключение
			Route::ErrorPage(404);
		}
	}
	
	static function routeApp()
	{
		$controllerName = ROUTE_MAIN_CONTROLLER;
		$actionName = ROUTE_MAIN_ACTION;
		$params = null;
		
		$data = Route::urlToArray($_SERVER['REQUEST_URI']);
		$routes = explode('/', $data['url']);

		if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
		if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
		if ( !empty($routes[3]) ) $params = ucfirst($routes[3]);
		$_GET = $data['get'];

		// Prefix
		$modelName = $controllerName . 'Model';
		$controllerName = $controllerName . 'Controller';
		
		// Imports
		importModel($modelName);
		importController($controllerName);
		
		// Imitation
		$controller = new $controllerName;
		if(class_exists($modelName)) $controller->setModel($modelName);
		if(is_callable([$controller, $actionName])) {
			// вызываем действие контроллера
			try {
				$controller->$actionName($params);
			} catch (Throwable $e) {
				if (cfgGet()['GLOBAL_SETTING']['DEBUG']) dd($e);
				else Route::ErrorPage(400);
			}
		} else {
			// здесь также разумнее было бы кинуть исключение
			Route::ErrorPage(404);
		}
	}

	static function urlToArray(string $url)
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

	static function isAuth($redirect = false){
		if ($redirect) {
			if (empty($_SESSION['id'])) Route::redirect("auth/login");
		} else {
			if (empty($_SESSION['id'])) Route::ErrorPage(423);
		}
	}
	
	static function redirect(String $url = null, Array $param = null) {
		if ($url) header("location: /$url " . arrayToRequest($param));
		else header("location:" . $_SERVER['HTTP_REFERER']);
	}
	
	static function ErrorPage($code)
	{
        header("HTTP/1.1 $code " . Route::$ErrorStatus["$code"]);
		header("Status: $code " . Route::$ErrorStatus["$code"]);
		if(explode('/', $_SERVER['PHP_SELF'])[1] != 'error') die( include VIEW_FOLDER . "/error/$code.php" );
		die;
	}

	static function ErrorResponseJson($data)
	{
        header('Content-type: application/json');
		echo json_encode($data);
		die;
	}
	
}

?>