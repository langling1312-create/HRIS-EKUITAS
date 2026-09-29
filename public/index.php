<?php
/**
 * Front Controller - HRIS PHP Native MVC
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_ROOT', dirname(__DIR__) . '/app');

require APP_ROOT . '/config/config.php';
require APP_ROOT . '/helpers/functions.php';
require APP_ROOT . '/helpers/SessionHelper.php';
require APP_ROOT . '/helpers/AuthHelper.php';
require APP_ROOT . '/helpers/ValidationHelper.php';
require APP_ROOT . '/middleware/AuthMiddleware.php';
require APP_ROOT . '/core/Database.php';
require APP_ROOT . '/core/Model.php';
require APP_ROOT . '/core/Controller.php';
require APP_ROOT . '/core/Router.php';

SessionHelper::start();

$router = new Router();
$router->dispatch();
