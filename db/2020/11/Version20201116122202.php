<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201116122202 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE user_param (key VARCHAR(64) NOT NULL, user_id INT NOT NULL, value JSON DEFAULT NULL, created TIMESTAMP(0) WITH TIME ZONE NOT NULL, modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(user_id, key))');
        $this->addSql('CREATE INDEX IDX_463887D1A76ED395 ON user_param (user_id)');
        $this->addSql('CREATE UNIQUE INDEX user_unique ON user_param (user_id, key)');
        $this->addSql('ALTER TABLE user_param ADD CONSTRAINT FK_463887D1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ALTER sign_up SET DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE user_param');
        $this->addSql('ALTER TABLE "user" ALTER sign_up SET DEFAULT \'now()\'');
    }
}
