<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20241227222203 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS unaccent');
        $this->addSql('ALTER TABLE song ADD COLUMN search_vector tsvector');
        $this->addSql('CREATE INDEX idx_songs_search ON song USING GIN(search_vector)');
        $this->addSql('UPDATE song SET search_vector = setweight(to_tsvector(\'french\', unaccent(COALESCE(title, \'\'))), \'A\') || setweight(to_tsvector(\'french\', unaccent(COALESCE(artist, \'\'))), \'B\')');

        $this->addSql('ALTER TABLE song_request ADD COLUMN search_vector tsvector');
        $this->addSql('CREATE INDEX idx_song_requests_search ON song_request USING GIN(search_vector)');
        $this->addSql('UPDATE song_request SET search_vector = setweight(to_tsvector(\'french\', unaccent(COALESCE(title, \'\'))), \'A\') || setweight(to_tsvector(\'french\', unaccent(COALESCE(artist, \'\'))), \'B\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS song_request_search_vector_update ON song');
        $this->addSql('DROP TRIGGER IF EXISTS song_search_vector_update ON song');
        $this->addSql('DROP FUNCTION IF EXISTS song_search_vector_update');
        $this->addSql('DROP INDEX IF EXISTS idx_song_requests_search');
        $this->addSql('DROP INDEX IF EXISTS idx_songs_search');
        $this->addSql('ALTER TABLE song_request DROP COLUMN search_vector');
        $this->addSql('ALTER TABLE song DROP COLUMN search_vector');
        $this->addSql('DROP EXTENSION IF EXISTS unaccent');
    }
}
