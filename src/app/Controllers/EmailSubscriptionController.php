<?php

namespace App\Controllers;

use App\Exceptions\ConflictException;
use App\Exceptions\HttpException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\EmailSubscription;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class EmailSubscriptionController
 * @package App\Controllers
 */
class EmailSubscriptionController extends BaseController
{
    /**
     * The resource type
     *
     * @var string
     */
    protected $type = 'email-subscriptions';

    /**
     * List all of the e-mail subscriptions
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        // Get all of the e-mail subscriptions
        $emailSubscriptions = EmailSubscription::all();
        $data = [];

        foreach ($emailSubscriptions as $emailSubscription) {
            $data[] = [
                'type' => $this->type,
                'id' => $emailSubscription->id,
                'attributes' => $emailSubscription->toArray()
            ];
        }

        // Return the response as JSON
        $response = $response->withJson(['data' => $data]);
        return $this->manipulateResponse($response);
    }

    /**
     * Show a single e-mail subscription
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        // Check if the e-mail subscription exists (no need to check if the ID is set in args, the router takes care of this)
        $emailSubscription = EmailSubscription::find($args['id']);

        if (!empty($emailSubscription)) {
            // Return the response as JSON
            $response = $response
                ->withJson([
                    'data' => [
                        'type' => $this->type,
                        'id' => $emailSubscription->id,
                        'attributes' => $emailSubscription->toArray()
                    ]
                ]);
            return $this->manipulateResponse($response);
        }

        // If no e-mail subscription is found return a 404
        return $response->withStatus(404);
    }

    /**
     * Create a new e-mail subscription
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function store(Request $request, Response $response, array $args): Response
    {
        try {
            // Get and validate the request
            $data = $this->parseRequest($request);

            // Validate the input
            $this->validateRequest($data, ['email']);

            /* Everything checks out create the e-mail subscription */
            // If the e-mail address already exists, simply update. This is highly unlikely though
            $emailSubscription = EmailSubscription::whereEmail($data['attributes']['email'])->first();
            if (empty($emailSubscription)) {
                $emailSubscription = new EmailSubscription();
            }

            $emailSubscription->email = $data['attributes']['email'];

            if (!empty($data['attributes']['name'])) {
                $emailSubscription->name = $data['attributes']['name'];
            }

            $emailSubscription->save();

            // Finally return the newly created subscription
            $createdUrl = "{$this->container['settings']['appUrl']}{$request->getServerParams()['REQUEST_URI']}/{$emailSubscription->id}";

            $response = $response
                ->withStatus(201)
                ->withHeader('Location', $createdUrl)
                ->withJson([
                    'data' => [
                        'type' => $this->type,
                        'id' => $emailSubscription->id,
                        'attributes' => $emailSubscription->toArray()
                    ],
                    'links' => [
                        'self' => $createdUrl
                    ]
                ]);
            return $this->manipulateResponse($response);
        } catch (UnprocessableEntityException $e) {
            // Display the validation errors
            return $this->displayValidationErrors($e, $response);
        } catch (HttpException $e) {
            // Return a HTTP error if an error occurred
            return $response->withStatus($e->getStatusCode());
        }
    }

    /**
     * Validate the data in a request
     *
     * @param array $data
     * @param array $fields
     * @throws ConflictException
     * @throws UnprocessableEntityException
     */
    protected function validateRequest(array $data, array $fields = [])
    {
        $errors = [];

        /* Validate that the type is correct */
        if (
            empty($data['type']) ||
            $data['type'] !== $this->type
        ) {
            throw new ConflictException();
        }

        /* Validate the ID */
        if (in_array('id', $fields)) {
            if (empty($data['id'])) {
                $errors['id']['required'] = 'The ID is required';
            } elseif ($data['id'] != $data['realId']) {
                $errors['id']['invalid'] = 'The ID does not match the called route';
            }
        }

        /* Validate the e-mail address */
        if (in_array('email', $fields)) {
            if (
                !isset($data['attributes']) ||
                empty($data['attributes']['email'])
            ) {
                $errors['email']['required'] = 'The email attribute is required';
            } elseif (!filter_var($data['attributes']['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email']['invalid-format'] = 'The email attribute is invalid';
            }
        }

        // Throw an exception if we have any validation errors
        if (count($errors) > 0) {
            $exception = new UnprocessableEntityException();
            $exception->setValidationErrors($errors);
            throw $exception;
        }
    }

    /**
     * Update an existing e-mail subscription
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        // Check if the e-mail subscription exists (no need to check if the ID is set in args, the router takes care of this)
        $emailSubscription = EmailSubscription::find($args['id']);

        if (!empty($emailSubscription)) {
            try {
                // Get and validate the request
                $data = $this->parseRequest($request);

                // Validate the input
                $validators = ['id'];
                if (isset($data['attributes']['email'])) {
                    $validators[] = 'email';
                }

                $this->validateRequest(array_merge($data, ['realId' => $args['id']]), $validators);

                // Everything checks out update the e-mail subscription
                if (array_key_exists('email', $data['attributes'])) {
                    $emailSubscription->email = $data['attributes']['email'];
                }

                if (array_key_exists('name', $data['attributes'])) {
                    $emailSubscription->name = $data['attributes']['name'];
                }

                $emailSubscription->save();

                // Finally return the newly created subscription
                $response = $response
                    ->withJson([
                        'data' => [
                            'type' => $this->type,
                            'id' => $emailSubscription->id,
                            'attributes' => $emailSubscription->toArray()
                        ]
                    ]);
                return $this->manipulateResponse($response);
            } catch (UnprocessableEntityException $e) {
                // Display the validation errors
                return $this->displayValidationErrors($e, $response);
            } catch (HttpException $e) {
                // Return a HTTP error if an error occurred
                return $response->withStatus($e->getStatusCode());
            }
        }

        // If no e-mail subscription is found return a 404
        return $response->withStatus(404);
    }

    /**
     * Delete an existing e-mail subscription
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        // Check if the e-mail subscription exists (no need to check if the ID is set in args, the router takes care of this)
        $emailSubscription = EmailSubscription::find($args['id']);

        if (!empty($emailSubscription)) {
            // Delete the subscription
            $emailSubscription->delete();

            return $this->manipulateResponse($response)
                ->withStatus(204);
        }

        // If no e-mail subscription is found return a 404
        return $response->withStatus(404);
    }
}