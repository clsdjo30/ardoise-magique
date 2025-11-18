<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Ardoise, Section, and Plat tables for L\'Ardoise Magique';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ardoise (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, date_creation DATE NOT NULL, is_active BOOLEAN NOT NULL, prix_complet NUMERIC(10, 2) DEFAULT NULL, prix_entree_plat NUMERIC(10, 2) DEFAULT NULL, prix_plat_dessert NUMERIC(10, 2) DEFAULT NULL, afficher_prix_formules BOOLEAN NOT NULL)');
        $this->addSql('CREATE TABLE section (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ardoise_id INTEGER NOT NULL, titre VARCHAR(255) NOT NULL, ordre INTEGER NOT NULL, CONSTRAINT FK_2D737AEF8A5C4FE9 FOREIGN KEY (ardoise_id) REFERENCES ardoise (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2D737AEF8A5C4FE9 ON section (ardoise_id)');
        $this->addSql('CREATE TABLE plat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, section_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, prix NUMERIC(10, 2) DEFAULT NULL, CONSTRAINT FK_2038A207D823E37A FOREIGN KEY (section_id) REFERENCES section (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2038A207D823E37A ON plat (section_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ardoise');
        $this->addSql('DROP TABLE section');
        $this->addSql('DROP TABLE plat');
    }
}
