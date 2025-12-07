<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206093513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renommer nom_restaurant en firstname et rendre slug nullable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE slug slug VARCHAR(255) DEFAULT NULL, CHANGE nom_restaurant firstname VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` CHANGE slug slug VARCHAR(255) NOT NULL, CHANGE firstname nom_restaurant VARCHAR(255) NOT NULL');
    }
}
