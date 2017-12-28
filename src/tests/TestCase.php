<?php

namespace Tests;

use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class TestCase
 * @package Tests
 */
class TestCase extends BaseTestCase
{
    /**
     * The app object
     *
     * @var App
     */
    protected $app;

    /**
     * The database connection
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod
     * @param string $requestUri
     * @param array|string $requestData
     * @param string $contentType
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function runApp($requestMethod, $requestUri, $requestData = null, $contentType = 'application/vnd.api+json') : Response
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'CONTENT_TYPE' => $contentType,
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        // Process the application
        $response = $this->app->process($request, $response);

        // Return the response
        return $response;
    }

    /**
     * Set up all tests by starting a DB transaction to prevent anything from happening
     */
    protected function setUp()
    {
        // Boot the app
        $settings = require __DIR__ . '/../bootstrap/settings.php';
        $app = new App($settings);

        require __DIR__ . '/../bootstrap/dependencies.php';
        require __DIR__ . '/../bootstrap/middleware.php';
        require __DIR__ . '/../bootstrap/routes.php';

        $this->app = $app;

        // Start the transaction
        $this->connection = \Illuminate\Database\Capsule\Manager::connection();
        $this->connection->beginTransaction();
    }

    /**
     * Tear down the tests and rollback any changes
     */
    protected function tearDown()
    {
        $this->connection->rollBack();
    }
}