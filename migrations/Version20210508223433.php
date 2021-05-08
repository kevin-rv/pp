<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210508223433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, home VARCHAR(255) DEFAULT NULL, birthday DATE DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, relationship VARCHAR(255) DEFAULT NULL, work VARCHAR(255) DEFAULT NULL, INDEX IDX_4C62E638A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_event (contact_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_16841AA8E7A1254A (contact_id), INDEX IDX_16841AA871F7E88B (event_id), PRIMARY KEY(contact_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE contact_event ADD CONSTRAINT FK_16841AA8E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_event ADD CONSTRAINT FK_16841AA871F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_event DROP FOREIGN KEY FK_16841AA8E7A1254A');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE contact_event');
        $this->addSql('ALTER TABLE `user` DROP name');
    }
}
