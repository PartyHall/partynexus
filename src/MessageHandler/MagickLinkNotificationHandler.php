<?php

namespace App\MessageHandler;

use App\Message\MagicLinkNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
readonly class MagickLinkNotificationHandler
{
    private string $baseUrl;

    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        #[Autowire(env: 'PUBLIC_URL')]
        string $baseUrl,
    ) {
        $this->baseUrl = \rtrim($baseUrl, '/');
    }

    public function __invoke(MagicLinkNotification $notification): void
    {
        $mail = (new TemplatedEmail())
            ->to($notification->getUserEmail())
            ->subject('[PartyHall] '.$this->translator->trans('emails.login.subject', locale: $notification->getLanguage()))
            ->htmlTemplate('emails/login.html.twig')
            ->locale($notification->getLanguage())
            ->context([
                'username' => $notification->getUsername(),
                'link' => $this->baseUrl.'/magic-login/?email='.$notification->getUserEmail().'&code='.$notification->getCode(),
            ])
        ;

        $this->mailer->send($mail);
    }
}
