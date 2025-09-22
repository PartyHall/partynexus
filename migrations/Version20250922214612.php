<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922214612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ALTER user_registration_code SET NOT NULL');
        $this->addSql('ALTER TABLE nexus_user ADD oauth_user_id VARCHAR(128) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6097C3D7B4687CE0 ON nexus_user (oauth_user_id)');
        $this->addSql('ALTER TABLE song ALTER music_brainz_id TYPE VARCHAR(64)');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE tsvector');
        $this->addSql('COMMENT ON COLUMN song.music_brainz_id IS NULL');
        $this->addSql('ALTER TABLE song DROP search_vector');
        $this->addSql('ALTER TABLE song_request DROP search_vector');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE song ADD search_vector TEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_songs_search ON song (search_vector)');
        $this->addSql('ALTER TABLE song_request ADD search_vector TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE song ALTER music_brainz_id TYPE UUID');
        $this->addSql('ALTER TABLE song ALTER music_brainz_id TYPE UUID');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN song.music_brainz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE event ALTER user_registration_code DROP NOT NULL');
        $this->addSql('DROP INDEX UNIQ_6097C3D7B4687CE0');
        $this->addSql('ALTER TABLE nexus_user DROP oauth_user_id');
    }
}
