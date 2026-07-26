<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260726030602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN activity.goal_amount IS \'\'');
        $this->addSql('COMMENT ON COLUMN activity.raised_amount IS \'\'');
        $this->addSql('COMMENT ON COLUMN transaction.amount IS \'\'');
        $this->addSql('COMMENT ON COLUMN transaction.created_at IS \'\'');
        $this->addSql('ALTER INDEX idx_4a29754481c06096 RENAME TO IDX_723705D181C06096');
        $this->addSql('ALTER INDEX idx_4a297544b03a8386 RENAME TO IDX_723705D1B03A8386');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA pgbouncer');
        $this->addSql('CREATE SCHEMA realtime');
        $this->addSql('CREATE SCHEMA extensions');
        $this->addSql('CREATE SCHEMA vault');
        $this->addSql('CREATE SCHEMA graphql_public');
        $this->addSql('CREATE SCHEMA graphql');
        $this->addSql('CREATE SCHEMA auth');
        $this->addSql('CREATE SCHEMA storage');
        $this->addSql('COMMENT ON COLUMN "activity".goal_amount IS \'(DC2Type:decimal)\'');
        $this->addSql('COMMENT ON COLUMN "activity".raised_amount IS \'(DC2Type:decimal)\'');
        $this->addSql('COMMENT ON COLUMN "transaction".amount IS \'(DC2Type:decimal)\'');
        $this->addSql('COMMENT ON COLUMN "transaction".created_at IS \'(DC2Type:datetime)\'');
        $this->addSql('ALTER INDEX idx_723705d1b03a8386 RENAME TO idx_4a297544b03a8386');
        $this->addSql('ALTER INDEX idx_723705d181c06096 RENAME TO idx_4a29754481c06096');
    }
}
