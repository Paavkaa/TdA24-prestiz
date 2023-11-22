<?php

require "../vendor/autoload.php";

$router = new Core\Router();

$router->addRoute('GET', '/api', 'Api@get');
$router->addRoute('GET', '/api/lektor/{uuid}', 'Lektor@get');
$router->addRoute('GET','lektor', 'Lektor@index');
$router->addRoute('GET', '/lektor', 'Lektor@index');
// Add more routes as needed

$router->handleRequest();