<?php

namespace App\Tests\Functional;

use App\Entity\Message;
use App\Event\MessageCreatedEvent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MessageWorkflowTest extends KernelTestCase
{
    private $dispatcher;

    public function setUp(): void
    {
        $this->dispatcher = self::getContainer()->get('event_dispatcher');
    }
    public function testMessageWorkflow(): void
    {
        $kernel = self::bootKernel();

        // Create a sample Message entity
        $message = (new Message())
            ->setEmail('user@example.com')
            ->setFirstName('Alice')
            ->setLastName('Johnson')
            ->setMessage('Test functional workflow.');

        // Dispatch the MessageCreatedEvent
        $event = new MessageCreatedEvent($message);
        $this->dispatcher->dispatch($event);

        // No exceptions mean the workflow worked as expected
        $this->assertTrue(true);
    }
}
