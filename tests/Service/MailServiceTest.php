<?php

namespace App\Tests\Service;

use App\Entity\Message;
use App\Service\MailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailServiceTest extends TestCase
{
    public function testSendNewMessageNotification(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $mailer->expects(self::once())
            ->method("send")
            ->with($this->isInstanceOf(Email::class));

        $mailService = new MailService($mailer);

        $message = (new Message())
            ->setEmail('test@example.com')
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setMessage('Hello!');

        $mailService->sendNewMessageNotification($message);
    }
}
