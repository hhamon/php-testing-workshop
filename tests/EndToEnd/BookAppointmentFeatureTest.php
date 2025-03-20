<?php

declare(strict_types=1);

namespace App\Tests\EndToEnd;

use App\Entity\MedicalSpecialty;
use App\Factory\AgendaFactory;
use App\Factory\HealthSpecialistFactory;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use DateTimeImmutable;
use Symfony\Component\Panther\PantherTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class BookAppointmentFeatureTest extends PantherTestCase
{
    use Factories;
    use HasBrowser;
    use ResetDatabase;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        StaticDriver::setKeepStaticConnections(false);
    }

    public static function tearDownAfterClass(): void
    {
        StaticDriver::setKeepStaticConnections(true);

        parent::tearDownAfterClass();
    }

    public function testBookADentistAppointment(): void
    {
        $specialist = HealthSpecialistFactory::createOne([
            'firstName' => 'Kevin',
            'lastName' => 'Costner',
            'specialty' => MedicalSpecialty::DENTIST,
        ]);

        AgendaFactory::new(['owner' => $specialist])
            ->published()
            ->withCalendar('today', 'tomorrow')
            ->create();

        $tomorrow = new DateTimeImmutable('tomorrow');

        $availabilityId = \sprintf('#availability--%s--1800', $tomorrow->format('Ymd'));

        $this->pantherBrowser()
            ->visit('/')
            ->click('Kevin Costner')
            ->assertSee('Check another date?')
            ->fillField('date', $tomorrow->format('Y-m-d'))
            ->click('Search')
            ->waitUntilSeeIn($availabilityId, '18:00')
            ->click($availabilityId)
            ->fillField('medical_appointment_booking[firstName]', 'Lauren')
            ->fillField('medical_appointment_booking[lastName]', 'Montgomery')
            ->fillField('medical_appointment_booking[email]', 'l.montgomery@example.com')
            ->fillField('medical_appointment_booking[phone]', '+123456789')
            ->click('Book my appointment')
            ->assertSee(\sprintf('Your dentist appointment is scheduled on %s between 18:00 and 18:30.', $tomorrow->format('m/d/Y')))
            ->takeScreenshot('appointment-confirmation.png');
    }
}
