<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430213119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE app_faq (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, question VARCHAR(500) NOT NULL, answer LONGTEXT NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_C68DC60BB03A8386 (created_by_id), INDEX IDX_C68DC60BA6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE archive (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, is_published TINYINT(1) NOT NULL, period_year INT DEFAULT NULL, historical_context LONGTEXT DEFAULT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D5FC5D9CF675F31B (author_id), INDEX IDX_D5FC5D9CB03A8386 (created_by_id), INDEX IDX_D5FC5D9CA6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE archive_user (archive_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_BDB600D22956195F (archive_id), INDEX IDX_BDB600D2A76ED395 (user_id), PRIMARY KEY(archive_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, category_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, excerpt VARCHAR(255) NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, is_published TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_23A0E66F675F31B (author_id), INDEX IDX_23A0E6612469DE2 (category_id), INDEX IDX_23A0E66B03A8386 (created_by_id), INDEX IDX_23A0E66A6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE article_user (article_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3DD151487294869C (article_id), INDEX IDX_3DD15148A76ED395 (user_id), PRIMARY KEY(article_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_64C19C1B03A8386 (created_by_id), INDEX IDX_64C19C1A6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, article_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, content LONGTEXT NOT NULL, is_approved TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526C7294869C (article_id), INDEX IDX_9474526CB03A8386 (created_by_id), INDEX IDX_9474526CA6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, is_read TINYINT(1) NOT NULL, read_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4C62E638B03A8386 (created_by_id), INDEX IDX_4C62E638A6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE enseignement (id INT AUTO_INCREMENT NOT NULL, maitre_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, is_published TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_BD310CCCF133C25 (maitre_id), INDEX IDX_BD310CCB03A8386 (created_by_id), INDEX IDX_BD310CCA6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE favoris (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, podcast_id INT NOT NULL, article_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8933C432A76ED395 (user_id), INDEX IDX_8933C432786136AB (podcast_id), INDEX IDX_8933C4327294869C (article_id), INDEX IDX_8933C432B03A8386 (created_by_id), INDEX IDX_8933C432A6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE forum_post (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, topic_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, content LONGTEXT NOT NULL, is_approved TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_996BCC5AF675F31B (author_id), INDEX IDX_996BCC5A1F55203D (topic_id), INDEX IDX_996BCC5AB03A8386 (created_by_id), INDEX IDX_996BCC5AA6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE forum_topic (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, is_locked TINYINT(1) NOT NULL, is_pinned TINYINT(1) NOT NULL, view_count INT NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_853478CCF675F31B (author_id), INDEX IDX_853478CCB03A8386 (created_by_id), INDEX IDX_853478CCA6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE maitre_islamique (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, biographie LONGTEXT NOT NULL, photo VARCHAR(255) DEFAULT NULL, enseignements LONGTEXT DEFAULT NULL, specialite VARCHAR(255) DEFAULT NULL, annee_naissance INT DEFAULT NULL, annee_deces INT DEFAULT NULL, is_published TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9ED0CEBBF675F31B (author_id), INDEX IDX_9ED0CEBBB03A8386 (created_by_id), INDEX IDX_9ED0CEBBA6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE params (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, use_terms LONGTEXT DEFAULT NULL, real_id INT NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8FCE0EF3B03A8386 (created_by_id), INDEX IDX_8FCE0EF3A6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE podcast (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, created_by_id INT DEFAULT NULL, modfied_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, is_published TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, valid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D7E805BDF675F31B (author_id), INDEX IDX_D7E805BDB03A8386 (created_by_id), INDEX IDX_D7E805BDA6B602BB (modfied_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE podcast_user (podcast_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_66B74805786136AB (podcast_id), INDEX IDX_66B74805A76ED395 (user_id), PRIMARY KEY(podcast_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, profile_picture VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE app_faq ADD CONSTRAINT FK_C68DC60BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE app_faq ADD CONSTRAINT FK_C68DC60BA6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive ADD CONSTRAINT FK_D5FC5D9CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive ADD CONSTRAINT FK_D5FC5D9CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive ADD CONSTRAINT FK_D5FC5D9CA6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive_user ADD CONSTRAINT FK_BDB600D22956195F FOREIGN KEY (archive_id) REFERENCES archive (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive_user ADD CONSTRAINT FK_BDB600D2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT FK_23A0E66B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT FK_23A0E66A6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_user ADD CONSTRAINT FK_3DD151487294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_user ADD CONSTRAINT FK_3DD15148A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C1B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C1A6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526CA6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD CONSTRAINT FK_4C62E638B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement ADD CONSTRAINT FK_BD310CCCF133C25 FOREIGN KEY (maitre_id) REFERENCES maitre_islamique (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement ADD CONSTRAINT FK_BD310CCB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement ADD CONSTRAINT FK_BD310CCA6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris ADD CONSTRAINT FK_8933C432A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris ADD CONSTRAINT FK_8933C432786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris ADD CONSTRAINT FK_8933C4327294869C FOREIGN KEY (article_id) REFERENCES article (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris ADD CONSTRAINT FK_8933C432B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris ADD CONSTRAINT FK_8933C432A6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5AF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5A1F55203D FOREIGN KEY (topic_id) REFERENCES forum_topic (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5AA6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_topic ADD CONSTRAINT FK_853478CCF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_topic ADD CONSTRAINT FK_853478CCB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_topic ADD CONSTRAINT FK_853478CCA6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE maitre_islamique ADD CONSTRAINT FK_9ED0CEBBF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE maitre_islamique ADD CONSTRAINT FK_9ED0CEBBB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE maitre_islamique ADD CONSTRAINT FK_9ED0CEBBA6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE params ADD CONSTRAINT FK_8FCE0EF3B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE params ADD CONSTRAINT FK_8FCE0EF3A6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BDF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BDB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BDA6B602BB FOREIGN KEY (modfied_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast_user ADD CONSTRAINT FK_66B74805786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast_user ADD CONSTRAINT FK_66B74805A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE app_faq DROP FOREIGN KEY FK_C68DC60BB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE app_faq DROP FOREIGN KEY FK_C68DC60BA6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive DROP FOREIGN KEY FK_D5FC5D9CF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive DROP FOREIGN KEY FK_D5FC5D9CB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive DROP FOREIGN KEY FK_D5FC5D9CA6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive_user DROP FOREIGN KEY FK_BDB600D22956195F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE archive_user DROP FOREIGN KEY FK_BDB600D2A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP FOREIGN KEY FK_23A0E66B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP FOREIGN KEY FK_23A0E66A6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_user DROP FOREIGN KEY FK_3DD151487294869C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_user DROP FOREIGN KEY FK_3DD15148A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP FOREIGN KEY FK_64C19C1B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP FOREIGN KEY FK_64C19C1A6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526CB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638A6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement DROP FOREIGN KEY FK_BD310CCCF133C25
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement DROP FOREIGN KEY FK_BD310CCB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement DROP FOREIGN KEY FK_BD310CCA6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432786136AB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris DROP FOREIGN KEY FK_8933C4327294869C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432A6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5AF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5A1F55203D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5AB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5AA6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_topic DROP FOREIGN KEY FK_853478CCF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_topic DROP FOREIGN KEY FK_853478CCB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_topic DROP FOREIGN KEY FK_853478CCA6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE maitre_islamique DROP FOREIGN KEY FK_9ED0CEBBF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE maitre_islamique DROP FOREIGN KEY FK_9ED0CEBBB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE maitre_islamique DROP FOREIGN KEY FK_9ED0CEBBA6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE params DROP FOREIGN KEY FK_8FCE0EF3B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE params DROP FOREIGN KEY FK_8FCE0EF3A6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BDF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BDB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BDA6B602BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast_user DROP FOREIGN KEY FK_66B74805786136AB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast_user DROP FOREIGN KEY FK_66B74805A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE app_faq
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE archive
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE archive_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contact
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE enseignement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE favoris
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE forum_post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE forum_topic
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE maitre_islamique
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE params
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE podcast
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE podcast_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
