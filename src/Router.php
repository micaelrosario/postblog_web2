<?php

declare(strict_types=1);

final class Router
{
    /** @var array<int, array{method:string, pattern:string, regex:string, handler:callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): self
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): self
    {
        return $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable $handler): self
    {
        $method = strtoupper($method);
        $pattern = '/' . trim($pattern, '/');
        $regex = $this->compilePatternToRegex($pattern);

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
        ];

        return $this;
    }

    /**
     * @return array{matched:bool, result:mixed}
     */
    public function dispatch(string $method, string $path): array
    {
        $method = strtoupper($method);
        $path = '/' . trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $matches = [];
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }

            $result = ($route['handler'])($params);
            return ['matched' => true, 'result' => $result];
        }

        return ['matched' => false, 'result' => null];
    }

    private function compilePatternToRegex(string $pattern): string
    {
        // Suporta:
        // - /posts/{id}
        // - /posts/{id:\d+}
        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}/',
            static function (array $m): string {
                $name = $m[1];
                $rule = $m[2] ?? '[^/]+';
                return '(?P<' . $name . '>' . $rule . ')';
            },
            $pattern
        );

        return '#^' . $regex . '$#';
    }
}
