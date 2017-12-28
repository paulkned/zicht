<?php

// Autoload the dependencies
require __DIR__ . '/../vendor/autoload.php';

// Instantiate the app
$settings = require __DIR__ . '/../bootstrap/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../bootstrap/dependencies.php';

// Register middleware
require __DIR__ . '/../bootstrap/middleware.php';

// Register routes
require __DIR__ . '/../bootstrap/routes.php';

// Run app
$app->run();