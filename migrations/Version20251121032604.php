<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale pour L'Ardoise Magique v3.0
 * Architecture avec menus séparés (DAILY/SPECIAL) et MariaDB
 */
final class Version20251121032604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables user, ardoise et ardoise_item pour L\'Ardoise Magique v3.0';
    }

    public function up(Schema $schema): void
    {
        // Table user
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            nom_restaurant VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            UNIQUE INDEX UNIQ_8D93D649989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Table ardoise
        $this->addSql('CREATE TABLE ardoise (
            id INT AUTO_INCREMENT NOT NULL,
            owner_id INT NOT NULL,
            titre VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            type VARCHAR(20) NOT NULL,
            status TINYINT(1) NOT NULL,
            daily_entree LONGTEXT DEFAULT NULL,
            daily_plat LONGTEXT DEFAULT NULL,
            daily_dessert LONGTEXT DEFAULT NULL,
            price_epd NUMERIC(10, 2) DEFAULT NULL,
            price_ep NUMERIC(10, 2) DEFAULT NULL,
            price_pd NUMERIC(10, 2) DEFAULT NULL,
            special_global_price NUMERIC(10, 2) DEFAULT NULL,
            INDEX IDX_7E7E3D7A7E3C61F9 (owner_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Table ardoise_item
        $this->addSql('CREATE TABLE ardoise_item (
            id INT AUTO_INCREMENT NOT NULL,
            parent_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            price NUMERIC(10, 2) DEFAULT NULL,
            position INT NOT NULL,
            INDEX IDX_C9F6E5E8727ACA70 (parent_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Foreign keys
        $this->addSql('ALTER TABLE ardoise ADD CONSTRAINT FK_7E7E3D7A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ardoise_item ADD CONSTRAINT FK_C9F6E5E8727ACA70 FOREIGN KEY (parent_id) REFERENCES ardoise (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ardoise DROP FOREIGN KEY FK_7E7E3D7A7E3C61F9');
        $this->addSql('ALTER TABLE ardoise_item DROP FOREIGN KEY FK_C9F6E5E8727ACA70');
        $this->addSql('DROP TABLE ardoise_item');
        $this->addSql('DROP TABLE ardoise');
        $this->addSql('DROP TABLE `user`');
    }
}
