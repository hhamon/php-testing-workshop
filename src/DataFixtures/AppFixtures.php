<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MedicalSpecialty;
use App\Factory\AgendaFactory;
use App\Factory\HealthSpecialistFactory;
use App\Factory\MedicalAppointmentFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $specialist1 = HealthSpecialistFactory::createOne([
            'firstName' => 'Linda',
            'lastName' => 'Helmann',
            'specialty' => MedicalSpecialty::GYNECOLOGIST,
        ]);

        $specialist2 = HealthSpecialistFactory::createOne([
            'firstName' => 'James',
            'lastName' => 'Clark',
            'specialty' => MedicalSpecialty::OPHTHALMOLOGIST,
        ]);

        $specialist3 = HealthSpecialistFactory::createOne([
            'firstName' => 'Vincent',
            'lastName' => 'Dries',
            'specialty' => MedicalSpecialty::GYNECOLOGIST,
        ]);

        $specialist4 = HealthSpecialistFactory::createOne([
            'firstName' => 'Cassidy',
            'lastName' => 'McBeal',
            'specialty' => MedicalSpecialty::DERMATOLOGIST,
        ]);

        $specialist5 = HealthSpecialistFactory::createOne([
            'firstName' => 'Courtney',
            'lastName' => 'Henry',
            'specialty' => MedicalSpecialty::ANESTHETIST,
        ]);

        $specialist6 = HealthSpecialistFactory::createOne([
            'firstName' => 'Tom',
            'lastName' => 'Cook',
            'specialty' => MedicalSpecialty::ANESTHETIST,
        ]);

        //HealthSpecialistFactory::createMany(120);

        AgendaFactory::new(['owner' => $specialist1])
            ->published()
            ->withCalendar('-2 days', '+65 days', saturday: false, sunday: false)
            ->create();

        AgendaFactory::new(['owner' => $specialist2])
            ->published()
            ->withCalendar('-2 days', '+10 days', wednesday: false, thursday: false)
            ->create();

        AgendaFactory::new(['owner' => $specialist3])->unpublished()->create();
        AgendaFactory::new(['owner' => $specialist4])->unpublished()->create();
        AgendaFactory::new(['owner' => $specialist5])->published()->create();
        AgendaFactory::new(['owner' => $specialist6])->published()->create();

        MedicalAppointmentFactory::new()
            ->cancelled()
            ->many(3)
            ->create();

        MedicalAppointmentFactory::createMany(10);
    }
}
