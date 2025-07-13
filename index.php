<?php
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/controllers/HomeController.php';

$router = new Router();
$router->addRoute('/', 'HomeController', 'index');
$router->route();
?>
//test MQ