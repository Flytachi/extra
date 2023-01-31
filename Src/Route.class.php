<?php

namespace Extra\Src;

use Exception;
use Throwable;
use Warframe;

/**
 *  Warframe collection
 * 
 *  Route - routing system
 * 
 * 	@version 13.2
 * 	@author itachi
 * 	@package Extra\Src
 */
class Route
{
	/** @var $httpStatus array HTTP array codes */
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

	/**
	 * Start routing system
	 * 
	 * @return void
	 */
	final static function start(): void
	{
		if (ROUTE_PLUGIN_SYSTEM) self::routePlugin();
		else self::routeApp();	
	}

	/**
	 * Controller AutoLoader
	 * 
	 * @return void
	 */
	final static function loader(): void
    {
		spl_autoload_register(function($class) {
            $file = dirname(__FILE__, 3) . '/controllers/' . $class . '.php';
			if (file_exists($file)) require $file;
        });
	}

	/**
	 * Plugin Controller AutoLoader
	 * 
	 * @return void
	 */
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
	
	/**
	 * Checking the post request
	 * 
	 * @return void
	 * 
	 * @throws Exception PHP discarded POST data because of request exceeding post_max_size.
	 */
	final static function changePostSize(): void
	{
		if($_SERVER['REQUEST_METHOD'] === "POST" && intval($_SERVER['CONTENT_LENGTH']) > 0 && count($_POST) === 0) {
			self::Throwable(413, 'PHP discarded POST data because of request exceeding post_max_size.');
        }
	}
	
	/**
	 * Standard routing
	 * 
     * @return never
	 * 
	 * @throws Throwable if debugging is enabled, it will return an error message
     */
	final static function routeApp(): never
	{
		self::loader();
		$controllerName = ROUTE_MAIN_CONTROLLER;
		$actionName = ROUTE_MAIN_ACTION;
		$params = null;
		
		$data = self::urlToArray($_SERVER['REQUEST_URI']);
		$routes = explode('/', $data['url']);

		if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
		if ( $controllerName === 'Api' ) self::routeApi($data);
		if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
		if ( !empty($routes[3]) ) {
			$params = array_slice($routes, 3);
			if(count($params) == 1) $params = $routes[3];
		}
		$_GET = $data['get'];
		self::changePostSize();

		// Prefix
		$controllerName = $controllerName . 'Controller';
		
		// Imports
		$funcPath = dirname(__DIR__, 2) . '/functions.php';
		if ( file_exists($funcPath) ) require $funcPath;
		
		// Imitation
		if(!class_exists($controllerName)) self::Throwable(404, 'The "' . $controllerName .'" controller was not found');
		try {
            self::imitation($controllerName, $actionName, $params);
		} catch (Throwable $e) {
			self::Throwable(500, $e->getMessage());
		}
		exit;
	}

