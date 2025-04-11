<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250411160040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE magic_password (id SERIAL NOT NULL, user_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, code VARCHAR(255) NOT NULL, used BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8971003CA76ED395 ON magic_password (user_id)');
        $this->addSql('COMMENT ON COLUMN magic_password.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE magic_password ADD CONSTRAINT FK_8971003CA76ED395 FOREIGN KEY (user_id) REFERENCES nexus_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE tsvector');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE tsvector');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE magic_password DROP CONSTRAINT FK_8971003CA76ED395');
        $this->addSql('DROP TABLE magic_password');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE TEXT');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE TEXT');
    }
}
