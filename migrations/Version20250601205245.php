<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601205245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE archive ADD view_count INT DEFAULT 0 NOT NULL, ADD download_count INT DEFAULT 0 NOT NULL, ADD file_size BIGINT DEFAULT NULL, ADD file_mime_type VARCHAR(100) DEFAULT NULL, ADD original_file_name VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD view_count INT DEFAULT 0 NOT NULL, ADD favorite_count INT DEFAULT 0 NOT NULL, ADD featured_image_thumbnail VARCHAR(255) DEFAULT NULL, ADD reading_time INT DEFAULT NULL, ADD meta_description LONGTEXT DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD approved_by_id INT DEFAULT NULL, ADD ip_address VARCHAR(45) DEFAULT NULL, ADD user_agent LONGTEXT DEFAULT NULL, ADD approved_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C2D234F6A FOREIGN KEY (approved_by_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526C2D234F6A ON comment (approved_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement ADD view_count INT DEFAULT 0 NOT NULL, ADD completion_count INT DEFAULT 0 NOT NULL, ADD support_file_size BIGINT DEFAULT NULL, ADD audio_file_size BIGINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post ADD edited_by_id INT DEFAULT NULL, ADD edited_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD ip_address VARCHAR(45) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5ADD7B2EBC FOREIGN KEY (edited_by_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_996BCC5ADD7B2EBC ON forum_post (edited_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_topic ADD reply_count INT DEFAULT 0 NOT NULL, ADD last_post_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast ADD view_count INT DEFAULT 0 NOT NULL, ADD download_count INT DEFAULT 0 NOT NULL, ADD calculated_duration VARCHAR(20) DEFAULT NULL, ADD file_size BIGINT DEFAULT NULL, ADD external_url VARCHAR(500) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD bio LONGTEXT DEFAULT NULL, ADD location VARCHAR(100) DEFAULT NULL, CHANGE phone phone VARCHAR(20) NOT NULL, CHANGE last_login_user_agent last_login_user_agent LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE archive DROP view_count, DROP download_count, DROP file_size, DROP file_mime_type, DROP original_file_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP view_count, DROP favorite_count, DROP featured_image_thumbnail, DROP reading_time, DROP meta_description, DROP published_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526C2D234F6A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9474526C2D234F6A ON comment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP approved_by_id, DROP ip_address, DROP user_agent, DROP approved_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement DROP view_count, DROP completion_count, DROP support_file_size, DROP audio_file_size
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5ADD7B2EBC
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_996BCC5ADD7B2EBC ON forum_post
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post DROP edited_by_id, DROP edited_at, DROP ip_address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_topic DROP reply_count, DROP last_post_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast DROP view_count, DROP download_count, DROP calculated_duration, DROP file_size, DROP external_url
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` DROP bio, DROP location, CHANGE phone phone VARCHAR(255) NOT NULL, CHANGE last_login_user_agent last_login_user_agent TEXT DEFAULT NULL
        SQL);
    }
}
