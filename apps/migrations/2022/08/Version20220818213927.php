<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220818213927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62024F529FD');
        $this->addSql('CREATE TABLE block (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', title VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_831B97222B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE block_footer (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', block_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AB9F53D8E9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE block_header (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', block_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_27DCFE4AE9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE block_html (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', block_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', content LONGTEXT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, INDEX IDX_DE17AA9EE9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE block_link (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', footer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', header_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', external TINYINT(1) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, url LONGTEXT DEFAULT NULL, INDEX IDX_F0C2CB8A2412A144 (footer_id), INDEX IDX_F0C2CB8A2EF91FD8 (header_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meta (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', chapter_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', edito_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', history_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', post_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', description VARCHAR(255) DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, INDEX IDX_D7F21435579F4768 (chapter_id), INDEX IDX_D7F214355B3CFAAA (edito_id), INDEX IDX_D7F214351E058452 (history_id), INDEX IDX_D7F21435C4663E4 (page_id), INDEX IDX_D7F214354B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paragraph (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', edito_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', memo_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', post_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', background VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, fond VARCHAR(255) DEFAULT NULL, position INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_7DD398625B3CFAAA (edito_id), INDEX IDX_7DD39862B4D32439 (memo_id), INDEX IDX_7DD39862C4663E4 (page_id), INDEX IDX_7DD398624B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paragraph_text (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', paragraph_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', content LONGTEXT DEFAULT NULL, INDEX IDX_E8F6B6688B50597F (paragraph_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE block_footer ADD CONSTRAINT FK_AB9F53D8E9ED820C FOREIGN KEY (block_id) REFERENCES block (id)');
        $this->addSql('ALTER TABLE block_header ADD CONSTRAINT FK_27DCFE4AE9ED820C FOREIGN KEY (block_id) REFERENCES block (id)');
        $this->addSql('ALTER TABLE block_html ADD CONSTRAINT FK_DE17AA9EE9ED820C FOREIGN KEY (block_id) REFERENCES block (id)');
        $this->addSql('ALTER TABLE block_link ADD CONSTRAINT FK_F0C2CB8A2412A144 FOREIGN KEY (footer_id) REFERENCES block_footer (id)');
        $this->addSql('ALTER TABLE block_link ADD CONSTRAINT FK_F0C2CB8A2EF91FD8 FOREIGN KEY (header_id) REFERENCES block_header (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F21435579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F214355B3CFAAA FOREIGN KEY (edito_id) REFERENCES edito (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F214351E058452 FOREIGN KEY (history_id) REFERENCES history (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F21435C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F214354B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398625B3CFAAA FOREIGN KEY (edito_id) REFERENCES edito (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862B4D32439 FOREIGN KEY (memo_id) REFERENCES memo (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398624B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE paragraph_text ADD CONSTRAINT FK_E8F6B6688B50597F FOREIGN KEY (paragraph_id) REFERENCES paragraph (id)');
        $this->addSql('DROP TABLE layout');
        $this->addSql('ALTER TABLE bookmark DROP meta_description, DROP meta_keywords');
        $this->addSql('ALTER TABLE chapter DROP meta_description, DROP meta_keywords');
        $this->addSql('ALTER TABLE edito DROP meta_description, DROP meta_keywords, CHANGE content content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE history DROP meta_description, DROP meta_keywords');
        $this->addSql('ALTER TABLE memo CHANGE content content LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_140AB62024F529FD ON page');
        $this->addSql('ALTER TABLE page DROP reflayout_id');
        $this->addSql('ALTER TABLE post DROP meta_description, DROP meta_keywords, CHANGE content content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE queue_name queue_name VARCHAR(190) NOT NULL');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE layout (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE block_footer DROP FOREIGN KEY FK_AB9F53D8E9ED820C');
        $this->addSql('ALTER TABLE block_header DROP FOREIGN KEY FK_27DCFE4AE9ED820C');
        $this->addSql('ALTER TABLE block_html DROP FOREIGN KEY FK_DE17AA9EE9ED820C');
        $this->addSql('ALTER TABLE block_link DROP FOREIGN KEY FK_F0C2CB8A2412A144');
        $this->addSql('ALTER TABLE block_link DROP FOREIGN KEY FK_F0C2CB8A2EF91FD8');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F21435579F4768');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F214355B3CFAAA');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F214351E058452');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F21435C4663E4');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F214354B89032C');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398625B3CFAAA');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862B4D32439');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862C4663E4');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398624B89032C');
        $this->addSql('ALTER TABLE paragraph_text DROP FOREIGN KEY FK_E8F6B6688B50597F');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE block_footer');
        $this->addSql('DROP TABLE block_header');
        $this->addSql('DROP TABLE block_html');
        $this->addSql('DROP TABLE block_link');
        $this->addSql('DROP TABLE meta');
        $this->addSql('DROP TABLE paragraph');
        $this->addSql('DROP TABLE paragraph_text');
        $this->addSql('ALTER TABLE page ADD reflayout_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62024F529FD FOREIGN KEY (reflayout_id) REFERENCES layout (id)');
        $this->addSql('CREATE INDEX IDX_140AB62024F529FD ON page (reflayout_id)');
        $this->addSql('ALTER TABLE bookmark ADD meta_description VARCHAR(255) DEFAULT NULL, ADD meta_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE memo CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE chapter ADD meta_description VARCHAR(255) DEFAULT NULL, ADD meta_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD meta_description VARCHAR(255) DEFAULT NULL, ADD meta_keywords VARCHAR(255) DEFAULT NULL, CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE history ADD meta_description VARCHAR(255) DEFAULT NULL, ADD meta_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE queue_name queue_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE edito ADD meta_description VARCHAR(255) DEFAULT NULL, ADD meta_keywords VARCHAR(255) DEFAULT NULL, CHANGE content content LONGTEXT NOT NULL');
    }
}
