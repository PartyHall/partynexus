<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250523114140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE display_board_key (id SERIAL NOT NULL, event_id UUID NOT NULL, key VARCHAR(512) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A0C0BFA871F7E88B ON display_board_key (event_id)');
        $this->addSql('COMMENT ON COLUMN display_board_key.event_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE display_board_key ADD CONSTRAINT FK_A0C0BFA871F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE tsvector');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE tsvector');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE display_board_key DROP CONSTRAINT FK_A0C0BFA871F7E88B');
        $this->addSql('DROP TABLE display_board_key');
        $this->addSql('ALTER TABLE song_request ALTER search_vector TYPE TEXT');
        $this->addSql('ALTER TABLE song ALTER search_vector TYPE TEXT');
    }
}
