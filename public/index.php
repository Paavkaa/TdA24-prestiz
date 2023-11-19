<?php

require "../vendor/autoload.php";

$router = new Core\Router();

$router->get('/', function () {
    echo "Hello, this is the home page!";
});

$router->get('/api', 'Api@get');
$router->get('/api/lektor/{uuid}', 'Lektor@get');
// Add more routes as needed

$router->handleRequest();