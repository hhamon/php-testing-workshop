<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Agenda;
use App\Service\GenerateAgendaCalendar;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Agenda>
 */
final class AgendaFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Agenda::class;
    }

    public function __construct(
        private readonly GenerateAgendaCalendar $generateAgendaCalendar,
    ) {
    }

    public function withCalendar(
        string $firstDay,
        string $lastDay,
        bool $monday = true,
        bool $tuesday = true,
        bool $wednesday = true,
        bool $thursday = true,
        bool $friday = true,
        bool $saturday = true,
        bool $sunday = true,
    ): self {
        return $this->with(static fn (): array => [
            'calendar' => [
                'firstDay' => $firstDay,
                'lastDay' => $lastDay,
                'schedule' => [
                    'monday' => $monday,
                    'tuesday' => $tuesday,
                    'wednesday' => $wednesday,
                    'thursday' => $thursday,
                    'friday' => $friday,
                    'saturday' => $saturday,
                    'sunday' => $sunday,
                ],
            ],
        ]);
    }

    public function published(): self
    {
        return $this->with(static fn (): array => ['isPublished' => true]);
    }

    public function unpublished(): self
    {
        return $this->with(static fn (): array => ['isPublished' => false]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return static fn (): array => [
            'owner' => HealthSpecialistFactory::randomOrCreate(),
            'isPublished' => true,
            'calendar' => null,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Agenda $object, array $parameters, self $factory): void {
                if ($parameters['isPublished']) {
                    $object->publish();
                } else {
                    $object->unpublish();
                }
            })
            ->afterPersist(function (Agenda $object, array $parameters, self $factory): void {
                $calendar = $parameters['calendar'] ?? null;

                if (!\is_array($calendar)) {
                    return;
                }

                $this->generateAgendaCalendar->generateCalendarFor(
                    $object,
                    $calendar['firstDay'],
                    $calendar['lastDay'],
                    $calendar['schedule']['monday'],
                    $calendar['schedule']['tuesday'],
                    $calendar['schedule']['wednesday'],
                    $calendar['schedule']['thursday'],
                    $calendar['schedule']['friday'],
                    $calendar['schedule']['saturday'],
                    $calendar['schedule']['sunday'],
                );

                /*
                AgendaSlotFactory::new(['agenda' => $object])
                    ->sequence([
                        ['openingAt' => '2025-02-25 10:00', 'closingAt' => '2025-02-25 10:30'],
                        ['openingAt' => '2025-02-25 10:30', 'closingAt' => '2025-02-25 11:00'],
                    ])
                    ->create();
                */

            })
            ;
    }
}
