<?php

namespace App\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Mercure "alternate topic" 101:
 *
 * On an entity, the user have access to certain ones but not all
 * E.g. Event (The user only has access to the event he owns or is participant of)
 *
 * In this case we can use the "alternate topic" feature of Mercure:
 * The frontend subscribes to `/events/{id}` as if he had access to everything
 * but his JWT contains a `mercure.subscribe` entry with the topic `/users/{id}?topic=/events/{id}`
 * which means he does not strictly have access to `/events/{id}` but only the ones that
 * are sent with BOTH `/events/{id}` AND `/users/{id}?topic=/events/{id}`.
 */
readonly class Mercure
{
    public function __construct(
        private HubInterface $hub,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @param array<string>     $groups
     * @param array<string|int> $userIds
     */
    public function submitToUsers(
        string $topic,
        object $data,
        array $groups,
        array $userIds = [],
    ): void {
        $data = $this->serializer->serialize($data, 'jsonld', [AbstractNormalizer::GROUPS => $groups]);

        foreach ($userIds as $user) {
            $this->hub->publish(new Update(
                [$topic, \sprintf('/users/%s?topic=%s', $user, rawurlencode($topic))],
                $data,
                private: true,
                type: $topic,
            ));
        }
    }
}
