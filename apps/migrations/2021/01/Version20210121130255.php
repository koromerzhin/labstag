<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210121130255 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE edito ADD state LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', DROP enable');
        $this->addSql('ALTER TABLE email ADD state LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', DROP verif');
        $this->addSql('ALTER TABLE note_interne ADD state LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', DROP enable');
        $this->addSql('ALTER TABLE phone ADD state LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE user ADD state LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', DROP enable, DROP lost, DROP verif');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE edito ADD enable TINYINT(1) NOT NULL, DROP state');
        $this->addSql('ALTER TABLE email ADD verif TINYINT(1) NOT NULL, DROP state');
        $this->addSql('ALTER TABLE note_interne ADD enable TINYINT(1) NOT NULL, DROP state');
        $this->addSql('ALTER TABLE phone DROP state');
        $this->addSql('ALTER TABLE user ADD enable TINYINT(1) NOT NULL, ADD lost TINYINT(1) NOT NULL, ADD verif TINYINT(1) NOT NULL, DROP state');
    }
}
