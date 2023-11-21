<?php

require "../vendor/autoload.php";

$router = new Core\Router();

$router->addRoute('GET', '/api', 'Api@get');
$router->addRoute('GET','/api/lektor/{uuid}', 'Lektor@get');
// Add more routes as needed

$router->handleRequest();