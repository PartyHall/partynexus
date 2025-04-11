<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\NewSongRequestNotification;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
readonly class NewSongRequestNotificationHandler
{
    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(NewSongRequestNotification $notification): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');

        /** @var User $admin */
        foreach ($admins as $admin) {
            $mail = (new TemplatedEmail())
                ->to($admin->getEmail())
                ->subject('[PartyHall] '.$this->translator->trans('emails.new_song_request.subject', locale: $admin->getLanguage()))
                ->htmlTemplate('emails/new_song_request.html.twig')
                ->locale($admin->getLanguage())
                ->context([
                    'firstname' => $admin->getFirstname(),
                    'lastname' => $admin->getLastname(),
                    'title' => $notification->getTitle(),
                    'artist' => $notification->getArtist(),
                    'requested_by' => $notification->getRequestedBy(),
                ]);

            $this->mailer->send($mail);
        }
    }
}
