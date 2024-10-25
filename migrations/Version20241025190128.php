<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025190128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_event (event_source UUID NOT NULL, event_target UUID NOT NULL, PRIMARY KEY(event_source, event_target))');
        $this->addSql('CREATE INDEX IDX_7AB5BB8B6D130821 ON event_event (event_source)');
        $this->addSql('CREATE INDEX IDX_7AB5BB8B74F658AE ON event_event (event_target)');
        $this->addSql('COMMENT ON COLUMN event_event.event_source IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event_event.event_target IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE event_event ADD CONSTRAINT FK_7AB5BB8B6D130821 FOREIGN KEY (event_source) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_event ADD CONSTRAINT FK_7AB5BB8B74F658AE FOREIGN KEY (event_target) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE nexus_user ALTER username DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event_event DROP CONSTRAINT FK_7AB5BB8B6D130821');
        $this->addSql('ALTER TABLE event_event DROP CONSTRAINT FK_7AB5BB8B74F658AE');
        $this->addSql('DROP TABLE event_event');
        $this->addSql('ALTER TABLE nexus_user ALTER username SET NOT NULL');
    }
}
