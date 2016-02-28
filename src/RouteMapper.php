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
        // Route map is array or PHP filename returning array
        if (!is_array($this->map)) {
            $this->map = require($this->map);
        }
        if (!isset($this->routes)) {
            $this->routes = [];
            $this->flatten($this->map, '', ['on' => 'GET']);
        }
        foreach ($this->routes as $row) {
            $collector->addRoute($row[0], $row[1], $row[2]);
        }
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
