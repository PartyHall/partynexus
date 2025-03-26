<?php

namespace App\MessageHandler;

use App\Message\TestEmailNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class TestEmailNotificationHandler
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(TestEmailNotification $notification): void
    {
        $mail = (new TemplatedEmail())
            ->to($notification->getEmail())
            ->subject('[PartyHall] Test email')
            ->htmlTemplate('emails/test_email.html.twig')
        ;

        $this->mailer->send($mail);
    }
}
