Junc
====

Obvious map for [FastRouter](https://github.com/nikic/FastRoute/).

Install
-------

To install with composer:

```sh
composer require artoodetoo/junc
```

Usage
-----

Since this package only enhances FastRoute behavior, look at its documentation.
You have to realize how to use the route dispatcher.
Then come back here to see the difference in routes.

Junc replaces series of FastRoute's $r->addRoute() to one clear (static) roadmap.

### Example 1. Basic usage:

```php
<?php

require '/path/to/vendor/autoload.php';

$map = [
  '/users'                       => 'get_all_users_handler',
  '/user/{id:\d+}'               => 'get_user_handler',
  '/articles/{id:\d+}[/{title}]' => 'get_article_handler',
];
$dispatcher = FastRoute\simpleDispatcher(new R2\Junc\RouteMapper($map));

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        header('HTTP/1.0 404 Not Found');
        exit;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        header("HTTP/1.0 405 Method Not Allowed");
        exit;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1]['do'];
        $handler($routeInfo[2]);
        break;
}
```
*Note*:  
$routeInfo[1] is not a string. It's array with at least one required key 'do'.  
$routeInfo[2] is assotiative array of route variables ('id' & so on.)

### Example 2. Deep hierarchy with extra attribute (which inherits to all sub-routes):

```php
$map = [
    '/'                 => 'index',
    '/forum/{id:\d+}'   => 'view_forums', 
    '/admin' => [
        'allow' => 'ROLE_ADMIN',
        '/'             => 'admin_dashboard',
        '/updates'      => 'admin_updates',
        '/user' => [
            '/'         => 'admin_user_list',
            '/{id:\d+}' => 'admin_user_view',
            '/new'      => 'admin_user_new',
        ],
    ],
];
```

### Example 3. Different methods:

```php
$map = [
    '/' => 'index',
    '/post/{id:\d+}' => [
        ['on' => 'GET',  'do' => 'read_post'], 
        ['on' => 'POST', 'do' => 'write_post'], 
    ],
];
```

### Example 4. Caching:

```php
$dispatcher = FastRoute\cachedDispatcher(
    new R2\Junc\RouteMapper($map), 
    [
        'cacheFile' => __DIR__ . '/route.cache', /* required */
        'cacheDisabled' => IS_DEBUG_ENABLED,     /* optional, enabled by default */
    ]
);
```
