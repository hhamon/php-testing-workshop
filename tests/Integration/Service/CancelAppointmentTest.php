<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Factory\MedicalAppointmentFactory;
use App\Service\CancelAppointment;
use DateTimeInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Mailer\Test\InteractsWithMailer;
use Zenstruck\Mailer\Test\TestEmail;

final class CancelAppointmentTest extends KernelTestCase
{
    use Factories;
    use InteractsWithMailer;
    use ResetDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    public function testScheduledUpcomingAppointmentCanBeCancelled(): void
    {
        // Arrange: seed the database with a scheduled upcoming appointment
        $appointment = MedicalAppointmentFactory::new()
            ->tomorrowAt('10:00')
            ->forPatient('John', 'Smith')
            ->create(['email' => 'user@example.com']);

        // Act: cancel the appointment
        $this->getCancelAppointmentService()->cancel($appointment->_real(), 'I found one earlier.');

        // Assert: check the appointment is cancelled in the database
        MedicalAppointmentFactory::assert()->count(1, [
            'id' => $appointment->getId(),
            'cancellationReason' => 'I found one earlier.',
        ]);

        self::assertTrue($appointment->isCancelled());
        self::assertInstanceOf(DateTimeInterface::class, $appointment->getCancelledAt());
        self::assertSame('I found one earlier.', $appointment->getCancellationReason());

        // Assert: check the confirmation email has been sent
        $this->mailer()
            ->assertSentEmailCount(1)
            ->assertEmailSentTo('user@example.com', 'Your appointment has been cancelled');
    }

    private function getCancelAppointmentService(): CancelAppointment
    {
        return self::getContainer()->get(CancelAppointment::class);
    }
}