<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Agenda;
use App\Entity\AgendaSlot;
use App\Entity\AgendaSlotStatus;
use App\Entity\HealthSpecialist;
use App\Entity\MedicalSpecialty;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class AgendaSlotTest extends TestCase
{
    private Agenda $agenda;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agenda = $this->createDummyAgenda();
    }

    public function testCreateOpenAgendaSlot(): void
    {
        $agenda = $this->createStub(Agenda::class);
        $agenda->method('getId')->willReturn(Uuid::fromString('e39ad697-c554-421f-b381-86945500c123'));

        // Arrange
        $sut = AgendaSlot::createOpen($agenda, '2025-03-20 09:00', '2025-03-20 09:30');

        // Act & Assert
        self::assertSame($agenda, $sut->getAgenda());
        self::assertSame(AgendaSlotStatus::OPEN, $sut->getStatus());

        self::assertSame('2025-03-20T09:00:00+00:00', $sut->getOpeningAt()->format('c'));
        self::assertSame('2025-03-20T09:30:00+00:00', $sut->getClosingAt()->format('c'));

        self::assertEquals(new DateTimeImmutable('2025-03-20 09:00'), $sut->getOpeningAt());
        self::assertEquals(new DateTimeImmutable('2025-03-20 09:30'), $sut->getClosingAt());
    }

    public function testCreateAgendaSlotWithSwitchedOpeningAndClosingTimes(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Opening time must be before closing time');

        // Act
        AgendaSlot::createOpen($this->agenda, '2025-03-20 09:30', '2025-03-20 09:00');
    }

    private function createDummyAgenda(): Agenda
    {
        return new Agenda(
            new HealthSpecialist('John', 'Doe', MedicalSpecialty::CARDIOLOGIST),
        );
    }
}