<?php

require "../vendor/autoload.php";

$router = new Core\Router();

$router->addRoute('GET', '/api', 'Api@get');
$router->addRoute('GET', '/api/lecturer/{uuid}', 'Lektor@get');
$router->addRoute('GET', '/api/lecturer', 'Lektor@post');
$router->addRoute('GET', 'lecturer', 'Lektor@index');
$router->addRoute('GET', '/lecturer', 'Lektor@index');
// Add more routes as needed

$router->handleRequest();