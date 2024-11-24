<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241124202922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song_session DROP appliace_id');
        $this->addSql('ALTER TABLE song_session DROP CONSTRAINT fk_dd2fc4fba0bdb2f3');
        $this->addSql('DROP INDEX idx_dd2fc4fba0bdb2f3');
        $this->addSql('ALTER TABLE song_session DROP song_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song_session ADD song_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE song_session ADD CONSTRAINT fk_dd2fc4fba0bdb2f3 FOREIGN KEY (song_id) REFERENCES song (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_dd2fc4fba0bdb2f3 ON song_session (song_id)');
        $this->addSql('ALTER TABLE song_session ADD appliace_id INT NOT NULL');
    }
}
