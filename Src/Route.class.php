<?php

namespace Extra\Src;

use Throwable;

class Route
{
	/**
     * 
     * Route
     * 
     * @version 9.7
     */

	
	static array $httpStatus = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		419 => 'Authentication Timeout (not in RFC 2616)',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended'
	];

	final static function start(): void
	{
		if (ROUTE_PLUGIN_SYSTEM) Route::routePlugin();
		else Route::routeApp();	
	}

	final static function loader(): void
    {
		spl_autoload_register(function($class) {
            $file = dirname(__FILE__, 3) . '/controllers/' . $class . '.php';
			if (file_exists($file)) require $file;
        });
	}

	final static function pluginLoader(): void
    {
		spl_autoload_register(function($class) {
			$class = explode("\\", $class);
			if (ROUTE_PLUGIN_SYSTEM && count($class) > 1) {
				$file = PATH_PLUGIN . "/Frame." . $class[0] . "/controllers/" . $class[1] . '.php';
			} else {
				$file = PATH_APP . '/controllers/' . $class[0] . '.php';
			}
			if (file_exists($file)) require $file;
        });
	}
	
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
		if ( !empty($routes[3]) ) {
			$params = array_slice($routes, 3);
			if(count($params) == 1) $params = $routes[3];
		}
		$_GET = $data['get'];

		// Prefix
		$controllerName = $controllerName . 'Controller';
		
		// Imports
		$funcPath = dirname(__DIR__, 2) . '/functions.php';
		if ( file_exists($funcPath) ) require $funcPath;
		
		// Imitation
		if(!class_exists($controllerName)) Route::ErrorPage(404);
		try {
            self::imitation($controllerName, $actionName, $params);
		} catch (Throwable $e) {
			if (cfgGet()['GLOBAL_SETTING']['DEBUG']) dd($e);
			else Route::ErrorPage(400);
		}
		exit;
	}

	final static function routePlugin(): never
	{
		Route::pluginLoader();
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
			$path = PATH_PLUGIN . "/Frame.$pluginName/__frame__.php";
			if ( file_exists($path) ) require $path;
			if ( !empty($routes[2]) ) $controllerName = ucfirst($routes[2]);
			if ( !empty($routes[3]) ) $actionName = ucfirst($routes[3]);
			if ( !empty($routes[4]) ) {
				$params = array_slice($routes, 4);
				if(count($params) == 1) $params = $routes[4];
			}
			$_GET = $data['get'];
			
			// Prefix
			$controllerName = "$pluginName\\" . $controllerName . 'Controller';
		
			// Imports
			$funcPath = PATH_PLUGIN . "/Frame.$pluginName/functions.php";
        } else {
			if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
			if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
			if ( !empty($routes[3]) ) $params = ucfirst($routes[3]);
			$_GET = $data['get'];
			
			// Prefix
			$controllerName = $controllerName . 'Controller';

			// Imports
			$funcPath = dirname(__DIR__, 2) . '/functions.php';
        }
        if ( file_exists($funcPath) ) require $funcPath;

        // Imitation
		try {
            self::imitation($controllerName, $actionName, $params);
        } catch (Throwable $e) {
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
		$chP =  (isset($routes[2])) ? checkPlugin(ucfirst($routes[2])) : null;
		if (ROUTE_PLUGIN_SYSTEM and $chP) {
			$pluginName     = ( !empty($routes[2]) ) ? ucfirst($routes[2]) : null;
			$controllerName = ( !empty($routes[3]) ) ? ucfirst($routes[3]) : null;
			$actionName     = ( !empty($routes[4]) ) ? ucfirst($routes[4]) : null;
			if ( !empty($routes[5]) ) {
				$params = array_slice($routes, 5);
				if(count($params) == 1) $params = $routes[5];
			} else $params = null;

            // Imports
            spl_autoload_register(function($class) {
                $class = explode("\\", $class);
                $file = PATH_PLUGIN . "/Frame." . $class[0] . "/api/" . $class[1] . '.php';
                if (file_exists($file)) require $file;
            });

            // Prefix
            $controllerName = "$pluginName\\" . $controllerName . 'Api';
		} else {
            $controllerName = (!empty($routes[2])) ? ucfirst($routes[2]) : null;
            $actionName = (!empty($routes[3])) ? ucfirst($routes[3]) : null;
            if (!empty($routes[4])) {
                $params = array_slice($routes, 4);
                if (count($params) == 1) $params = $routes[4];
            } else $params = null;

            // Imports
            spl_autoload_register(function ($class) {
                $file = dirname(__FILE__, 3) . '/api/' . $class . '.php';
                if (file_exists($file)) require $file;
            });
            $funcPath = dirname(__DIR__, 3) . '/functions.php';
            if (file_exists($funcPath)) require $funcPath;

            // Prefix
            $controllerName = $controllerName . 'Api';
        }
		
		// Imitation
		try {
            self::imitation($controllerName, $actionName, $params);
        } catch (Throwable $e) {
			if (cfgGet()['GLOBAL_SETTING']['DEBUG']) dd($e);
			else Route::ApiError(400);
		}
		exit;
	}

    /**
     * @param string $controllerName
     * @param string $actionName
     * @param array|string|null $params
     * @return void
     */
    private static function imitation(string $controllerName, string $actionName, array|string|null $params): void
    {
        spl_autoload_register(function ($class) {
            $class = explode("\\", $class);
            if (ROUTE_PLUGIN_SYSTEM && count($class) > 1) {
                $file = PATH_PLUGIN . "/Frame." . $class[0] . "/repository/" . $class[1] . '.php';
            } else {
                $file = PATH_APP . '/repository/' . $class[0] . '.php';
            }
            if (file_exists($file)) require $file;
        });
        $controller = new $controllerName;
        $controller->$actionName($params);
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
		$page = PATH_PUBLIC . '/' . VIEW_ERROR . "/$code.php";
		if (file_exists($page)) die( include $page );
		else {
			$_error = $code . ' ' . Route::$httpStatus[$code];
			die( include PATH_PUBLIC . '/' . VIEW_ERROR . "/system.php" );
		}
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
		header_remove("X-Powered-By");
		header("Access-Control-Allow-Orgin: *"); 
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");
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

	final static function ApiError(int $code, mixed $data = null): never
	{
		$status = Route::$httpStatus[$code];
		header_remove("X-Powered-By");
		header("Access-Control-Allow-Orgin: *"); 
		header("Access-Control-Allow-Methods: *"); 
		header("Content-Type: application/json");
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