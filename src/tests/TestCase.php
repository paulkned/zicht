<?php

namespace Tests;

use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;

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
     * Set up all tests by starting a DB transaction to prevent anything from happening
     */
    protected function setUp()
    {
        $this->app = (new \App\App())->get();

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