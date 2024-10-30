<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029085138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nexus_user ALTER username SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6097C3D7F85E0677 ON nexus_user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6097C3D7E7927C74 ON nexus_user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_6097C3D7F85E0677');
        $this->addSql('DROP INDEX UNIQ_6097C3D7E7927C74');
        $this->addSql('ALTER TABLE nexus_user ALTER username DROP NOT NULL');
    }
}