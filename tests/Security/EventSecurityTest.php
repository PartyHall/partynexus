<?php

namespace App\Tests\Security;

use App\Entity\Appliance;
use App\Entity\Event;
use App\Repository\ApplianceRepository;
use App\Repository\EventRepository;
use App\Tests\AuthenticatedTestCase;
use Doctrine\ORM\EntityManagerInterface;

class EventSecurityTest extends AuthenticatedTestCase
{
    private EntityManagerInterface $emi;

    private const array EVENT_DATA = [
        'name' => 'My event',
        'author' => 'Me',
        'datetime' => '2024-10-25T15:34:54Z',
        'location' => 'At home',
    ];

    private const array EVENT_CREATED_DATA = [
        'name' => 'My event',
        'author' => 'Me',
        'datetime' => '2024-10-25T15:34:54+00:00',
        'location' => 'At home',
        'owner' => [
            '@id' => '/api/users/1',
            '@type' => 'User',
            'id' => 1,
            'username' => 'admin',
        ],
        'over' => false,
        'participants' => [],
    ];

    private const array EVENT_UPDATE_DATA = [
        'name' => 'My edited event',
        'author' => 'The new author',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->emi = $this->getContainer()->get(EntityManagerInterface::class);
    }

    private function assertEventEquals(?Event $dbEvent): void
    {
        $this->assertNotNull($dbEvent);
        $this->assertEquals(self::EVENT_DATA['name'], $dbEvent->getName());
        $this->assertEquals(self::EVENT_DATA['author'], $dbEvent->getAuthor());
        $this->assertEqualsWithDelta(
            (new \DateTimeImmutable(self::EVENT_DATA['datetime']))->getTimestamp(),
            $dbEvent->getDatetime()->getTimestamp(),
            5,
        );
        $this->assertEquals(self::EVENT_DATA['location'], $dbEvent->getLocation());
        $this->assertEquals(self::EVENT_CREATED_DATA['owner']['@id'], '/api/users/' . $dbEvent->getOwner()->getId());
        $this->assertFalse($dbEvent->isOver());
        $this->assertEmpty($dbEvent->getParticipants());
    }

