<?php 

namespace App\Event;

use App\Entity\Message;
use Symfony\Contracts\EventDispatcher\Event;

class MessageCreatedEvent extends Event
{
    public const NAME = 'message.created';

    private Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}
