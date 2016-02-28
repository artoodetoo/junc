<?php

namespace R2\Junc;

use FastRoute\RouteCollector;

class RouteMapper {
    private $map;
    private $routes;

    /**
     * Constructs a map.
     *
     * @param mixed $routes
     */
    public function __construct($map) {
        $this->map = $map;
    }

    /**
     * Method allows the object to be called as a function.
     *
     * @param RouteCollector $collector
     */
    public function __invoke(RouteCollector $collector)
    {
        $this->map($collector);
    }

    /**
     * Collect routes from map.
     *
     * @param RouteCollector $collector
     */
    public function map(RouteCollector $collector)
    {
        foreach ($this->getRoutes() as $row) {
            $collector->addRoute($row[0], $row[1], $row[2]);
        }
    }

    /**
     * It's very begining and dirty URL builder.
     *
     * @param string $name
     * @param array  $vars
     */
    public function pathFor($name, array $vars = null)
    {
        $pattern = false;
        foreach ($this->getRoutes() as $row) {
            if ($row[2]['do'] === $name) {
                $pattern = $row[1];
                break;
            }
        }
        if ($pattern && $vars) {
            foreach ($vars as $k => $v) {
                $pattern = preg_replace('~\{'.$k.'[^}]*\}~', $v, $pattern);
            }
        }
        return $pattern;
    }

    /**
     * Lazy route mapping
     *
     * @return &array
     */
    private function &getRoutes()
    {
        if (!isset($this->routes)) {
            // Route map is array or PHP filename returning array
            if (!is_array($this->map)) {
                $this->map = require($this->map);
            }
            $this->routes = [];
            $this->flatten($this->map, '', ['on' => 'GET']);
        }
        return $this->routes;
    }

    /**
     * Convert hierarchical tree with attribute inheritance to array like
     * [ [method, route, value], ...]
     *
     * @param array  $nested
     * @param string $prefix
     * @param array  $bag
     */
    private function flatten(array $nested, $prefix = '', array $bag = [])
    {
        foreach ($nested as $k => $v) {
            if ($k{0} === '/') {
                if (is_array($v)) {
                    $this->flatten($v, $prefix.$k, $bag);
                } else {
                    $this->add($prefix.$k, ['do' => $v] + $bag);
                }
            } elseif (is_numeric($k) && is_array($v)) {
                $this->flatten($v, $prefix, $bag);
            } elseif ($k === 'do') {
                $this->add($prefix, ['do' => $v] + $bag);
            } else {
                $bag[$k] = $v;
            }
        }
    }

    /**
     * Add item to temporary route list.
     *
     * @param string  $key
     * @param array   $value
     */
    private function add($key, array $value)
    {
        $method = $value['on'];
        unset($value['on']);
        $this->routes[] = [$method, $key, $value];
    }
}
