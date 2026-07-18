<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260715195357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Configure refresh token IDs to use the existing PostgreSQL sequence.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER SEQUENCE refresh_tokens_id_seq OWNED BY refresh_tokens.id');
        $this->addSql("SELECT setval('refresh_tokens_id_seq', COALESCE((SELECT MAX(id) FROM refresh_tokens), 1), (SELECT COUNT(*) > 0 FROM refresh_tokens))");
        $this->addSql("ALTER TABLE refresh_tokens ALTER id SET DEFAULT nextval('refresh_tokens_id_seq')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE refresh_tokens ALTER id DROP DEFAULT');
        $this->addSql('ALTER SEQUENCE refresh_tokens_id_seq OWNED BY NONE');
    }
}
