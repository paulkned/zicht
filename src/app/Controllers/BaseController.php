<?php

namespace App\Controllers;

use App\Exceptions\UnprocessableEntityException;
use App\Exceptions\UnsupportedMediaTypeException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class BaseController
 * @package App\Controllers
 */
class BaseController
{
    /**
     * The Slim app container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The JSON-API content type
     *
     * @var string
     */
    protected $contentType = 'application/vnd.api+json';

    /**
     * EmailSubscriptionController constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Parse the incoming request
     *
     * @param Request $request
     * @return array
     *
     * @throws UnsupportedMediaTypeException
     */
    protected function parseRequest(Request $request): array
    {
        // Check if the content-type header is set
        if (
            !$request->hasHeader('Content-Type') ||
            $request->getHeader('Content-Type')[0] !== $this->contentType
        ) {
            throw new UnsupportedMediaTypeException();
        }

        // Parse the JSON and return if properly formatted
        $body = $request->getParsedBody();

        if (!isset($body['data'], $body['data']['type'])) {
            throw new UnsupportedMediaTypeException();
        }

        return $body['data'];
    }

    /**
     * Display validation errors to the client
     *
     * @param UnprocessableEntityException $e
     * @param Response $response
     * @return Response
     */
    protected function displayValidationErrors(UnprocessableEntityException $e, Response $response): Response
    {
        $response = $response
            ->withStatus($e->getStatusCode())
            ->withJson([
                'errors' => $e->getFormattedValidationErrors()
            ]);

        return $this->manipulateResponse($response);
    }

    /**
     * Manipulate the response before sending it to the client
     *
     * @param Response $response
     * @return Response
     */
    protected function manipulateResponse(Response $response): Response
    {
        return $response->withHeader('Content-Type', $this->contentType);
    }
}