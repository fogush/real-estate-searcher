<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191124180632 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE real_estate (id INT AUTO_INCREMENT NOT NULL, link VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, price_dollars INT UNSIGNED NOT NULL, number_of_rooms SMALLINT UNSIGNED NOT NULL, address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, floor SMALLINT UNSIGNED NOT NULL, floors_total SMALLINT UNSIGNED NOT NULL, area_total_cm INT UNSIGNED NOT NULL, area_living_cm INT UNSIGNED DEFAULT NULL, area_kitchen_cm INT UNSIGNED DEFAULT NULL, year_construction SMALLINT UNSIGNED DEFAULT NULL, year_repair SMALLINT UNSIGNED DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE real_estate');
    }
}
