<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210508190840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, planning_id INT DEFAULT NULL, short_description VARCHAR(45) DEFAULT NULL, full_description VARCHAR(255) DEFAULT NULL, start_datetime DATETIME NOT NULL, en_datetime DATETIME NOT NULL, INDEX IDX_3BAE0AA73D865311 (planning_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE planning (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(80) NOT NULL, INDEX IDX_D499BFF6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, planning_id INT DEFAULT NULL, short_description VARCHAR(45) NOT NULL, done DATE DEFAULT NULL, done_date DATE DEFAULT NULL, INDEX IDX_527EDB253D865311 (planning_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(254) NOT NULL, password VARCHAR(255) NOT NULL, birthday DATE NOT NULL, num INT NOT NULL, home VARCHAR(255) DEFAULT NULL, work VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB253D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA73D865311');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB253D865311');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6A76ED395');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE planning');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE `user`');
    }
}
