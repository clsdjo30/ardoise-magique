<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203152108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ardoise DROP FOREIGN KEY FK_7E7E3D7A7E3C61F9');
        $this->addSql('ALTER TABLE ardoise ADD template VARCHAR(50) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_7e7e3d7a7e3c61f9 ON ardoise');
        $this->addSql('CREATE INDEX IDX_877C4D707E3C61F9 ON ardoise (owner_id)');
        $this->addSql('ALTER TABLE ardoise ADD CONSTRAINT FK_7E7E3D7A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ardoise_item DROP FOREIGN KEY FK_C9F6E5E8727ACA70');
        $this->addSql('DROP INDEX idx_c9f6e5e8727aca70 ON ardoise_item');
        $this->addSql('CREATE INDEX IDX_FBBBAA92727ACA70 ON ardoise_item (parent_id)');
        $this->addSql('ALTER TABLE ardoise_item ADD CONSTRAINT FK_C9F6E5E8727ACA70 FOREIGN KEY (parent_id) REFERENCES ardoise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE ardoise DROP FOREIGN KEY FK_877C4D707E3C61F9');
        $this->addSql('ALTER TABLE ardoise DROP template');
        $this->addSql('DROP INDEX idx_877c4d707e3c61f9 ON ardoise');
        $this->addSql('CREATE INDEX IDX_7E7E3D7A7E3C61F9 ON ardoise (owner_id)');
        $this->addSql('ALTER TABLE ardoise ADD CONSTRAINT FK_877C4D707E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ardoise_item DROP FOREIGN KEY FK_FBBBAA92727ACA70');
        $this->addSql('DROP INDEX idx_fbbbaa92727aca70 ON ardoise_item');
        $this->addSql('CREATE INDEX IDX_C9F6E5E8727ACA70 ON ardoise_item (parent_id)');
        $this->addSql('ALTER TABLE ardoise_item ADD CONSTRAINT FK_FBBBAA92727ACA70 FOREIGN KEY (parent_id) REFERENCES ardoise (id)');
    }
}
