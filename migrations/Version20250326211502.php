<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326211502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture ADD alternate_filepath VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE picture ADD alternate_file_mimetype VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE picture ADD has_alternate_picture BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE tsvector');
        $this->addSql('DROP INDEX idx_song_requests_search');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE tsvector');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE TEXT');
        $this->addSql('CREATE INDEX idx_song_requests_search ON song_request (search_vector)');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE TEXT');
        $this->addSql('ALTER TABLE picture DROP alternate_filepath');
        $this->addSql('ALTER TABLE picture DROP alternate_file_mimetype');
        $this->addSql('ALTER TABLE picture DROP has_alternate_picture');
    }
}
