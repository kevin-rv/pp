<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210506221848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact ADD planning_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E6383D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('CREATE INDEX IDX_4C62E6383D865311 ON contact (planning_id)');
        $this->addSql('ALTER TABLE event ADD planning_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA73D865311 ON event (planning_id)');
        $this->addSql('ALTER TABLE planning ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_D499BFF6A76ED395 ON planning (user_id)');
        $this->addSql('ALTER TABLE task ADD planning_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB253D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('CREATE INDEX IDX_527EDB253D865311 ON task (planning_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E6383D865311');
        $this->addSql('DROP INDEX IDX_4C62E6383D865311 ON contact');
        $this->addSql('ALTER TABLE contact DROP planning_id');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA73D865311');
        $this->addSql('DROP INDEX IDX_3BAE0AA73D865311 ON event');
        $this->addSql('ALTER TABLE event DROP planning_id');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6A76ED395');
        $this->addSql('DROP INDEX IDX_D499BFF6A76ED395 ON planning');
        $this->addSql('ALTER TABLE planning DROP user_id');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB253D865311');
        $this->addSql('DROP INDEX IDX_527EDB253D865311 ON task');
        $this->addSql('ALTER TABLE task DROP planning_id');
    }
}
