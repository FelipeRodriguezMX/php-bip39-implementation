<?php

class Router
{

    private array $routes = [];
    private string $version = 'v1';
    private array $params = [];
    private bool $routeFound = false;
    private mixed $body;
    private string $errorStr = 'API not found';
    private int $errorCode = 404;
    private array $queryParams = [];
    private ?string $route = '';

    public function __construct() {}

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRouteVersion(string $version = 'v1')
    {
        $this->version = $version;
    }

    private function middlewares($middlewares): bool
    {
        if (count($middlewares) == 0) {
            return true;
        }
        $access = array_search(true, $middlewares);
        if ($access === false) {
            $this->errorStr = 'Usuario sin acceso';
            $this->errorCode = 403;
        }
        return $access !== false;
    }

    public function get(string $URL, $callback, $middlewares = [])
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            return;
        }

        if (!$this->middlewares($middlewares)) {
            return;
        }

        $this->setRoute($URL);
        if ($this->executeThisURL()) {
            $req['params'] = (object) $this->params;
            $callback((object) $req);
            return;
        };
    }

    public function delete(string $URL, $callback, $middlewares = [])
    {
        if ($_SERVER['REQUEST_METHOD'] != 'DELETE') {
            return;
        }

        if (!$this->middlewares($middlewares)) {
            return;
        }

        $this->setRoute($URL);
        if ($this->executeThisURL()) {
            $req['params'] = (object) $this->params;
            $callback((object) $req);
            return;
        };
    }

    public function post(string $URL, $callback, $middlewares = [])
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return;
        }
        if (!$this->middlewares($middlewares)) {
            return;
        }
        $this->setRoute($URL);
        if ($this->executeThisURL()) {
            $req['params'] = (object) $this->params;
            $req['body'] = (object) $this->body;

            $callback((object) $req);
            return;
        }
    }

    public function put(string $URL, $callback, $middlewares = [])
    {

        if ($_SERVER['REQUEST_METHOD'] != 'PUT') {
            return;
        }

        if (!$this->middlewares($middlewares)) {
            return;
        }

        $this->setRoute($URL);
        if ($this->executeThisURL()) {
            $req['params'] = (object) $this->params;
            $req['body'] = (object) $this->body;

            $callback((object) $req);
        }
    }

    public function notFound($callback)
    {
        if ($this->routeFound == false) {
            $callback(
                (object) [
                    "message" => $this->errorStr,
                    "statusCode" => $this->errorCode
                ]
            );
        }
    }

    private function executeThisURL(): bool
    {
        $response = $this->start();
        return $response;
    }
    public function setRoute(string $routeName): void
    {
        $exist = $this->existsRoute($routeName);
        if ($exist !== false) {
            throw new Error("This URL: $routeName already exists");
        }
        $splitedRoutName = explode('/', $routeName);
        array_push($this->routes, (object) array(
            "url" => $this->version . $routeName,
        ));
    }

    private function existsRoute(string $routeName): bool
    {
        $newRoute = $this->version . $routeName;
        $found = array_search($newRoute, array_column($this->routes, 'url'));
        return $found;
    }

    private function hasSameParams(array $currentURLSplited, array $routeUrlSplited): bool
    {
        $res = true;
        foreach ($routeUrlSplited as $key => $item) {
            if (str_contains($item, ':')) {
                $valuePram = $currentURLSplited[$key];
                $res = !empty($valuePram);
            }
        }
        return $res;
    }

    private function isSameURLString(array $currentURLSplited, array $routeUrlSplited): bool
    {
        $isSame = true;
        foreach ($routeUrlSplited as $key => $item) {
            if (str_contains($item, ':')) {
                $currentURLSplited[$key] = $item;
            }
        }
        $isSame = count(array_diff($currentURLSplited, $routeUrlSplited)) == 0;
        return $isSame;
    }

    private function saveQueryParams(string $url): void
    {
        $queryParams = array_combine(array_keys($_GET), array_map(function ($queryParam) {
            //reemplazar los espacios en blanco por "+"
            $queryParam = str_replace(' ', '+', $queryParam);
            return $queryParam;
        }, $_GET));
        $this->queryParams = count($queryParams) > 0 ? $queryParams : [];
    }

    private function unsetQueryParamsOfUrl(string $url): string
    {

        if (str_contains($url, '?')) {
            $urlExploded = explode('?', $url);
            $urlWhitoutQuery = $urlExploded[0] ?? null;
            $url = $urlWhitoutQuery;
        }
        return $url;
    }
    public function start()
    {
        $foundRoute = false;
        // $currentURL = explode("index.php", $_SERVER['REQUEST_URI'])[1];
        // $this->saveQueryParams($currentURL);
        // $currentURL = $this->unsetQueryParamsOfUrl($currentURL);
        // $currentURL = substr($currentURL, 1);
        // $urlSplited = explode('/', $currentURL);
        // $this->route = $urlSplited[1] ?? null;
        // $sizeURL = count($urlSplited);

        $currentURL = isset($_GET['path']) ? $_GET['path'] : explode("index.php", $_SERVER['REQUEST_URI'])[1];
        $currentURL = isset($_GET['path']) ? $currentURL :  substr($currentURL, 1);
        $urlSplited = explode('/', $currentURL);
        $this->route = $urlSplited[1] ?? null;
        $sizeURL = count($urlSplited);
        foreach ($this->routes as $route) {
            $routeSize = count(explode('/', $route->url));
            $routeUrlSplited = explode('/', $route->url);
            if ($sizeURL == $routeSize && $this->hasSameParams($urlSplited, $routeUrlSplited) && $this->isSameURLString($urlSplited, $routeUrlSplited)) {
                foreach ($routeUrlSplited as $key => $item) {
                    if (str_contains($item, ':')) {
                        $valuePram = $urlSplited[$key];
                        $namePram = explode(':', $item)[1];

                        $this->params[$namePram] = $valuePram;
                    }
                }
                $this->params['queryParams'] = (object) $this->queryParams;
                $this->body = json_decode(file_get_contents('php://input'));
                $foundRoute = true;
            }
        }
        $this->routeFound = $foundRoute;
        return $foundRoute;
    }
}
