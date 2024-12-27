<?php

namespace App\DataFixtures;

use App\Entity\Song;
use App\Enum\SongFormat;
use App\Enum\SongQuality;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

class SongFixture extends Fixture
{
    private const array SONGS = [
        [
            'title' => 'The backup',
            'artist' => '2080',
            'mbid' => 'c8c7633e-979a-49dd-bce4-03163eec7946',
            'cover' => 'cover1',
            'format' => 'transparent_video',
            'quality' => 'perfect',
            'spotify' => '4xJyzAdCmHZhDcglSDtzs6',
            'buildid' => '57451e98-465b-4968-9e88-e7c0f8ab5b6c',
        ],
        [
            'title' => 'Super Easy',
            'artist' => '2080',
            'mbid' => '25c79041-55e9-4db7-886f-9b0a21e22dae',
            'cover' => 'cover1',
            'format' => 'video',
            'quality' => 'good',
            'spotify' => '7FX1hKRZus6YyPg7lfuEJA',
            'buildid' => '622be3c4-28c4-4e10-b7b3-473adf0d8274',
        ],
        [
            'title' => 'The master key',
            'artist' => '2080',
            'mbid' => '38d340cf-554b-4ee4-a07a-27f55403ce17',
            'cover' => 'cover1',
            'format' => 'video',
            'quality' => 'ok',
        ],
        [
            'title' => 'My megadrive',
            'artist' => '2080',
            'mbid' => '5c684d4e-6ded-4c1e-a135-b5a5452e4521',
            'cover' => 'cover1',
            'format' => 'cdg',
            'quality' => 'perfect',
            'spotify' => '3TI8YR2t52TF9zZ1Y2aozS',
            'buildid' => 'aab8f42b-7340-42f3-8648-f0a85c67bf4b',
        ],
        [
            'title' => 'Vector Pictures',
            'artist' => '2080',
            'mbid' => '3f002208-7cc1-4396-be55-e627dbc7b4d2',
            'cover' => 'cover2',
            'format' => 'video',
            'quality' => 'bad',
        ],
        // Lets also add a few songs with weird chars to test the SongSearchFilter
        [
            'title' => 'Ainsi soit-il',
            'artist' => 'Taïro',
            'cover' => 'cover1',
            'format' => 'video',
            'quality' => 'good',
            'mbid' => 'ff4151da-b769-4624-bcd8-4b088c65fc02',
        ],
        [
            'title' => 'J\'étais prêt',
            'artist' => 'Taïro',
            'cover' => 'cover1',
            'format' => 'video',
            'quality' => 'perfect',
            'mbid' => '4f2c028a-48e2-490d-8176-d8f602f5f9d1',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        // Hardcoded base songs
        foreach (self::SONGS as $song) {
            $s = new Song();
            $s
                ->setTitle($song['title'])
                ->setArtist($song['artist'])
                ->setMusicBrainzId(Uuid::fromString($song['mbid']))
                // ->setCoverFile($this->getCover($song['cover']))
                ->setFormat(SongFormat::from($song['format']))
                ->setQuality(SongQuality::from($song['quality']))
                ->setSpotifyId(array_key_exists('spotify', $song) ? $song['spotify'] : null)
            ;

            if (array_key_exists('buildid', $song)) {
                $s->setReady(true)->setNexusBuildId(Uuid::fromString($song['buildid']));
            }

            $manager->persist($s);
        }
        $manager->flush();

        // Random songs
        $faker = FakerFactory::create();
        for ($i = 0; $i < 150; ++$i) {
            $song = new Song();

            $song->setTitle($faker->words(5, true));
            $song->setArtist($faker->name());
            $song->setFormat($faker->randomElement(SongFormat::cases()));
            $song->setQuality($faker->randomElement(SongQuality::cases()));

            if ($faker->boolean()) {
                $song->setNexusBuildId(Uuid::fromString($faker->uuid()));
            }

            $manager->persist($song);

            if (0 === $i % 15) {
                $manager->flush();
            }
        }
        $manager->flush();
    }
}
