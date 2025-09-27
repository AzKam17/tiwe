<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927120112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ADD owner_id UUID NOT NULL');
        $this->addSql('ALTER TABLE company ALTER balance DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN company.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4FBF094F7E3C61F9 ON company (owner_id)');
        $this->addSql('ALTER TABLE order_item ALTER price DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE order_item ALTER price SET DEFAULT \'0\'');
        $this->addSql('ALTER TABLE company DROP CONSTRAINT FK_4FBF094F7E3C61F9');
        $this->addSql('DROP INDEX IDX_4FBF094F7E3C61F9');
        $this->addSql('ALTER TABLE company DROP owner_id');
        $this->addSql('ALTER TABLE company ALTER balance SET DEFAULT \'0\'');
    }
}
