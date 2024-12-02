<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129192542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE backdrop (id SERIAL NOT NULL, album_id INT NOT NULL, title VARCHAR(128) NOT NULL, filepath VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EBFEDE681137ABCF ON backdrop (album_id)');
        $this->addSql('CREATE TABLE backdrop_album (id SERIAL NOT NULL, title VARCHAR(128) NOT NULL, author VARCHAR(64) NOT NULL, version INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE backdrop ADD CONSTRAINT FK_EBFEDE681137ABCF FOREIGN KEY (album_id) REFERENCES backdrop_album (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE backdrop DROP CONSTRAINT FK_EBFEDE681137ABCF');
        $this->addSql('DROP TABLE backdrop');
        $this->addSql('DROP TABLE backdrop_album');
    }
}
