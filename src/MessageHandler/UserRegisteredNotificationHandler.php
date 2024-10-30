<?php

namespace App\MessageHandler;

use App\Message\MagicLinkNotification;
use App\Message\UserRegisteredNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
readonly class UserRegisteredNotificationHandler
{
    private string $baseUrl;

    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        #[Autowire(env: 'PUBLIC_URL')]
        string $baseUrl,
    )
    {
        $this->baseUrl = \rtrim($baseUrl, '/');
    }

    public function __invoke(UserRegisteredNotification $notification): void
    {
        $mail = (new TemplatedEmail())
            ->to($notification->getUserEmail())
            ->subject('[PartyHall] ' . $this->translator->trans('emails.registered.subject', locale: $notification->getLanguage()))
            ->htmlTemplate('emails/registered.html.twig')
            ->locale($notification->getLanguage())
            ->context([
                'username' => $notification->getUsername(),
                'link' => $this->baseUrl,
            ])
        ;

        $this->mailer->send($mail);
    }
}
