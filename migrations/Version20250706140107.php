<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250706140107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD user_registration_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD user_registration_enabled BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA740349C6F ON event (user_registration_code)');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE tsvector');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE tsvector');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE TEXT');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA740349C6F');
        $this->addSql('ALTER TABLE event DROP user_registration_code');
        $this->addSql('ALTER TABLE event DROP user_registration_enabled');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE TEXT');
    }
}
