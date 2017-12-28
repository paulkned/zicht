<?php

use App\Handlers\Error;
use Illuminate\Database\Capsule\Manager;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


// Get the application container
$container = $app->getContainer();

// Service factory for the ORM
$container['db'] = function ($container) {
    $capsule = new Manager();
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

try {
    $container->get('db');
} catch (ContainerExceptionInterface $e) {
    // Do nothing, since this will never happen
}

// Enable logging
$container['logger'] = function ($container) {
    $logger = new Logger('logger');
    $filename = __DIR__ . '/../storage/logs/error.log';
    $stream = new StreamHandler($filename, Logger::DEBUG);
    $fingersCrossed = new FingersCrossedHandler($stream, Logger::ERROR);
    $logger->pushHandler($fingersCrossed);

    return $logger;
};

$container['errorHandler'] = function ($container) {
    return new Error($container['logger']);
};

$container['phpErrorHandler'] = function ($container) {
    return new Error($container['logger']);
};

// Override the default Not Found Handler
$container['notFoundHandler'] = function ($container) {
    return function (Request $request, Response $response) use ($container) {
        return $container['response']
            ->withStatus(404);
    };
};

// Override the default Not Allowed Handler
$container['notAllowedHandler'] = function ($container) {
    return function (Request $request, Response $response, $methods) use ($container) {
        return $container['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods));
    };
};