	/**
	 * Routing for plugins
	 * 
     * @return never
	 * 
	 * @throws Throwable if debugging is enabled, it will return an error message
     */
	final static function routePlugin(): never
	{
		self::pluginLoader();
		$pluginName = "";
		$controllerName = ROUTE_PLUGIN_MAIN_CONTROLLER;
		$actionName = ROUTE_MAIN_ACTION;
		$params = null;

		$data = self::urlToArray($_SERVER['REQUEST_URI']);
		$routes = explode('/', $data['url']);

		if ( !empty($routes[1]) ) $pluginName = ucfirst($routes[1]);
		if ( $pluginName === 'Api' ) self::routeApi($data);

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
			self::changePostSize();
			
			// Prefix
			$controllerName = "$pluginName\\" . $controllerName . 'Controller';
		
			// Imports
			$funcPath = PATH_PLUGIN . "/Frame.$pluginName/functions.php";
        } else {
			if ( !empty($routes[1]) ) $controllerName = ucfirst($routes[1]);
			if ( !empty($routes[2]) ) $actionName = ucfirst($routes[2]);
			if ( !empty($routes[3]) ) $params = ucfirst($routes[3]);
			$_GET = $data['get'];
			self::changePostSize();
			
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
			self::Throwable(500, $e->getMessage());
		}
		exit;
	}

	/**
	 * Routing for api requests
	 * 
     * @param array $data 
	 *  * @return array[url] URL string
     *  * @return array[get] GET params array
	 * 
     * @return never
	 * 
	 * @throws Throwable if debugging is enabled, it will return an error message
     */
	final static function routeApi(array $data): never
	{
		if (!ROUTE_API_SYSTEM) self::ApiResponseError(400);
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
			self::ThrowableApi(500, $e->getMessage());
		}
		exit;
	}

    /**
	 * Start imitation controller
	 *  
     * @param string $controllerName controller name
     * @param string $actionName controller public method
     * @param array|string|null $params method params
	 * 
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

	/**
	 * URL to array 
	 *  
     * @param string $url url address and params
	 * 
     * @return array 
	 *  * @return array[url] URL string
     *  * @return array[get] GET params array
     */
	final static function urlToArray(string $url): array
    {
        $code = explode('?', urldecode($url));
        $result = [];
		if (isset($code[1])) {
			foreach (explode('&', $code[1]) as $param) {
				if ($param) {
					$value = explode('=', $param);
					$result[$value[0]] = $value[1];
				}
			}
		}
        return ['url' => $code[0], 'get' => $result];
    }

	/**
	 * Change Session key 'id'
	 * 
     * @param bool|int $redirect status action redirect 
	 * 
	 * @return void
	 */
	static function isAuth(bool|int $redirect = false): void
	{
		if ($redirect) {
			if (empty($_SESSION['id'])) self::redirect('auth/login');
		} else {
			if (empty($_SESSION['id'])) self::Throwable(423, 'You are not authorized');
		}
	}

	/**
	 * Change Session key 'is_admin'
	 * 
     * @param bool|int $redirect status action redirect 
	 * 
	 * @return void
	 */
	static function isAuthAdmin(bool|int $redirect = false): void
	{
		self::isAuth($redirect);
		if (empty($_SESSION['is_admin']) or $_SESSION['is_admin'] !== 1) self::Throwable(423, 'You are not logged in as an administrator');
	}
	
	/**
	 * Redirect
	 * 
     * @param ?string $url /url address
     * @param ?array $param get parametrs
	 * 
	 * @return never
	 */
	final static function redirect(?string $url = null, ?array $param = null): never
	{
		if ($url) header('location: /' . $url . arrayToRequest($param));
		else header('location:' . $_SERVER['HTTP_REFERER']);
		exit();
	}
	
	/**
	 * Render error page
	 * 
     * @param int $code index http error code
	 * 
	 * @return never
	 */
	final static function ErrorPage(int $code): never
	{
        header("HTTP/1.1 $code " . self::$httpStatus[$code]);
		header("Status: $code " . self::$httpStatus[$code]);
		$page = PATH_PUBLIC . '/' . VIEW_ERROR . "/$code.php";
		if (file_exists($page)) die( include $page );
		else {
			$_error = $code . ' ' . self::$httpStatus[$code];
			die( include PATH_PUBLIC . '/' . VIEW_ERROR . "/system.php" );
		}
		die;
	}

	/**
	 * Response format to json
	 * 
	 * @param array $data json data
	 * 
	 * @return never
	 */
	final static function responseJson(array $data): never
	{
        header('Content-type: application/json');
		echo json_encode($data);
		die;
	}

	/**
	 * Api Success Response
	 * 
	 * HTTP code - 200
	 * 
	 * @param mixed $data message
	 * 
	 * @return never
	 */
	final static function ApiResponseOk(mixed $data = null): never
	{
		$code = 200;
		$status = self::$httpStatus['200'];
		header_remove("X-Powered-By");
		header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");
		header("HTTP/1.1 $code " . $status);
		header("Status: $code " . $status);
		echo json_encode(array(
			'statusCode' => $code,
			'statusDescription' => $status,
			'data' => $data
		));
		die;
	}

	/**
	 * Api Error Response
	 *  
	 * @param int $code index http error code
	 * @param mixed $data message
	 * 
	 * @return never
	 */
	final static function ApiResponseError(int $code, mixed $data = null): never
	{
		$status = self::$httpStatus[$code];
		header_remove("X-Powered-By");
		header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
		header("Access-Control-Allow-Methods: *"); 
		header("Content-Type: application/json");
		header("HTTP/1.1 $code " . $status);
		header("Status: $code " . $status);
		echo json_encode(array(
			'statusCode' => $code,
			'statusDescription' => $status,
			'data' => $data
		));
		die;
	}

	/**
	 * Throwable Warframe function
	 * 
	 * If debugging is enabled, then it will show in detail where the error is located, 
	 * as well as output its own description of the error. 
	 * 
	 * If debugging is disabled it will return an error page with the specified code
	 * 
     * @param int $code index http error code
     * @param string $title error description
	 * 
	 * @return never
	 */
	final static function Throwable(int $code, string $title): never
    {
        if (Warframe::$cfg['GLOBAL_SETTING']['DEBUG']) {
            $message = self::getThrowableMessage($code, $title);
            echo "<strong style=\"font-size:16px; color: #ffffff;\"> Warframe Debug Message</strong><hr>";
			print_r($message);
			echo '<hr></pre>';
            die();
        }
        else self::ErrorPage($code);
    }

	/**
	 * Throwable Api Warframe function
	 * 
	 * If debugging is enabled, then it will show in detail where the error is located, 
	 * as well as output its own description of the error. 
	 * 
	 * If debugging is disabled it will return an api error with the specified code
	 * 
     * @param int $code index http error code
     * @param string $title error description
	 * 
	 * @return never
	 */
	final static function ThrowableApi(int $code, string $title): never
    {
        if (Warframe::$cfg['GLOBAL_SETTING']['DEBUG']) {
            $message = self::getThrowableMessage($code, $title);
            echo "<strong style=\"font-size:16px; color: #ffffff;\"> Warframe Api Debug Message</strong><hr>";
			print_r($message);
			echo '<hr></pre>';
            die();
        }
        else self::ApiResponseError($code);
    }

    /**
     * Throwable Message Warframe function
     *
     * @param int $code
     * @param string $title
     *
     * @return string error message
     */
    private static function getThrowableMessage(int $code, string $title): string
    {
        header("HTTP/1.1 $code " . self::$httpStatus[$code]);
        header("Status: $code " . self::$httpStatus[$code]);
        $tColor = match ((int)($code / 100)) {
            1 => "00ffff",
            2 => "00ff00",
            3 => "ff00e0",
            4 => "ffff00",
            5 => "ff0000",
            default => "dddddd",
        };
        $message = "\t <strong style=\"font-size:14px;\">" . $title . '</strong>';
        foreach (debug_backtrace() as $key => $value) {
            if ($key != 0) {
                $message .= "\n\t\t#" . $key . ' ';
                if (isset($value['file'])) $message .= $value['file'];
                if (isset($value['line'])) $message .= ' (' . $value['line'] . '): ';
                if (isset($value['class'])) $message .= "\t" . $value['class'];
                if (isset($value['type'])) $message .= $value['type'];
                if (isset($value['function'])) $message .= $value['function'];
            }
        }
        echo '<pre style="background-color: black; color: #' . $tColor . '; border-style: solid; border-color: #ff0000; border-width: medium; padding:7px; padding-top:13px">';
        return $message;
    }
}
