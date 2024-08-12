<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240812130826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Step 1: Add the `slug` column with a temporary `NULL` constraint.
        $this->addSql('ALTER TABLE conference ADD slug VARCHAR(255) DEFAULT NULL');
        // Step 2: Generate the slug by concatenating the fields and updating the existing rows.
        $this->addSql("
            UPDATE conference 
            SET slug = LOWER(
                REPLACE(
                    CONCAT(city, '-', year), 
                    ' ', '-'
                )
            )
        ");
        // Step 3: Make the `slug` column `NOT NULL`.
        $this->addSql('ALTER TABLE conference ALTER COLUMN slug SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conference DROP slug');
    }
}
