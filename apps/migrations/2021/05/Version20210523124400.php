<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210523124400 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attachment ADD state_changed DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE edito ADD state_changed DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F2EC5FE02B36786B ON edito (title)');
        $this->addSql('ALTER TABLE email ADD state_changed DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE note_interne ADD state_changed DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A16352062B36786B ON note_interne (title)');
        $this->addSql('ALTER TABLE phone ADD state_changed DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD img_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD state_changed DATETIME DEFAULT NULL, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL, DROP img');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC06A9F55 FOREIGN KEY (img_id) REFERENCES attachment (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8D2B36786B ON post (title)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DC06A9F55 ON post (img_id)');
        $this->addSql('ALTER TABLE user ADD state_changed DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attachment DROP state_changed');
        $this->addSql('DROP INDEX UNIQ_F2EC5FE02B36786B ON edito');
        $this->addSql('ALTER TABLE edito DROP state_changed');
        $this->addSql('ALTER TABLE email DROP state_changed');
        $this->addSql('DROP INDEX UNIQ_A16352062B36786B ON note_interne');
        $this->addSql('ALTER TABLE note_interne DROP state_changed');
        $this->addSql('ALTER TABLE phone DROP state_changed');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC06A9F55');
        $this->addSql('DROP INDEX UNIQ_5A8A6C8D2B36786B ON post');
        $this->addSql('DROP INDEX IDX_5A8A6C8DC06A9F55 ON post');
        $this->addSql('ALTER TABLE post ADD img VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP img_id, DROP state_changed, DROP created, DROP updated');
        $this->addSql('ALTER TABLE user DROP state_changed');
    }
}
