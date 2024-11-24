<?php

namespace App\Tests\Service;

use App\Entity\Message;
use App\Model\MessageDto;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

class MessageServiceTest extends KernelTestCase
{
    private Container $container;
    private MessageService $messageService;
    private EntityManagerInterface $entityManager;
    private Generator $faker;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->container = static::getContainer();

        $this->messageService = $this->container->get(MessageService::class);

        $this->entityManager = $this->container->get(EntityManagerInterface::class);

        $this->faker = Factory::create('fr_FR');
    }

    private function createMessages(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $message = new Message();
            $message->setFirstName($this->faker->firstName())
                ->setLastName($this->faker->lastName())
                ->setEmail($this->faker->email())
                ->setMessage($this->faker->sentence())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setRead(false)
                ->setAnswered(false);

            $this->entityManager->persist($message);
        }

        $this->entityManager->flush();
    }

    private function createMessageDto(): MessageDto
    {
        return new MessageDto(
            $this->faker->firstName(),
            $this->faker->lastName(),
            $this->faker->email(),
            $this->faker->sentence()
        );
    }

    public function testFindAllMessages(): void
    {
        // GIVEN
        $this->createMessages(5);

        // WHEN
        $messages = $this->messageService->findAll();

        // THEN
        $this->assertCount(5, $messages);
    }

    public function testFindOneMessage(): void
    {
        // GIVEN
        $this->createMessages(3);
        $messages = $this->messageService->findAll();
        $message = $messages[0];

        // WHEN
        $foundMessage = $this->messageService->findOne($message->getId());

        // THEN
        $this->assertNotNull($foundMessage);
    }

    public function testFindOneMessage_notFound(): void
    {
        // GIVEN
        $this->createMessages(3);

        // WHEN
        $foundMessage = $this->messageService->findOne(999);

        // THEN
        $this->assertNull($foundMessage);
    }

    public function testCreateMessage_isSaved(): void
    {
        // GIVEN
        $messageDto = $this->createMessageDto();

        // WHEN
        $message = $this->messageService->createMessage($messageDto);

        // THEN
        $this->assertNotNull($message->getId());
    }

    public function testCreateMessage_hasDesiredData(): void
    {
        // GIVEN
        $messageDto = $this->createMessageDto();

        // WHEN
        $message = $this->messageService->createMessage($messageDto);

        // THEN
        $this->assertEquals($messageDto->firstName, $message->getFirstName());
        $this->assertEquals($messageDto->lastName, $message->getLastName());
        $this->assertEquals($messageDto->email, $message->getEmail());
        $this->assertEquals($messageDto->message, $message->getMessage());
        $this->assertFalse($message->isRead());
        $this->assertFalse($message->isAnswered());
    }

    public function testUpdateMessage_setAsRead(): void
    {
        // GIVEN
        $this->createMessages(1);

        $message = $this->messageService->findOne(1);

        $message->setRead(true);

        // WHEN
        $message = $this->messageService->updateMessage($message);

        // THEN
        $this->assertTrue($message->isRead());
    }

    public function testUpdateMessage_setAsAnswered(): void
    {
        // GIVEN
        $this->createMessages(1);

        $message = $this->messageService->findOne(1);

        $message->setAnswered(true);

        // WHEN
        $message = $this->messageService->updateMessage($message);

        // THEN
        $this->assertTrue($message->isAnswered());
    }

    public function testDeleteMessage(): void
    {
        // GIVEN
        $messageDto = $this->createMessageDto();
        $message = $this->messageService->createMessage($messageDto);
        $id = $message->getId();

        // WHEN
        $this->messageService->deleteMessage($message);

        // THEN
        $this->assertNull($this->messageService->findOne($id));
    }

    public function testDeleteMessage_notFound(): void
    {
        // GIVEN
        $messageDto = $this->createMessageDto();
        $message = $this->messageService->createMessage($messageDto);
        $id = $message->getId();

        // WHEN
        $this->messageService->deleteMessage($message);

        // THEN
        $this->assertNull($this->messageService->findOne($id));

        // WHEN
        $this->messageService->deleteMessage($message);

        // THEN
        $this->assertNull($this->messageService->findOne($id));
    }
}
