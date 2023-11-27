<?php

require "../vendor/autoload.php";

$router = new Core\Router();

$router->addRoute('GET', '/api', 'Api@get');
$router->addRoute('GET', '/api/lecturers', 'Lektor@get');
$router->addRoute('POST', '/api/lecturers', 'Lektor@post');
$router->addRoute('GET', '/api/lecturers/{uuid}', 'Lektor@getOne');
$router->addRoute('GET', 'lecturer', 'Lektor@index');
$router->addRoute('GET', '/lecturer', 'Lektor@index');
// Add more routes as needed

$router->handleRequest();