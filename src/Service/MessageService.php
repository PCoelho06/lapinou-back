<?php

namespace App\Service;

use App\Entity\Message;
use App\Model\MessageDto;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class MessageService
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageRepository $messageRepository) {}

    public function findAll(): array
    {
        return $this->messageRepository->findAll();
    }

    public function findOne(int $id): ?Message
    {
        return $this->messageRepository->find($id);
    }

    public function createMessage(MessageDto $messageDto): Message
    {
        $message = new Message();

        $message->setFirstName($messageDto->firstName);
        $message->setLastName($messageDto->lastName);
        $message->setEmail($messageDto->email);
        $message->setMessage($messageDto->message);
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setRead(false);
        $message->setAnswered(false);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    public function updateMessage(Message $message): Message
    {
        $this->entityManager->flush();

        return $message;
    }

    public function deleteMessage(Message $message): void
    {
        $this->entityManager->remove($message);
        $this->entityManager->flush();
    }
}
