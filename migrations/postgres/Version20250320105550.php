<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250320105550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make fields nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE medical_appointment ALTER email TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE medical_appointment ALTER email DROP NOT NULL');
        $this->addSql('ALTER TABLE medical_appointment ALTER phone TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE medical_appointment ALTER phone DROP NOT NULL');
        $this->addSql('ALTER TABLE medical_appointment ALTER cancelled_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE medical_appointment ALTER cancelled_at DROP NOT NULL');
        $this->addSql('ALTER TABLE medical_appointment ALTER cancellation_reason TYPE TEXT');
        $this->addSql('ALTER TABLE medical_appointment ALTER cancellation_reason DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE medical_appointment ALTER email TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE medical_appointment ALTER email SET NOT NULL');
        $this->addSql('ALTER TABLE medical_appointment ALTER phone TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE medical_appointment ALTER phone SET NOT NULL');
        $this->addSql('ALTER TABLE medical_appointment ALTER cancelled_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE medical_appointment ALTER cancelled_at SET NOT NULL');
        $this->addSql('ALTER TABLE medical_appointment ALTER cancellation_reason TYPE TEXT');
        $this->addSql('ALTER TABLE medical_appointment ALTER cancellation_reason SET NOT NULL');
    }
}
