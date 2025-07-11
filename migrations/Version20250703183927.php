<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703183927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE magic_link_id_seq CASCADE');
        $this->addSql('ALTER TABLE magic_link DROP CONSTRAINT fk_6b40b1c6a76ed395');
        $this->addSql('DROP TABLE magic_link');
        $this->addSql('ALTER TABLE event ADD registration_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD registration_enabled BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE tsvector');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE tsvector');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE magic_link_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE magic_link (id SERIAL NOT NULL, user_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, code VARCHAR(255) NOT NULL, used BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_6b40b1c6a76ed395 ON magic_link (user_id)');
        $this->addSql('COMMENT ON COLUMN magic_link.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE magic_link ADD CONSTRAINT fk_6b40b1c6a76ed395 FOREIGN KEY (user_id) REFERENCES nexus_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event DROP registration_key');
        $this->addSql('ALTER TABLE event DROP registration_enabled');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE TEXT');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE TEXT');
    }
}