    // Create event (unauthenticated)
    public function test_event_post_unauthenticated(): void
    {
        $response = static::createClient()->request('POST', '/api/events', [
            'json' => self::EVENT_DATA,
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    // Create event (standard user)
    public function test_event_post_user(): void
    {
        $token = self::authenticate('user', 'password');

        $response = static::createClient()->request('POST', '/api/events', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $token,
            ],
            'json' => self::EVENT_DATA,
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    // Create event (admin)
    public function test_event_post_admin(): void
    {
        $token = self::authenticate('admin', 'password');

        $response = static::createClient()->request('POST', '/api/events', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $token,
            ],
            'json' => self::EVENT_DATA,
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJsonContains(self::EVENT_CREATED_DATA);

        $resp = json_decode($response->getContent(), true);
        $repo = $this->getContainer()->get(EventRepository::class);

        $this->assertEventEquals($repo->find($resp['id']));
    }

    // Create event (appliance)
    public function test_event_post_appliance(): void
    {
        $response = static::createClient()->request('POST', '/api/events', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
            'json' => self::EVENT_DATA,
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        // This contains the user so this also check that it is linked to the appliance's owner
        $this->assertJsonContains(self::EVENT_CREATED_DATA);

        $resp = json_decode($response->getContent(), true);
        $repo = $this->getContainer()->get(EventRepository::class);

        $this->assertEventEquals($repo->find($resp['id']));
    }

    // Update event (unauthenticated)
    public function test_event_patch_unauthenticated(): void
    {
        $resp = static::createClient()->request('PATCH', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa', [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => self::EVENT_UPDATE_DATA,
        ]);

        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Update event (not owner)
    public function test_event_patch_not_owner(): void
    {
        $token = self::authenticate('user', 'password');

        $resp = static::createClient()->request('PATCH', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Authorization' => $token,
            ],
            'json' => self::EVENT_UPDATE_DATA,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Update event (appliance not owner)
    public function test_event_patch_not_owner_appliance(): void
    {
        $repository = $this->getContainer()->get(EventRepository::class);
        $repositoryAppliance = $this->getContainer()->get(ApplianceRepository::class);

        /** @var Event $event */
        $event = $repository->find('0192bf5a-67d8-7d9d-8a5e-962b23aceeaa');
        /** @var Appliance $appliance */
        $appliance = $repositoryAppliance->find(2);

        $resp = static::createClient()->request('PATCH', '/api/events/' . $event->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'X-HARDWARE-ID' => $appliance->getHardwareId(),
                'X-API-TOKEN' => $appliance->getApiToken(),
            ],
            'json' => self::EVENT_UPDATE_DATA,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Update event (appliance owner)
    public function test_event_patch_owner_appliance(): void
    {
        $repository = $this->getContainer()->get(EventRepository::class);

        /** @var Event $event */
        $event = $repository->find('0192bf5a-67d8-7d9d-8a5e-962b23aceeaa');
        /** @var Appliance $appliance */
        $appliance = $event->getOwner()->getAppliances()->get(0);

        $resp = static::createClient()->request('PATCH', '/api/events/' . $event->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'X-HARDWARE-ID' => $appliance->getHardwareId(),
                'X-API-TOKEN' => $appliance->getApiToken(),
            ],
            'json' => self::EVENT_UPDATE_DATA,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Update event (owner)
    public function test_event_patch_owner(): void
    {
        $token = self::authenticate('eventmaker', 'password');

        $resp = static::createClient()->request('PATCH', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Authorization' => $token,
            ],
            'json' => self::EVENT_UPDATE_DATA,
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        // Response
        $this->assertJsonContains(self::EVENT_UPDATE_DATA);

        // DB
        /** @var Event $event */
        $event = $this->emi->getRepository(Event::class)->find('0192bf5a-67d8-7d9d-8a5e-962b23aceeaa');

        $this->assertEquals($event->getName(), self::EVENT_UPDATE_DATA['name']);
        $this->assertEquals($event->getAuthor(), self::EVENT_UPDATE_DATA['author']);
    }

    // Get (Unauthenticated)
    public function test_event_get_unauthenticated(): void
    {
        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa');
        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Get (Not in event)
    public function test_event_get_not_participant(): void
    {
        $token = $this->authenticate('noevents', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa', [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);

        $this->assertEquals(404, $resp->getStatusCode());
    }

    // Get (appliance owner)
    public function test_event_get_appliance_owner(): void
    {
        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertJsonContains(self::EVENT_CREATED_DATA);
    }

    // Get (appliance not owner)
    public function test_event_get_appliance_not_owner(): void
    {
        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_NOT_OWNER_KEY,
                'X-API-TOKEN' => self::APPLIANCE_NOT_OWNER_SECRET,
            ]
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Get (owner)
    public function test_event_get_owner(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa', [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertJsonContains(self::EVENT_CREATED_DATA);
    }

    // Get (participant)
    public function test_event_get_participant(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa', [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertJsonContains(self::EVENT_CREATED_DATA);
    }

    // Get collection (unauthenticated)
    public function test_event_getcollection_unauthenticated(): void
    {
        $resp = static::createClient()->request('GET', '/api/events');
        $this->assertEquals(401, $resp->getStatusCode());
    }

    // @TODO: GetCollection (Not admin, can only see which event he's participating/own)
    /*
    public function test_event_getcollection_user()
    {

    }
    */

    // @TODO: GetCollection (admin, can search either all event or its own / participating)
    /*
    public function test_event_getcollection_admin()
    {

    }
    */

    // Conclude event (unauthenticated)
    public function test_event_conclude_unauthenticated(): void
    {
        $resp = static::createClient()->request('POST', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/conclude', ['json' => []]);
        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Conclude event (Not owner)
    public function test_event_conclude_participant(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('POST', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/conclude', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Conclude event (Owner)
    public function test_event_conclude_owner(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('POST', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/conclude', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Conclude event (Appliance / Not owner)
    public function test_event_conclude_appliance_not_owner(): void
    {
        $resp = static::createClient()->request('POST', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/conclude', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_NOT_OWNER_KEY,
                'X-API-TOKEN' => self::APPLIANCE_NOT_OWNER_SECRET,
            ],
            'json' => [],
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Conclude event (Appliance / Owner)
    public function test_event_conclude_appliance_owner(): void
    {
        $resp = static::createClient()->request('POST', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/conclude', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
            'json' => [],
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }
}
