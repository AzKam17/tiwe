<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250924185601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status column to order table with default PENDING';
    }

    public function up(Schema $schema): void
    {
        // Use double quotes for reserved table name
        $this->addSql('ALTER TABLE "order" ADD COLUMN status VARCHAR(255) NOT NULL DEFAULT \'PENDING\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP COLUMN status');
    }
}
