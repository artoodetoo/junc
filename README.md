Junc
====

Junc is the concise way to define routes.
Route map can be stored in any format, including JSON/YAML/.ini, only there was a way to convert it into an array.

It is not new routing library, but the addon to awesome [FastRouter](https://github.com/nikic/FastRoute/).

Install
-------

To install with composer:

```sh
composer require artoodetoo/junc
```

Usage
-----

Since this package only enhances FastRoute behavior, look at FastRoute documentation.
You have to realize how to use the route dispatcher.
Then come back here to see the difference in route definition.

Junc replaces series of FastRoute's $r->addRoute() to one clear (static) roadmap.

### Example 1. Basic usage:

By default all the routes are for GET method.

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

Junc is smart enough to distinguish route parts from attributes. 
All route parts started with slash "/" character. 


### Example 3a. Explicit method:

You can define method for certain route.
```php
$map = [
    '/' => 'index',
    '/post/{id:\d+}' => [
        ['on' => 'GET',  'do' => 'read_post'], 
        ['on' => 'POST', 'do' => 'write_post'], 
    ],
];
```

### Example 3b. Implicit method:

Alternatively you can set common method for set of routes.
```php
$map = [
    '/' => 'index',
    [
        'on' => 'GET',
        '/forum/{id:\d+}' => 'view_forum',
        '/topic/{id:\d+}' => 'view_topic',
    ],
    [
        'on' => 'POST',
        '/new',
        [
            '/forum'   => 'new_forum',
            '/topic'   => 'new_topic',
            '/comment' => 'new_comment',
        ]
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

### License

The Junc is open-source software, licensed under the [MIT license](http://opensource.org/licenses/MIT)
