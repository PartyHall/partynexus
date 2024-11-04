<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104203528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE song_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE song_request (id INT NOT NULL, requested_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, artist VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_36FC9EB04DA1E751 ON song_request (requested_by_id)');
        $this->addSql('COMMENT ON COLUMN song_request.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN song_request.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE song_request ADD CONSTRAINT FK_36FC9EB04DA1E751 FOREIGN KEY (requested_by_id) REFERENCES nexus_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE song_request_id_seq CASCADE');
        $this->addSql('ALTER TABLE song_request DROP CONSTRAINT FK_36FC9EB04DA1E751');
        $this->addSql('DROP TABLE song_request');
    }
}
