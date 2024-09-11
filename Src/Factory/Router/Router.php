<?php

namespace Extra\Src\Factory\Router;

use Extra\Src\Artefact\ArtefactError;
use Extra\Src\Artefact\CDO\CDOError;
use Extra\Src\Controller\Method;
use Extra\Src\Entity\Request\Request;
use Extra\Src\Error\ExtraException;
use Extra\Src\Factory\Mapping\Mapping;
use Extra\Src\Factory\Router\Common\RouteNode;
use Extra\Src\Factory\Router\Common\RouterDependence;
use Extra\Src\Factory\Router\Common\RouterInterface;
use Extra\Src\Factory\Router\Common\RouterRequest;
use Extra\Src\HttpCode;
use Extra\Src\Log\Log;
use Extra\Src\Repo\RepositoryError;
use Extra\Src\Sheath\SheathException;

/**
 * Class Router
 *
 * The Router class is responsible for handling HTTP requests
 * by routing them to the appropriate handlers based on the
 * configured routes and the request URL.
 *
 * @method static void group(array $attributes, callable $routes)
 * @method static void request(string $route, string $class, string $classMethod = 'index', ?Method $method = null)
 * @method static void get(string $route, string $class, string $classMethod = 'index')
 * @method static void post(string $route, string $class, string $classMethod = 'index')
 * @method static void put(string $route, string $class, string $classMethod = 'index')
 * @method static void patch(string $route, string $class, string $classMethod = 'index')
 * @method static void delete(string $route, string $class, string $classMethod = 'index')
 *
 * @version 1.0
 * @author Flytachi
 */
abstract class Router implements RouterInterface
{
    use RouterDependence, RouterRequest;
    private static RouteNode $root;
    private static ?string $groupPrefix = null;

    public final static function run(false|string $routePath): void
    {
        Request::setHeaders();
        self::$root = new RouteNode;
        self::importMapping($routePath);
        self::route();
    }

    private static function importMapping(false|string $routePath): void
    {
        if ($routePath === false) Mapping::scanning(false);
        else {
            if (!file_exists($routePath)) Mapping::scanning();
            require $routePath;
        }
    }

    private static function route(): void
    {
        Log::trace("Route uri:" . $_SERVER['REQUEST_URI']);
        $data = self::splitUrlAndParams($_SERVER['REQUEST_URI']);
        $_GET = $data['params'];

        // Imports
        $funcPath = dirname(__DIR__, 2) . '/Config/functions.php';
        if ( file_exists($funcPath) ) require $funcPath;

        $node =  self::scanNode($data['url'], $_SERVER['REQUEST_METHOD']);
        if (!$node) RouteError::throw(HttpCode::NOT_FOUND, "{$_SERVER['REQUEST_METHOD']} '{$data['url']}' url not found");

        try {
            self::callNodeAction($node['action'], $node['params'], $data['url']);
        } catch (\TypeError $exception) {
            RouteError::throw(HttpCode::BAD_REQUEST, (env('DEBUG'))
                ? $exception->getMessage()
                : 'Invalid data'
            );
        } catch (CDOError|RepositoryError|ArtefactError|SheathException $exception) {
            RouteError::throw(HttpCode::INTERNAL_SERVER_ERROR, (env('DEBUG'))
                ? $exception->getMessage()
                : 'Server error',
                $exception
            );
        } catch (ExtraException|RouteError $exception) {
            $code = HttpCode::tryFrom((int) $exception->getCode());
            RouteError::throw($code ?: HttpCode::INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception);
        } catch (\Throwable $exception) {
            RouteError::throw(HttpCode::INTERNAL_SERVER_ERROR, (env('DEBUG'))
                ? $exception->getMessage()
                : 'Server error'
            );
        }
    }

    private static function scanNode(string $url, string $httpMethod): ?array
    {
        $node = self::$root;
        $params = [];
        $parts = explode('/', trim($url, '/'));

        foreach ($parts as $part) {
            if (isset($node->children[$part])) {
                $node = $node->children[$part];
                continue;
            }
            if (isset($node->children['{param}'])) {
                $node = $node->children['{param}'];
                $params[] = $part;
                continue;
            }
            return null;
        }

        if ($node->actions && isset($node->actions[$httpMethod]))
            return ['action' => $node->actions[$httpMethod], 'params' => $params];
        if ($node->defaultAction)
            return ['action' => $node->defaultAction, 'params' => $params];
        return null;
    }

    /**
     * This method redirects the user to a specified URL or to the previous page.
     *
     * @param string|null $url The URL to redirect to. Default is null.
     * @param array|null $param The parameters to append to the URL. Default is null.
     *
     * @return never
     */
    final static function redirect(?string $url = null, ?array $param = null): never
    {
        if ($url) header('location: /' . $url . arrayToRequest($param));
        else header('location:' . $_SERVER['HTTP_REFERER']);
        exit();
    }

}