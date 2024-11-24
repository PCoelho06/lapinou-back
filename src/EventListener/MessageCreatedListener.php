<?php

namespace App\EventListener;

use App\Event\MessageCreatedEvent;
use App\Service\MailService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class MessageCreatedListener
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function __invoke(MessageCreatedEvent $event): void
    {
        $message = $event->getMessage();
        $this->mailService->sendNewMessageNotification(
            $message
        );
    }
}
