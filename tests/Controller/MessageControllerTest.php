<?php

/**
 * Test suite for the MessageController.
 *
 * This class tests all endpoints related to the "message" resource, including:
 * - Creation (POST /messages)
 * - Retrieval (GET /messages, GET /messages/{id})
 * - Updates (PUT /messages/{id}/read, PUT /messages/{id}/answered)
 * - Deletion (DELETE /messages/{id})
 *
 * Each test validates correct behavior, including successful operations
 * and error handling for invalid inputs or missing resources.
 *
 * @package App\Tests\Controller
 * @see \App\Controller\MessageController
 * @see \App\Model\MessageDto
 * 
 * @author: Pierre Coelho <@PCoelho06>
 */

namespace App\Tests\Controller;

use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessageControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private \Faker\Generator $faker;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->faker = Factory::create('fr_FR');
    }

    private function createMessage(): array
    {
        return [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'message' => $this->faker->sentence()
        ];
    }

    private function assertResponseNotFound(): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertEquals('error', json_decode($this->client->getResponse()->getContent(),true)['status']);
        $this->assertEquals('Message not found', json_decode($this->client->getResponse()->getContent(),true)['message']);
    }

    private function assertResponseIsOk(): void
    {
        $this->assertResponseIsSuccessful();
        $this->assertEquals('success', json_decode($this->client->getResponse()->getContent(),true)['status']);
    }

    /***** POST *****/

    /**
     * Test successful creation of a message.
     *
     * GIVEN valid data for a new message.
     * WHEN a POST request is made to the /messages endpoint.
     * THEN the response should indicate success and return the ID of the created message.
     *
     * @return void
     */
    public function testCreateMessage_isOk(): void
    {
        // GIVEN
        $data = $this->createMessage();

        // WHEN
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // THEN
        $this->assertResponseIsOk();
        $this->assertArrayHasKey('id', json_decode($this->client->getResponse()->getContent(), true));
    }

    /**
     * Test failure when creating a message without a first name.
     *
     * GIVEN a dataset missing the "firstName" field.
     * WHEN a POST request is made to the /messages endpoint.
     * THEN the response should return a 422 status code and include a validation error for the missing field.
     *
     * @return void
     */
    public function testCreateMessage_noFirstName(): void
    {
        // GIVEN
        $data = [
            'lastName' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'message' => $this->faker->sentence()
        ];

        // WHEN
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // THEN
        $this->assertResponseIsUnprocessable();
        $this->assertEquals('First name is required', $response['violations'][0]['title']);
    }

    /**
     * Test failure when creating a message without a last name.
     *
     * GIVEN a dataset missing the "lastName" field.
     * WHEN a POST request is made to the /messages endpoint.
     * THEN the response should return a 422 status code and include a validation error for the missing field.
     *
     * @return void
     */
    public function testCreateMessage_noLastName(): void
    {
        // GIVEN
        $data = [
            'firstName' => $this->faker->firstName(),
            'email' => $this->faker->email(),
            'message' => $this->faker->sentence()
        ];

        // WHEN
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // THEN
        $this->assertResponseIsUnprocessable();
        $this->assertEquals('Last name is required', $response['violations'][0]['title']);
    }

    /**
     * Test failure when creating a message without an email.
     *
     * GIVEN a dataset missing the "email" field.
     * WHEN a POST request is made to the /messages endpoint.
     * THEN the response should return a 422 status code and include a validation error for the missing field.
     *
     * @return void
     */
    public function testCreateMessage_noEmail(): void
    {
        // GIVEN
        $data = [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'message' => $this->faker->sentence()
        ];

        // WHEN
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // THEN
        $this->assertResponseIsUnprocessable();
        $this->assertEquals('Email is required', $response['violations'][0]['title']);
    }

    /**
     * Test failure when creating a message with an invalid email.
     *
     * GIVEN a dataset with an invalid "email" field.
     * WHEN a POST request is made to the /messages endpoint.
     * THEN the response should return a 422 status code and include a validation error for the missing field.
     *
     * @return void
     */
    public function testCreateMessage_invalidEmail(): void
    {
        // GIVEN
        $data = [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'email' => 'invalid-email',
            'message' => $this->faker->sentence()
        ];

        // WHEN
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // THEN
        $this->assertResponseIsUnprocessable();
        $this->assertEquals('Invalid email', $response['violations'][0]['title']);
    }

    /**
     * Test failure when creating a message without a message.
     *
     * GIVEN a dataset missing the "message" field.
     * WHEN a POST request is made to the /messages endpoint.
     * THEN the response should return a 422 status code and include a validation error for the missing field.
     *
     * @return void
     */
    public function testCreateMessage_noMessage(): void
    {
        // GIVEN
        $data = [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'email' => $this->faker->email()
        ];

        // WHEN
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // THEN
        $this->assertResponseIsUnprocessable();
        $this->assertEquals('Message is required', json_decode($this->client->getResponse()->getContent(),true)['violations'][0]['title']);
    }

    /**
     * Test failure when creating a message with an antispam field.
     *
     * GIVEN a dataset with an "antispam" field.
     * WHEN a POST request is made to the /messages endpoint.
     * THEN the response should return a 400 status code and include a message indicating the message was filtered as spam.
     *
     * @return void
     */
    public function testCreateMessage_isSpam(): void
    {
        //GIVEN
        $data = [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'message' => $this->faker->sentence(),
            'antispam' => $this->faker->sentence(),
        ];

        //WHEN
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        //THEN
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertEquals('filtered', json_decode($this->client->getResponse()->getContent(),true)['status']);
        $this->assertEquals('Message filtered as spam', json_decode($this->client->getResponse()->getContent(),true)['message']);
    }

    /***** GET *****/

    /**
     * Test retrieval of all messages.
     *
     * GIVEN multiple messages exist in the system.
     * WHEN a GET request is made to the /messages endpoint.
     * THEN the response should indicate success and return all stored messages.
     *
     * @return void
     */
    public function testFindAllMessages_isOk(): void
    {
        // GIVEN
        $this->client->followRedirects();

        // WHEN
        $this->client->request('GET', '/messages');

        // THEN
        $this->assertResponseIsOk();
    }

    /**
     * Test retrieval of all messages with correct count.
     *
     * GIVEN multiple messages exist in the system.
     * WHEN a GET request is made to the /messages endpoint.
     * THEN the response should indicate success and return all stored messages.
     *
     * @return void
     */
    public function testFindAllMessages_countIsOk(): void
    {
        // GIVEN
        $this->client->followRedirects();
        for ($i = 0; $i < 5; $i++) {
            $data = $this->createMessage();
            $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        }

        // WHEN
        $this->client->request('GET', '/messages');

        // THEN
        $this->assertEquals(5, count(json_decode($this->client->getResponse()->getContent())->data));
    }

    /**
     * Test retrieval of a single message.
     *
     * GIVEN a message exists in the system.
     * WHEN a GET request is made to the /messages/{id} endpoint.
     * THEN the response should indicate success and return the requested message.
     *
     * @return void
     */
    public function testFindOneMessage_isOk(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // WHEN
        $this->client->request('GET', '/messages/' . json_decode($this->client->getResponse()->getContent(), true)['id']);

        // THEN
        $this->assertResponseIsOk();
    }

    /**
     * Test retrieval of a single message with all keys.
     *
     * GIVEN a message exists in the system.
     * WHEN a GET request is made to the /messages/{id} endpoint.
     * THEN the response should indicate success and return the requested message with all keys.
     *
     * @return void
     */
    public function testFindOneMessage_hasAllKeys(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // WHEN
        $this->client->request('GET', '/messages/' . json_decode($this->client->getResponse()->getContent(),true)['id']);
        $message = json_decode($this->client->getResponse()->getContent(), true)['data'];

        // THEN
        $this->assertArrayHasKey('id', $message);
        $this->assertArrayHasKey('firstName', $message);
        $this->assertArrayHasKey('lastName', $message);
        $this->assertArrayHasKey('email', $message);
        $this->assertArrayHasKey('message', $message);
        $this->assertArrayHasKey('read', $message);
        $this->assertArrayHasKey('answered', $message);
        $this->assertArrayHasKey('createdAt', $message);
    }

    /**
     * Test retrieval of a single message not found.
     *
     * GIVEN no message exists with the specified ID.
     * WHEN a GET request is made to the /messages/{id} endpoint.
     * THEN the response should return a 404 status code and an error message.
     *
     * @return void
     */
    public function testFindOneMessage_notFound(): void
    {
        // WHEN
        $this->client->request('GET', '/messages/999999');

        // THEN
        $this->assertResponseNotFound();
    }

    /***** PUT *****/

    /**
     * Test marking a message as read.
     *
     * GIVEN a message exists in the system.
     * WHEN a PUT request is made to the /messages/{id}/read endpoint.
     * THEN the response should indicate success.
     *
     * @return void
     */
    public function testMarkAsRead_isOk(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // WHEN
        $this->client->request('PUT', '/messages/' . json_decode($this->client->getResponse()->getContent(),true)['id'] . '/read');

        // THEN
        $this->assertResponseIsOk();
    }

    /**
     * Test marking a message as read.
     *
     * GIVEN a message exists in the system.
     * WHEN a PUT request is made to the /messages/{id}/read endpoint.
     * THEN the message should be marked as read in database.
     *
     * @return void
     */
    public function testMarkAsRead_isUpdated(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $message = json_decode($this->client->getResponse()->getContent(),true);

        // WHEN
        $this->client->request('PUT', '/messages/' . $message['id'] . '/read');
        $this->client->request('GET', '/messages/' . $message['id']);

        // THEN
        $this->assertEquals(true, json_decode($this->client->getResponse()->getContent(),true)['data']['read']);
    }

    /**
     * Test marking a message as read not found.
     *
     * GIVEN no message exists with the specified ID.
     * WHEN a PUT request is made to the /messages/{id}/read endpoint.
     * THEN the response should return a 404 status code and an error message.
     *
     * @return void
     */
    public function testMarkAsRead_notFound(): void
    {
        // WHEN
        $this->client->request('PUT', '/messages/999999/read');

        // THEN
        $this->assertResponseNotFound();
    }

    /**
     * Test marking a message as unread.
     *
     * GIVEN a message exists in the system.
     * WHEN a PUT request is made to the /messages/{id}/unread endpoint.
     * THEN the response should indicate success.
     *
     * @return void
     */
    public function testMarkedAsUnread_isOk(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $message = json_decode($this->client->getResponse()->getContent(),true);
        $this->client->request('PUT', '/messages/' . $message['id'] . '/read');

        // WHEN
        $this->client->request('PUT', '/messages/' . $message['id'] . '/unread');

        // THEN
        $this->assertResponseIsOk();
    }

    /**
     * Test marking a message as unread.
     *
     * GIVEN a message exists in the system.
     * WHEN a PUT request is made to the /messages/{id}/unread endpoint.
     * THEN the message should be marked as unread in database.
     *
     * @return void
     */
    public function testMarkedAsUnread_isUpdated(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $message = json_decode($this->client->getResponse()->getContent(),true);
        $this->client->request('PUT', '/messages/' . $message['id'] . '/read');

        // WHEN
        $this->client->request('PUT', '/messages/' . $message['id'] . '/unread');
        $this->client->request('GET', '/messages/' . $message['id']);

        // THEN
        $this->assertEquals(false, json_decode($this->client->getResponse()->getContent(),true)['data']['read']);
    }

    /**
     * Test marking a message as unread not found.
     *
     * GIVEN no message exists with the specified ID.
     * WHEN a PUT request is made to the /messages/{id}/unread endpoint.
     * THEN the response should return a 404 status code and an error message.
     *
     * @return void
     */
    public function testMarkedAsUnread_notFound(): void
    {
        // WHEN
        $this->client->request('PUT', '/messages/999999/unread');

        // THEN
        $this->assertResponseNotFound();
    }

    /**
     * Test marking a message as answered.
     *
     * GIVEN a message exists in the system.
     * WHEN a PUT request is made to the /messages/{id}/answered endpoint.
     * THEN the response should indicate success.
     *
     * @return void
     */
    public function testMarkAsAnswered_isOk(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // WHEN
        $this->client->request('PUT', '/messages/' . json_decode($this->client->getResponse()->getContent(),true)['id'] . '/answered');

        // THEN
        $this->assertResponseIsOk();
    }

    /**
     * Test marking a message as answered.
     *
     * GIVEN a message exists in the system.
     * WHEN a PUT request is made to the /messages/{id}/answered endpoint.
     * THEN the message should be marked as answered in database.
     *
     * @return void
     */
    public function testMarkAsAnswered_isUpdated(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $message = json_decode($this->client->getResponse()->getContent(),true);

        // WHEN
        $this->client->request('PUT', '/messages/' . $message['id'] . '/answered');
        $this->client->request('GET', '/messages/' . $message['id']);

        // THEN
        $this->assertEquals(true, json_decode($this->client->getResponse()->getContent(),true)['data']['answered']);
    }

    /**
     * Test marking a message as answered not found.
     *
     * GIVEN no message exists with the specified ID.
     * WHEN a PUT request is made to the /messages/{id}/answered endpoint.
     * THEN the response should return a 404 status code and an error message.
     *
     * @return void
     */
    public function testMarkAsAnswered_notFound(): void
    {
        // WHEN
        $this->client->request('PUT', '/messages/999999/answered');

        // THEN
        $this->assertResponseNotFound();
    }

    /***** DELETE *****/

    /**
     * Test deletion of a message.
     *
     * GIVEN a message exists in the system.
     * WHEN a DELETE request is made to the /messages/{id} endpoint.
     * THEN the response should indicate success.
     *
     * @return void
     */
    public function testDeleteMessage_isOk(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // WHEN
        $this->client->request('DELETE', '/messages/' . json_decode($this->client->getResponse()->getContent(),true)['id']);

        // THEN
        $this->assertResponseIsOk();
    }

    /**
     * Test deletion of a message.
     *
     * GIVEN a message exists in the system.
     * WHEN a DELETE request is made to the /messages/{id} endpoint.
     * THEN the message should be deleted from the database.
     *
     * @return void
     */
    public function testDeleteMessage_isDeleted(): void
    {
        // GIVEN
        $data = $this->createMessage();
        $this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $message = json_decode($this->client->getResponse()->getContent(),true);

        // WHEN
        $this->client->request('DELETE', '/messages/' . $message['id']);
        $this->client->request('GET', '/messages/' . $message['id']);

        // THEN
        $this->assertResponseNotFound();
    }

    /**
     * Test deletion of a message not found.
     *
     * GIVEN no message exists with the specified ID.
     * WHEN a DELETE request is made to the /messages/{id} endpoint.
     * THEN the response should return a 404 status code and an error message.
     *
     * @return void
     */
    public function testDeleteMessage_notFound(): void
    {
        // WHEN
        $this->client->request('DELETE', '/messages/999999');

        // THEN
        $this->assertResponseNotFound();
    }
}
