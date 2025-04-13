<?php

namespace App\MessageHandler;

use App\Message\PasswordUpdatedNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
readonly class PasswordUpdateNotificationHandler
{
    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(PasswordUpdatedNotification $notification): void
    {
        $mail = (new TemplatedEmail())
            ->to($notification->getUserEmail())
            ->subject('[PartyHall] '.$this->translator->trans('emails.password_update.subject', locale: $notification->getLanguage()))
            ->htmlTemplate('emails/password_update.html.twig')
            ->locale($notification->getLanguage())
            ->context([
                'username' => $notification->getUsername(),
                'fullname' => $notification->getFullname(),
            ])
        ;

        $this->mailer->send($mail);
    }
}
