<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250516093834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE retweet DROP FOREIGN KEY FK_45E67DB31041E39B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE retweet DROP FOREIGN KEY FK_45E67DB3A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE retweet
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tweet ADD original_tweet_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tweet ADD CONSTRAINT FK_3D660A3B331E048A FOREIGN KEY (original_tweet_id) REFERENCES tweet (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3D660A3B331E048A ON tweet (original_tweet_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE retweet (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, tweet_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_45E67DB31041E39B (tweet_id), INDEX IDX_45E67DB3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE retweet ADD CONSTRAINT FK_45E67DB31041E39B FOREIGN KEY (tweet_id) REFERENCES tweet (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE retweet ADD CONSTRAINT FK_45E67DB3A76ED395 FOREIGN KEY (user_id) REFERENCES account (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tweet DROP FOREIGN KEY FK_3D660A3B331E048A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3D660A3B331E048A ON tweet
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tweet DROP original_tweet_id
        SQL);
    }
}
