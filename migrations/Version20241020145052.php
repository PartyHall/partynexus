<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020145052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE appliance_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE export_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE nexus_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE song_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE appliance (id INT NOT NULL, owner_id INT DEFAULT NULL, name VARCHAR(32) NOT NULL, hardware_id UUID NOT NULL, api_token VARCHAR(512) NOT NULL, last_seen TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B4E6C1107E3C61F9 ON appliance (owner_id)');
        $this->addSql('COMMENT ON COLUMN appliance.hardware_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN appliance.last_seen IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE event (id UUID NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, author VARCHAR(255) DEFAULT NULL, datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, location VARCHAR(255) DEFAULT NULL, over BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77E3C61F9 ON event (owner_id)');
        $this->addSql('COMMENT ON COLUMN event.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.datetime IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE export (id INT NOT NULL, event_id UUID NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_428C169471F7E88B ON export (event_id)');
        $this->addSql('COMMENT ON COLUMN export.event_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN export.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE nexus_user (id INT NOT NULL, username VARCHAR(32) NOT NULL, password VARCHAR(512) NOT NULL, email VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE picture (id UUID NOT NULL, event_id UUID NOT NULL, taken_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, unattended BOOLEAN DEFAULT false NOT NULL, appliance_uuid UUID NOT NULL, filepath VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_16DB4F8971F7E88B ON picture (event_id)');
        $this->addSql('COMMENT ON COLUMN picture.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN picture.event_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN picture.taken_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN picture.appliance_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE refresh_tokens (id INT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('CREATE TABLE song (id INT NOT NULL, title VARCHAR(255) NOT NULL, artist VARCHAR(255) NOT NULL, cover_name VARCHAR(255) DEFAULT NULL, format VARCHAR(20) NOT NULL, quality VARCHAR(20) NOT NULL, music_brainz_id UUID DEFAULT NULL, spotify_id VARCHAR(255) DEFAULT NULL, nexus_build_id UUID DEFAULT NULL, hotspot INT DEFAULT NULL, ready BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN song.music_brainz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN song.nexus_build_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN song.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN song.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE song_file (type VARCHAR(64) NOT NULL, song_id INT NOT NULL, filename VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(song_id, type))');
        $this->addSql('CREATE INDEX IDX_FF0CE0E5A0BDB2F3 ON song_file (song_id)');
        $this->addSql('COMMENT ON COLUMN song_file.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN song_file.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE appliance ADD CONSTRAINT FK_B4E6C1107E3C61F9 FOREIGN KEY (owner_id) REFERENCES nexus_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA77E3C61F9 FOREIGN KEY (owner_id) REFERENCES nexus_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE export ADD CONSTRAINT FK_428C169471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F8971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE song_file ADD CONSTRAINT FK_FF0CE0E5A0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE appliance_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE export_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE nexus_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE song_id_seq CASCADE');
        $this->addSql('ALTER TABLE appliance DROP CONSTRAINT FK_B4E6C1107E3C61F9');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA77E3C61F9');
        $this->addSql('ALTER TABLE export DROP CONSTRAINT FK_428C169471F7E88B');
        $this->addSql('ALTER TABLE picture DROP CONSTRAINT FK_16DB4F8971F7E88B');
        $this->addSql('ALTER TABLE song_file DROP CONSTRAINT FK_FF0CE0E5A0BDB2F3');
        $this->addSql('DROP TABLE appliance');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE export');
        $this->addSql('DROP TABLE nexus_user');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE song');
        $this->addSql('DROP TABLE song_file');
    }
}
