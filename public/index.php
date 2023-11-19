<?php

require "../vendor/autoload.php";

$router = new Core\Router();

$router->get('/', function () {
    echo "Hello, this is the home page!";
});

$router->get('/', 'UserController@index');
$router->get('/api', 'Api@get');
// Add more routes as needed

$router->handleRequest();