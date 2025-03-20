<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Agenda;
use App\Entity\AgendaSlot;
use App\Entity\AgendaSlotStatus;
use App\Entity\HealthSpecialist;
use App\Entity\MedicalAppointment;
use App\Service\AppointmentReferenceGenerator;
use App\Service\BookAppointment;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class BookAppointmentTest extends TestCase
{
    public function testBookAppointment(): void
    {
        $referenceGenerator = $this->createMock(AppointmentReferenceGenerator::class);

        $referenceGenerator
            ->expects(self::once())
            ->method('generateReferenceNumber')
            ->willReturn('ABC123');

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $doctor = $this->createStub(HealthSpecialist::class);
        $agenda = new Agenda($doctor);

        $availability = AgendaSlot::createOpen($agenda, '2025-03-20 10:00', '2025-03-20 10:30');

        $entityManager
            ->expects(self::exactly(2))
            ->method('persist')
            ->withConsecutive(
                [self::isInstanceOf(MedicalAppointment::class)],
                [$availability],
            );

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $sut = new BookAppointment($referenceGenerator, $entityManager);

        $appointment = $sut->bookAppointment($availability, 'John', 'Doe', 'jdoe@example.com', '1234567890');

        self::assertSame($doctor, $appointment->getPractitioner());
        self::assertSame('2025-03-20 10:00', $appointment->getOpeningAt()->format('Y-m-d H:i'));
        self::assertSame('2025-03-20 10:30', $appointment->getClosingAt()->format('Y-m-d H:i'));
        self::assertSame('ABC123', $appointment->getReferenceNumber());
        self::assertSame('John', $appointment->getFirstName());
        self::assertSame('Doe', $appointment->getLastName());
        self::assertSame('jdoe@example.com', $appointment->getEmail());
        self::assertSame('1234567890', $appointment->getPhone());

        self::assertSame(AgendaSlotStatus::BOOKED, $availability->getStatus());
    }
}

