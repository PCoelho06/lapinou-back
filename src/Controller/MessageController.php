<?php

namespace App\Controller;

use App\Event\MessageCreatedEvent;
use App\Model\MessageDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route('/messages', name: 'messages_', requirements: ['id' => '\d+'], defaults: ['_format' => 'json'])]
class MessageController extends AbstractController
{
    public function __construct(private \App\Service\MessageService $messageService) {}

    #[Route('/', name: 'findAll', methods: ['GET'])]
    public function findAll(): JsonResponse
    {
        $messages = $this->messageService->findAll();

        return $this->json([
            'status' => 'success',
            'data' => $messages,
        ]);
    }

    #[Route('/{id}', name: 'findOne', methods: ['GET'])]
    public function findOne(int $id): JsonResponse
    {
        $message = $this->messageService->findOne($id);

        if (!$message) {
            return $this->json([
                'status' => 'error',
                'message' => 'Message not found',
            ], 404);
        }

        return $this->json([
            'status' => 'success',
            'data' => $message,
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] MessageDto $messageDto, EventDispatcherInterface $dispatcher): JsonResponse
    {
        if ($messageDto->antispam) {
            return $this->json([
                'status' => 'filtered',
                'message' => 'Message filtered as spam',
            ], 400);
        }

        try {
            $message = $this->messageService->createMessage($messageDto);

            $event = new MessageCreatedEvent($message);
            $dispatcher->dispatch($event, MessageCreatedEvent::NAME);

            return $this->json([
                'status' => 'success',
                'message' => 'Message created successfully',
                'id' => $message->getId(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/{id}/read', name: 'mark_as_read', methods: ['PUT'])]
    public function markAsRead(int $id): JsonResponse
    {
        $message = $this->messageService->findOne($id);

        if (!$message) {
            return $this->json([
                'status' => 'error',
                'message' => 'Message not found',
            ], 404);
        }

        $message->setRead(true);

        $this->messageService->updateMessage($message);

        return $this->json([
            'status' => 'success',
            'message' => 'Message marked as read',
        ]);
    }

    #[Route('/{id}/unread', name: 'mark_as_unread', methods: ['PUT'])]
    public function markAsUnread(int $id): JsonResponse
    {
        $message = $this->messageService->findOne($id);

        if (!$message) {
            return $this->json([
                'status' => 'error',
                'message' => 'Message not found',
            ], 404);
        }

        $message->setRead(false);

        $this->messageService->updateMessage($message);

        return $this->json([
            'status' => 'success',
            'message' => 'Message marked as unread',
        ]);
    }

    #[Route('/{id}/answered', name: 'mark_as_answered', methods: ['PUT'])]
    public function markAsAnswered(int $id): JsonResponse
    {
        $message = $this->messageService->findOne($id);

        if (!$message) {
            return $this->json([
                'status' => 'error',
                'message' => 'Message not found',
            ], 404);
        }

        $message->setAnswered(true);

        $this->messageService->updateMessage($message);

        return $this->json([
            'status' => 'success',
            'message' => 'Message marked as answered',
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $message = $this->messageService->findOne($id);

        if (!$message) {
            return $this->json([
                'status' => 'error',
                'message' => 'Message not found',
            ], 404);
        }

        $this->messageService->deleteMessage($message);

        return $this->json([
            'status' => 'success',
            'message' => 'Message deleted successfully',
        ]);
    }
}
