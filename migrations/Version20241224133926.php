<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241224133926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_authentication_log (id SERIAL NOT NULL, user_id INT DEFAULT NULL, ip VARCHAR(40) DEFAULT NULL, authed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5ECE40D6A76ED395 ON user_authentication_log (user_id)');
        $this->addSql('COMMENT ON COLUMN user_authentication_log.authed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_authentication_log ADD CONSTRAINT FK_5ECE40D6A76ED395 FOREIGN KEY (user_id) REFERENCES nexus_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE nexus_user ADD firstname VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE nexus_user ADD lastname VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_authentication_log DROP CONSTRAINT FK_5ECE40D6A76ED395');
        $this->addSql('DROP TABLE user_authentication_log');
        $this->addSql('ALTER TABLE nexus_user DROP firstname');
        $this->addSql('ALTER TABLE nexus_user DROP lastname');
    }
}
