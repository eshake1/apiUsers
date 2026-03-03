<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302142703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649AA08CB10CE70D424 ON user');
        $this->addSql('ALTER TABLE user ADD password_hash VARCHAR(255) NOT NULL, DROP pass');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AA08CB10 ON user (login)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649AA08CB10 ON user');
        $this->addSql('ALTER TABLE user ADD pass VARCHAR(8) NOT NULL, DROP password_hash');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AA08CB10CE70D424 ON user (login, pass)');
    }
}
