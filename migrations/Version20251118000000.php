<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial database schema for User, Ardoise, Section, and Plat entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles CLOB NOT NULL --(DC2Type:json)
            ,
            password VARCHAR(255) NOT NULL,
            nom_restaurant VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649989D9B62 ON user (slug)');

        $this->addSql('CREATE TABLE ardoise (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            restaurateur_id INTEGER NOT NULL,
            titre VARCHAR(255) NOT NULL,
            date_creation DATETIME NOT NULL,
            is_active BOOLEAN NOT NULL,
            CONSTRAINT FK_8B4C4A9FFBA0C98B FOREIGN KEY (restaurateur_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_8B4C4A9FFBA0C98B ON ardoise (restaurateur_id)');

        $this->addSql('CREATE TABLE section (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            ardoise_id INTEGER NOT NULL,
            titre VARCHAR(255) NOT NULL,
            ordre INTEGER NOT NULL,
            CONSTRAINT FK_2D737AEF4B0E4A4D FOREIGN KEY (ardoise_id) REFERENCES ardoise (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_2D737AEF4B0E4A4D ON section (ardoise_id)');

        $this->addSql('CREATE TABLE plat (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            section_id INTEGER NOT NULL,
            nom VARCHAR(255) NOT NULL,
            description CLOB DEFAULT NULL,
            prix NUMERIC(10, 2) NOT NULL,
            ordre INTEGER NOT NULL,
            CONSTRAINT FK_2038A207D823E37A FOREIGN KEY (section_id) REFERENCES section (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_2038A207D823E37A ON plat (section_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE plat');
        $this->addSql('DROP TABLE section');
        $this->addSql('DROP TABLE ardoise');
        $this->addSql('DROP TABLE user');
    }
}
