<?php
header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Header: *');

require __DIR__ . '/../vendor/autoload.php';

// Load DotENV
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';

$app = new \Slim\App($settings);

$container = $app->getContainer();

// Register dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

$app->run();