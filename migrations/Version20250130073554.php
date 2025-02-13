<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250130073554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE favorite_tasks_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE favorite_tasks (id INT NOT NULL, user_id INT NOT NULL, task_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E6D04EB3A76ED395 ON favorite_tasks (user_id)');
        $this->addSql('CREATE INDEX IDX_E6D04EB38DB60186 ON favorite_tasks (task_id)');
        $this->addSql('CREATE UNIQUE INDEX user_task_unique ON favorite_tasks (user_id, task_id)');
        $this->addSql('ALTER TABLE favorite_tasks ADD CONSTRAINT FK_E6D04EB3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite_tasks ADD CONSTRAINT FK_E6D04EB38DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA schema_name');
        $this->addSql('DROP SEQUENCE favorite_tasks_id_seq CASCADE');
        $this->addSql('ALTER TABLE favorite_tasks DROP CONSTRAINT FK_E6D04EB3A76ED395');
        $this->addSql('ALTER TABLE favorite_tasks DROP CONSTRAINT FK_E6D04EB38DB60186');
        $this->addSql('DROP TABLE favorite_tasks');
    }
}
