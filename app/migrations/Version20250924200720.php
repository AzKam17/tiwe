<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250924200720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "order" ADD slug VARCHAR(6) NOT NULL');
        $this->addSql('ALTER TABLE "order" ALTER status DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5299398989D9B62 ON "order" (slug)');
        $this->addSql('ALTER TABLE order_item ALTER status DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE order_item ALTER status SET DEFAULT \'PENDING\'');
        $this->addSql('DROP INDEX UNIQ_F5299398989D9B62');
        $this->addSql('ALTER TABLE "order" DROP slug');
        $this->addSql('ALTER TABLE "order" ALTER status SET DEFAULT \'PENDING\'');
    }
}
