<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240215093952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disponibility DROP FOREIGN KEY FK_38BB9260B897366B');
        $this->addSql('DROP INDEX UNIQ_38BB9260B897366B ON disponibility');
        $this->addSql('ALTER TABLE disponibility DROP date_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disponibility ADD date_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE disponibility ADD CONSTRAINT FK_38BB9260B897366B FOREIGN KEY (date_id) REFERENCES reservation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_38BB9260B897366B ON disponibility (date_id)');
    }
}
