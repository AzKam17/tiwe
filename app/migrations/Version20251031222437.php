<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031222437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inventory_entry (id SERIAL NOT NULL, user_id UUID NOT NULL, product_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, quantity INT NOT NULL, price NUMERIC(10, 2) NOT NULL, notes TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_user_entry ON inventory_entry (user_id)');
        $this->addSql('CREATE INDEX idx_product_entry ON inventory_entry (product_id)');
        $this->addSql('COMMENT ON COLUMN inventory_entry.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN inventory_entry.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE inventory_entry ADD CONSTRAINT FK_F7BD3670A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inventory_entry ADD CONSTRAINT FK_F7BD36704584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product DROP stock');
        $this->addSql('ALTER TABLE product DROP price');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE inventory_entry DROP CONSTRAINT FK_F7BD3670A76ED395');
        $this->addSql('ALTER TABLE inventory_entry DROP CONSTRAINT FK_F7BD36704584665A');
        $this->addSql('DROP TABLE inventory_entry');
        $this->addSql('ALTER TABLE product ADD stock INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD price DOUBLE PRECISION NOT NULL');
    }
}
