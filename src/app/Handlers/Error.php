<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

/**
 * Class Error
 * @package App\Handlers
 */
class Error extends \Slim\Handlers\Error
{
    /**
     * The object to use for logging
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Error constructor
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle logging
     *
     * @param Request $request
     * @param Response $response
     * @param \Exception|\ParseError $exception
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $exception)
    {
        // Log the message
        $this->logger->critical($exception->getMessage());
        return $response->withStatus(500);
    }
}