<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219153658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carte (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, valid_from DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', valid_to DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', is_published TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BAD4FFFD989D9B62 (slug), INDEX IDX_BAD4FFFDB1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carte_section (id INT AUTO_INCREMENT NOT NULL, carte_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, INDEX IDX_7CBB9EB5C9C7CEB6 (carte_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plat_catalogue (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A521F8AF7E3C61F9 (owner_id), INDEX IDX_A521F8AF12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plat_catalogue_tag (plat_catalogue_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_CA1EC7C62469A423 (plat_catalogue_id), INDEX IDX_CA1EC7C6BAD26311 (tag_id), PRIMARY KEY(plat_catalogue_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plat_categorie (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plat_variant (id INT AUTO_INCREMENT NOT NULL, plat_catalogue_id INT NOT NULL, label VARCHAR(255) NOT NULL, price_cents INT NOT NULL, tva_rate NUMERIC(5, 2) NOT NULL, is_default TINYINT(1) NOT NULL, INDEX IDX_76123F4A2469A423 (plat_catalogue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE section_item (id INT AUTO_INCREMENT NOT NULL, carte_section_id INT NOT NULL, plat_variant_id INT NOT NULL, position INT NOT NULL, INDEX IDX_9AA5C91516A53427 (carte_section_id), INDEX IDX_9AA5C915B02B9624 (plat_variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_389B783EA750E8 (label), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carte ADD CONSTRAINT FK_BAD4FFFDB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE carte_section ADD CONSTRAINT FK_7CBB9EB5C9C7CEB6 FOREIGN KEY (carte_id) REFERENCES carte (id)');
        $this->addSql('ALTER TABLE plat_catalogue ADD CONSTRAINT FK_A521F8AF7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE plat_catalogue ADD CONSTRAINT FK_A521F8AF12469DE2 FOREIGN KEY (category_id) REFERENCES plat_categorie (id)');
        $this->addSql('ALTER TABLE plat_catalogue_tag ADD CONSTRAINT FK_CA1EC7C62469A423 FOREIGN KEY (plat_catalogue_id) REFERENCES plat_catalogue (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plat_catalogue_tag ADD CONSTRAINT FK_CA1EC7C6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plat_variant ADD CONSTRAINT FK_76123F4A2469A423 FOREIGN KEY (plat_catalogue_id) REFERENCES plat_catalogue (id)');
        $this->addSql('ALTER TABLE section_item ADD CONSTRAINT FK_9AA5C91516A53427 FOREIGN KEY (carte_section_id) REFERENCES carte_section (id)');
        $this->addSql('ALTER TABLE section_item ADD CONSTRAINT FK_9AA5C915B02B9624 FOREIGN KEY (plat_variant_id) REFERENCES plat_variant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carte DROP FOREIGN KEY FK_BAD4FFFDB1E7706E');
        $this->addSql('ALTER TABLE carte_section DROP FOREIGN KEY FK_7CBB9EB5C9C7CEB6');
        $this->addSql('ALTER TABLE plat_catalogue DROP FOREIGN KEY FK_A521F8AF7E3C61F9');
        $this->addSql('ALTER TABLE plat_catalogue DROP FOREIGN KEY FK_A521F8AF12469DE2');
        $this->addSql('ALTER TABLE plat_catalogue_tag DROP FOREIGN KEY FK_CA1EC7C62469A423');
        $this->addSql('ALTER TABLE plat_catalogue_tag DROP FOREIGN KEY FK_CA1EC7C6BAD26311');
        $this->addSql('ALTER TABLE plat_variant DROP FOREIGN KEY FK_76123F4A2469A423');
        $this->addSql('ALTER TABLE section_item DROP FOREIGN KEY FK_9AA5C91516A53427');
        $this->addSql('ALTER TABLE section_item DROP FOREIGN KEY FK_9AA5C915B02B9624');
        $this->addSql('DROP TABLE carte');
        $this->addSql('DROP TABLE carte_section');
        $this->addSql('DROP TABLE plat_catalogue');
        $this->addSql('DROP TABLE plat_catalogue_tag');
        $this->addSql('DROP TABLE plat_categorie');
        $this->addSql('DROP TABLE plat_variant');
        $this->addSql('DROP TABLE section_item');
        $this->addSql('DROP TABLE tag');
    }
}
