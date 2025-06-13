<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604115904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast ADD duration VARCHAR(100) DEFAULT NULL, ADD is_featured TINYINT(1) NOT NULL, ADD tags JSON NOT NULL COMMENT '(DC2Type:json)', ADD language VARCHAR(10) DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE file_path file_path VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE podcast DROP duration, DROP is_featured, DROP tags, DROP language, DROP published_at, CHANGE file_path file_path VARCHAR(255) NOT NULL
        SQL);
    }
}
