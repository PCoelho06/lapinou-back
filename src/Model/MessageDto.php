<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class MessageDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'First name is required')]
        public ?string $firstName,

        #[Assert\NotBlank(message: 'Last name is required')]
        public ?string $lastName,

        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email')]
        public ?string $email,

        #[Assert\NotBlank(message: 'Message is required')]
        public ?string $message,

        public ?string $antispam = null,
    ) {}
}
