<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AgendaSlot;
use App\Entity\MedicalAppointment;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class BookAppointment
{
    public function __construct(
        private readonly AppointmentReferenceGenerator $appointmentReferenceGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function bookAppointment(
        AgendaSlot $availability,
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
    ): MedicalAppointment {
        $appointment = new MedicalAppointment(
            $availability->getPractitioner(),
            DateTimeImmutable::createFromInterface($availability->getOpeningAt()),
            DateTimeImmutable::createFromInterface($availability->getClosingAt()),
            $this->appointmentReferenceGenerator->generateReferenceNumber(),
            $firstName,
            $lastName,
        );

        $appointment->setEmail($email);
        $appointment->setPhone($phone);

        $availability->book($appointment->getCreatedAt());

        $this->entityManager->persist($appointment);
        $this->entityManager->persist($availability);
        $this->entityManager->flush();

        return $appointment;
    }
}