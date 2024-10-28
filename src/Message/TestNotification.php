<?php

namespace App\Message;

use Symfony\Component\Mime\Email;

class TestNotification
{
    public function __construct(
        private string $content
    )
    {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getEmail(): Email
    {
        $email = (new Email())
            ->from('postmaster@oxodao.fr')
            ->to('test@toto.fr')
            ->subject('Test')
            ->html($this->content);
        ;

        return $email;
    }
}
