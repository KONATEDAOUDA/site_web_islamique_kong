<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602115400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(64) NOT NULL, label VARCHAR(255) NOT NULL, sections JSON NOT NULL COMMENT '(DC2Type:json)', UNIQUE INDEX UNIQ_57698A6AE09C0C92 (role_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement ADD author_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement ADD CONSTRAINT FK_BD310CCF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BD310CCF675F31B ON enseignement (author_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE role
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_role
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement DROP FOREIGN KEY FK_BD310CCF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BD310CCF675F31B ON enseignement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE enseignement DROP author_id
        SQL);
    }
}
