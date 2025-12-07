<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206082908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des entités Restaurant, OpeningHour, ExceptionalOpening et migration Ardoise.owner vers Ardoise.restaurant';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exceptional_opening (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, date DATE NOT NULL, opens_at TIME DEFAULT NULL, closes_at TIME DEFAULT NULL, label VARCHAR(255) NOT NULL, is_closed TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_8DE0379CB1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE opening_hour (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, day_of_week SMALLINT NOT NULL, opens_at TIME NOT NULL, closes_at TIME NOT NULL, label VARCHAR(50) DEFAULT NULL, INDEX IDX_969BD765B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, zip_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, departement VARCHAR(100) DEFAULT NULL, phone_number VARCHAR(20) DEFAULT NULL, facebook_page VARCHAR(255) DEFAULT NULL, INDEX IDX_EB95123F7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE exceptional_opening ADD CONSTRAINT FK_8DE0379CB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE opening_hour ADD CONSTRAINT FK_969BD765B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE restaurant ADD CONSTRAINT FK_EB95123F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ardoise DROP FOREIGN KEY FK_7E7E3D7A7E3C61F9');
        $this->addSql('DROP INDEX IDX_877C4D707E3C61F9 ON ardoise');
        $this->addSql('ALTER TABLE ardoise CHANGE owner_id restaurant_id INT NOT NULL');
        $this->addSql('ALTER TABLE ardoise ADD CONSTRAINT FK_877C4D70B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('CREATE INDEX IDX_877C4D70B1E7706E ON ardoise (restaurant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ardoise DROP FOREIGN KEY FK_877C4D70B1E7706E');
        $this->addSql('ALTER TABLE exceptional_opening DROP FOREIGN KEY FK_8DE0379CB1E7706E');
        $this->addSql('ALTER TABLE opening_hour DROP FOREIGN KEY FK_969BD765B1E7706E');
        $this->addSql('ALTER TABLE restaurant DROP FOREIGN KEY FK_EB95123F7E3C61F9');
        $this->addSql('DROP TABLE exceptional_opening');
        $this->addSql('DROP TABLE opening_hour');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP INDEX IDX_877C4D70B1E7706E ON ardoise');
        $this->addSql('ALTER TABLE ardoise CHANGE restaurant_id owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE ardoise ADD CONSTRAINT FK_7E7E3D7A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_877C4D707E3C61F9 ON ardoise (owner_id)');
    }
}
