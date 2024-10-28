<?php

namespace App\MessageHandler;

use App\Message\TestNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TestNotificationHandler
{
    public function __construct(
        private MailerInterface $mailer,
    )
    {
    }

    public function __invoke(TestNotification $notification)
    {
        $this->mailer->send($notification->getEmail());
    }
}
