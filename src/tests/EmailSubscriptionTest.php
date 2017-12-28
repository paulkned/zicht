<?php

namespace Tests;

use App\Models\EmailSubscription;

/**
 * Class EmailSubscriptionTest
 * @package Tests
 */
class EmailSubscriptionTest extends TestCase
{
    /**
     * Dummy data for our tests
     *
     * @var array
     */
    protected $testData = [
        [
            'id' => 1,
            'email' => 'test1@test.te',
            'name' => 'Test 1'
        ],
        [
            'id' => 2,
            'email' => 'test2@test.te',
            'name' => 'Test 2'
        ],
        [
            'id' => 3,
            'email' => 'test3@test.te',
            'name' => 'Test 3'
        ]
    ];

    /**
     * Test getting the full list of e-mail subscriptions
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGettingList()
    {
        // Perform the call
        $response = $this->runApp('GET', '/email-subscriptions');

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 200);

        $body = json_decode((string)$response->getBody());
        $this->assertNotNull($body);

        $this->assertObjectHasAttribute('data', $body);
        $this->assertCount(3, $body->data);

        $counter = 0;
        foreach ($body->data as $record) {
            $this->assertSame($record->type, 'email-subscriptions');
            $this->assertSame($record->id, $this->testData[$counter]['id']);
            $this->assertSame($record->attributes->email, $this->testData[$counter]['email']);
            $this->assertSame($record->attributes->name, $this->testData[$counter]['name']);

            $counter++;
        }
    }

    /**
     * Test getting an item
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGettingItem()
    {
        // Perform the call
        $response = $this->runApp('GET', '/email-subscriptions/1');

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 200);

        $body = json_decode((string)$response->getBody());
        $this->assertNotNull($body);

        $this->assertSame($body->data->type, 'email-subscriptions');
        $this->assertSame($body->data->id, $this->testData[0]['id']);
        $this->assertSame($body->data->attributes->email, $this->testData[0]['email']);
        $this->assertSame($body->data->attributes->name, $this->testData[0]['name']);
    }

    /**
     * Test getting an item which doesn't exist
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGettingInvalidItem()
    {
        // Perform the call
        $response = $this->runApp('GET', '/email-subscriptions/4');

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 404);
    }

    /**
     * Test creating an item
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testCreatingItem()
    {
        // Perform the call
        $response = $this->runApp(
            'POST',
            '/email-subscriptions',
            [
                'data' => [
                    'type' => 'email-subscriptions',
                    'attributes' => [
                        'email' => 'test4@test.te',
                        'name' => 'Test 4'
                    ]
                ]
            ]);

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 201);

        $body = json_decode((string)$response->getBody());
        $this->assertNotNull($body);

        $emailSubscription = EmailSubscription::whereEmail('test4@test.te')->first();
        $this->assertNotNull($emailSubscription);

        $this->assertSame($body->data->type, 'email-subscriptions');
        $this->assertSame($body->data->id, $emailSubscription->id);
        $this->assertSame($body->data->attributes->email, $emailSubscription->email);
        $this->assertSame($body->data->attributes->name, $emailSubscription->name);
    }

    /**
     * Test that creating an item without the valid header fails
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testCreatingItemWithoutHeader()
    {
        // Perform the call
        $response = $this->runApp(
            'POST',
            '/email-subscriptions',
            null,
            'application/invalid');

        // Check that the return type is correct
        $this->assertSame($response->getStatusCode(), 415);
    }

    /**
     * Test that creating a subscription with invalid fields fails
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testCreatingItemWithInvalidFields()
    {
        // Perform the call
        $response = $this->runApp(
            'POST',
            '/email-subscriptions',
            [
                'data' => [
                    'type' => 'email-subscriptions',
                    'attributes' => [
                        'name' => 'Test 4'
                    ]
                ]
            ]);

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 422);

        $body = json_decode((string)$response->getBody());
        $this->assertNotNull($body);
        $this->assertObjectHasAttribute('errors', $body);
        $this->assertGreaterThan(0, count((array)$body->errors));
    }

    /**
     * Test updating an item
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testUpdatingItem()
    {
        // Perform the call
        $response = $this->runApp(
            'PATCH',
            '/email-subscriptions/1',
            [
                'data' => [
                    'type' => 'email-subscriptions',
                    'id' => 1,
                    'attributes' => [
                        'email' => 'test4@test.te',
                        'name' => 'Test 4'
                    ]
                ]
            ]);

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 200);

        $body = json_decode((string)$response->getBody());
        $this->assertNotNull($body);

        $emailSubscription = EmailSubscription::whereEmail('test4@test.te')->first();
        $this->assertNotNull($emailSubscription);

        $this->assertSame($body->data->type, 'email-subscriptions');
        $this->assertSame($body->data->id, $emailSubscription->id);
        $this->assertSame($body->data->attributes->email, $emailSubscription->email);
        $this->assertSame($body->data->attributes->name, $emailSubscription->name);
    }

    /**
     * Test that updating an item without the valid header fails
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testUpdatingItemWithoutHeader()
    {
        // Perform the call
        $response = $this->runApp(
            'PATCH',
            '/email-subscriptions/1',
            null,
            'application/invalid');

        // Check that the return type is correct
        $this->assertSame($response->getStatusCode(), 415);
    }

    /**
     * Test that updating an non-existent item fails
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testUpdatingInvalidItem()
    {
        // Perform the call
        $response = $this->runApp('PATCH','/email-subscriptions/4');

        // Check that the return type is correct
        $this->assertSame($response->getStatusCode(), 404);
    }

    /**
     * Test updating an item with invalid fields fails
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testUpdatingItemWithInvalidFields()
    {
        // Perform the call
        $response = $this->runApp(
            'PATCH',
            '/email-subscriptions/1',
            [
                'data' => [
                    'type' => 'email-subscriptions',
                    'id' => 4,
                    'attributes' => [
                        'email' => 'test4@test',
                        'name' => 'Test 4'
                    ]
                ]
            ]);

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 422);

        $body = json_decode((string)$response->getBody());
        $this->assertNotNull($body);
        $this->assertObjectHasAttribute('errors', $body);
        $this->assertObjectHasAttribute('id', $body->errors);
        $this->assertObjectHasAttribute('email', $body->errors);
    }

    /**
     * Test deleting an item
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testDeletingItem()
    {
        // Perform the call
        $response = $this->runApp('DELETE','/email-subscriptions/1');

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 204);

        // Check that the item has indeed been removed
        $this->assertNull(EmailSubscription::find(1));
    }

    /**
     * Test deleting an invalid item
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testDeletingInvalidItem()
    {
        // Perform the call
        $response = $this->runApp('DELETE','/email-subscriptions/4');

        // Check that the body is valid
        $this->assertSame($response->getStatusCode(), 404);
    }

    /**
     * Create some dummy data
     */
    protected function setUp()
    {
        // Start the database transactions
        parent::setUp();

        // Insert some test data
        EmailSubscription::getQuery()->delete();
        EmailSubscription::insert($this->testData);
    }
}