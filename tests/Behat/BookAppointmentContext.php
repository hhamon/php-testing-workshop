<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\AgendaSlotStatus;
use App\Entity\HealthSpecialist;
use App\Entity\MedicalSpecialty;
use App\Factory\AgendaFactory;
use App\Factory\AgendaSlotFactory;
use App\Factory\HealthSpecialistFactory;
use App\Factory\MedicalAppointmentFactory;
use Assert\Assertion;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Behat\Transformation\Transform;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\Proxy;

final class BookAppointmentContext extends AcceptanceTestCase implements Context
{
    /**
     * @var array<string, Proxy<HealthSpecialist>>
     */
    private array $specialists = [];

    /**
     * @return Proxy<HealthSpecialist>
     */
    #[Transform(':doctor')]
    public function transformDoctor(string $doctorName): Proxy
    {
        $doctor = $this->specialists[$doctorName] ?? null;

        Assertion::isInstanceOf($doctor, Proxy::class, \sprintf('Doctor "%s" not found.', $doctorName));

        return $doctor;
    }

    #[Given(':user is a :specialist')]
    public function isA(string $user, string $specialist): void
    {
        [$firstName, $lastName] = explode(' ', $user);

        $practitioner = HealthSpecialistFactory::createOne([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'specialty' => MedicalSpecialty::from($specialist),
        ]);

        AgendaFactory::createOne(['owner' => $practitioner]);

        $this->specialists[$user] = $practitioner;
    }

    /**
     * @param Proxy<HealthSpecialist> $doctor
     */
    #[Given(':doctor is available on :date at :time')]
    public function isAvailableOnAt(Proxy $doctor, string $date, string $time): void
    {
        AgendaSlotFactory::new()
            ->specialist($doctor)
            ->availableOn($date, $time, '30 minutes')
            ->create();
    }

    /**
     * @param Proxy<HealthSpecialist> $doctor
     */
    #[When('I book an appointment with :doctor on :date at :time')]
    public function iBookAnAppointmentWithOnAt(Proxy $doctor, string $date, string $time): void
    {
        $appointmentAt = new DateTimeImmutable($date . ' ' . $time);

        $this->browser()
            ->visit('/')
            ->click($doctor->getFullName())
            ->assertSee('Check another date?')
            ->fillField('date', $appointmentAt->format('Y-m-d'))
            ->click('Search')
            ->assertSee($time)
            ->click($time)
            ->fillField('medical_appointment_booking[firstName]', 'Lauren')
            ->fillField('medical_appointment_booking[lastName]', 'Montgomery')
            ->fillField('medical_appointment_booking[email]', 'l.montgomery@example.com')
            ->fillField('medical_appointment_booking[phone]', '+123456789')
            ->click('Book my appointment')
            ->assertSee(\sprintf(
                'Your %s appointment is scheduled on %s between %s and %s.',
                $doctor->getSpecialty()->value,
                $appointmentAt->format('m/d/Y'),
                $time,
                $appointmentAt->modify('+30 minutes')->format('H:i'),
            ));
    }

    /**
     * @param Proxy<HealthSpecialist> $doctor
     */
    #[Then(':doctor should have an appointment on :date at :time')]
    public function shouldHaveAnAppointmentOnAt(Proxy $doctor, string $date, string $time): void
    {
        MedicalAppointmentFactory::assert()->count(1, [
            'practitioner' => $doctor,
            'openingAt' => new DateTimeImmutable($date . ' ' . $time),
        ]);
    }

    /**
     * @param Proxy<HealthSpecialist> $doctor
     */
    #[Then(':doctor should no longer be available for new appointments on :date at :time')]
    public function shouldNoLongerBeAvailableForNewAppointmentsOnAt(Proxy $doctor, string $date, string $time): void
    {
        $agenda = AgendaFactory::find(['owner' => $doctor]);

        $openingAt = new DateTimeImmutable($date . ' ' . $time);

        AgendaSlotFactory::assert()->count(1, [
            'agenda' => $agenda,
            'openingAt' => $openingAt,
            'status' => AgendaSlotStatus::BOOKED,
        ]);

        AgendaSlotFactory::assert()->count(0, [
            'agenda' => $agenda,
            'openingAt' => $openingAt,
            'status' => AgendaSlotStatus::OPEN,
        ]);
    }
}
