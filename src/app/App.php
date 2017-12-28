<?php

namespace App;

use App\Handlers\Error;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;

class App
{
    /**
     * An instance of the Slim application
     *
     * @var \Slim\App
     */
    private $app;

    /**
     * App constructor
     */
    public function __construct()
    {
        // Instantiate the app
        $this->app = new SlimApp($this->initConfiguration());

        // Add the application routes
        $this->initRoutes();

        // Boot any dependencies
        $this->initDependencies();

        // Add middleware
        $this->initMiddleware();
    }

    /**
     * Initialize the configuration
     *
     * @return array
     */
    protected function initConfiguration(): array
    {
        // Make sure DotEnv is initialized for local configuration settings
        $dotenv = new Dotenv(__DIR__ . '/..');
        $dotenv->load();

        // Initialize the configuration
        $configDir = dirname(__FILE__) . '/config/';
        $config = [
            'settings' => []
        ];

        // Read all of the configuration files
        foreach (scandir($configDir) as $filename) {
            $path = $configDir . $filename;

            if (is_file($path)) {
                $configPart = require $path;
                $config['settings'] = array_merge($config['settings'], $configPart);
            }
        }

        return $config;
    }

    /**
     * Initialize the routes
     */
    protected function initRoutes()
    {
        $app = $this->app;
        require 'routes.php';
    }

    /**
     * Initialize all dependencies
     */
    protected function initDependencies()
    {
        // Get the application container
        $container = $this->app->getContainer();

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
    }

    /**
     * Initialize the middelware
     */
    protected function initMiddleware()
    {
        // Permanently redirect paths with a trailing slash to their non-trailing counterpart
        $this->app->add(function (Request $request, Response $response, callable $next) {
            $uri = $request->getUri();
            $path = $uri->getPath();

            if ($path != '/' && substr($path, -1) == '/') {
                $uri = $uri->withPath(substr($path, 0, -1));

                if ($request->getMethod() == 'GET') {
                    return $response->withRedirect((string)$uri, 301);
                } else {
                    return $next($request->withUri($uri), $response);
                }
            }

            return $next($request, $response);
        });
    }

    /**
     * Return the app object
     *
     * @return \Slim\App
     */
    public function get(): SlimApp
    {
        return $this->app;
    }
}
