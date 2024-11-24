<?php

namespace App\Service;

use App\Entity\Message;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send a notification email when a message is saved.
     *
     * @param Message $message
     * @return void
     */
    public function sendNewMessageNotification(Message $message): void
    {
        $email = (new Email())
            ->from('contact@lapinou.tech')
            ->to('p.coelho@lapinou.tech')
            ->replyTo($message->getEmail())
            ->subject('Nouveau message sur le Portfolio')
            ->html(
                sprintf(
                    '<p>Un nouveau message a été reçu de la part de %s %s (%s) :</p><p>%s</p>',
                    $message->getFirstName(),
                    $message->getLastName(),
                    $message->getEmail(),
                    $message->getMessage()
                )
            );

        $this->mailer->send($email);
    }
}
