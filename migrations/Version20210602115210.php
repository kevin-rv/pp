<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210602115210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, phone_number VARCHAR(20) DEFAULT NULL, home VARCHAR(266) DEFAULT NULL, birthday DATE DEFAULT NULL, email VARCHAR(320) DEFAULT NULL, relationship VARCHAR(255) DEFAULT NULL, work VARCHAR(255) DEFAULT NULL, INDEX IDX_4C62E638A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_event (contact_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_16841AA8E7A1254A (contact_id), INDEX IDX_16841AA871F7E88B (event_id), PRIMARY KEY(contact_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, planning_id INT DEFAULT NULL, short_description VARCHAR(45) DEFAULT NULL, full_description VARCHAR(255) DEFAULT NULL, start_datetime DATETIME NOT NULL, end_datetime DATETIME NOT NULL, INDEX IDX_3BAE0AA73D865311 (planning_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE planning (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(80) NOT NULL, INDEX IDX_D499BFF6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, planning_id INT DEFAULT NULL, short_description VARCHAR(255) NOT NULL, done DATE DEFAULT NULL, done_limit_date DATE DEFAULT NULL, INDEX IDX_527EDB253D865311 (planning_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(320) NOT NULL, password VARCHAR(255) NOT NULL, birthday DATE NOT NULL, phone_number VARCHAR(20) DEFAULT NULL, home VARCHAR(266) DEFAULT NULL, work VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE contact_event ADD CONSTRAINT FK_16841AA8E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_event ADD CONSTRAINT FK_16841AA871F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB253D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_event DROP FOREIGN KEY FK_16841AA8E7A1254A');
        $this->addSql('ALTER TABLE contact_event DROP FOREIGN KEY FK_16841AA871F7E88B');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA73D865311');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB253D865311');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638A76ED395');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6A76ED395');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE contact_event');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE planning');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE `user`');
    }
}
