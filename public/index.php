<?php

require "../vendor/autoload.php";

$router = new App\Router();

$router->get('/', function () {
    echo "Hello, this is the home page!";
});

$router->get('/users', 'UserController@index');
$router->post('/users', 'UserController@create');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@delete');

// Add more routes as needed

$router->handleRequest();