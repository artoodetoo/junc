<?php
/*
 * RouteMapper utilized obvious route map.
 * Examples of map:
 *
 * // Only GET. Flat structure.
 * [
 *      '/'           => 'index',
 *      '/forum/{id}' => 'view_forums', 
 *      '/topic/{id}' => 'view_topic',
 * ]
 * 
 * // GET & POST.
 * [
 *      '/' => 'index',
 *      '/post/{id}' => [
 *          ['on' => 'GET',  'do' => 'read_post'], 
 *          ['on' => 'POST', 'do' => 'write_post'], 
 *      ],
 * ]
 * 
 * // Hierarchy with extra attribute (which inherits).
 * [
 *      '/'           => 'index',
 *      '/forum/{id}' => 'view_forums', 
 *      '/admin'      => [
 *          'allow' => 'ROLE_ADMIN',
 *          '/'        => 'admin_dashboard',
 *          '/updates' => 'admin_updates',
 *          '/users'   => [
 *              '/'    => 'admin_user_list',
 *              '/new' => 'admin_user_new',
 *          ],
 *      ],
 * ]
 * 
 */

namespace R2\Junc;

use FastRoute\RouteCollector;

class RouteMapper {
    private $map;
    private $collector;

    /**
     * Constructs a map.
     *
     * @param mixed $routes
     */
    public function __construct($map) {
        $this->map = map;
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
        $this->collector = $collector;

        $flat = $this->flatten($this->map, '', ['on' => 'GET']);
        foreach ($flat as $method => $routes) {
            foreach ($routes as $route => $bag) {
                $this->collector->addRoute($method, $route, $bag);
            }
        }
    }

    /**
     * Convert hierarchical tree with attribute inheritance to array like
     * [ method => [ route => bag, ... ], ...]
     *
     * @param array  $nested
     * @param string $prefix
     * @param array  $bag
     */
    private function flatten(array $nested, $prefix = '', array $bag = [])
    {
        $result = [];
        foreach ($nested as $k => $v) {
            if ($k{0} === '/') {
                if (is_array($v)) {
                    $result = array_replace_recursive(
                        $result,
                        flatten($v, $prefix.$k, $bag)
                    );
                } else {
                    $this->add($result, $prefix.$k, ['do' => $v] + $bag);
                }
            } elseif (is_numeric($k) && is_array($v)) {
                $result = array_replace_recursive(
                    $result,
                    flatten($v, $prefix, $bag)
                );
            } elseif ($k === 'do') {
                $this->add($result, $prefix, ['do' => $v] + $bag);
            } else {
                $bag[$k] = $v;
            }
        }
        return $result;
    }

    private function add(array &$result, $key, array $value)
    {
        $method = $value['on'];
        unset($value['on']);
        $result[$method][$key] = $value;
    }
}
