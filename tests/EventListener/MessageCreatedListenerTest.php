<?php

namespace App\Tests\EventListener;

use App\Entity\Message;
use App\Event\MessageCreatedEvent;
use App\EventListener\MessageCreatedListener;
use App\Service\MailService;
use PHPUnit\Framework\TestCase;

class MessageCreatedListenerTest extends TestCase
{
    public function testInvokeCallsMailService(): void
    {
        $mailService = $this->createMock(MailService::class);

        $mailService->expects($this->once())
            ->method('sendNewMessageNotification')
            ->with($this->isInstanceOf(Message::class));

        $listener = new MessageCreatedListener($mailService);

        $message = (new Message())
            ->setEmail('test@example.com')
            ->setFirstName('Jane')
            ->setLastName('Smith')
            ->setMessage('Test message.');

        // Dispatch the event
        $event = new MessageCreatedEvent($message);
        $listener->__invoke($event);
    }
}
