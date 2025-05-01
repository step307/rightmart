<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250428142936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CREATE TABLE IF NOT EXISTS http_log';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS http_log (
                id CHAR(36) primary key,
                serviceName VARCHAR(255) NULL,
                dateTime datetime NULL,
                request TEXT NULL,
                httpStatusCode ENUM('100','101','102','103','200','201','202','203','204','205','206','207','208','226','300','301','302','303','304','305','307','308','400','401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','416','417','418','421','422','423','424','425','426','428','429','431','451','500','501','502','503','504','505','506','507','508','510','511') NULL,
                logLine TEXT NOT NULL,
                INDEX idx_dateTime (dateTime),
                INDEX idx_serviceName (serviceName),
                INDEX idx_httpStatusCode (httpStatusCode)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(Schema $schema): void
    {
    }
}
