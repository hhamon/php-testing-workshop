<?php

namespace App\Factory;

use App\Entity\AgendaSlot;
use App\Entity\AgendaSlotStatus;
use App\Entity\HealthSpecialist;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;

/**
 * @extends PersistentProxyObjectFactory<AgendaSlot>
 */
final class AgendaSlotFactory extends PersistentProxyObjectFactory
{
    /**
     * @var list<array{0: string, 1: string}>
     */
    public const array DAILY_AGENDA_SLOTS = [
        ['08:30', '09:00'],
        ['09:00', '09:30'],
        ['09:30', '10:00'],
        ['10:00', '10:30'],
        ['10:30', '11:00'],
        ['11:00', '11:30'],
        ['11:30', '12:00'],
        ['12:00', '12:30'],
        ['12:30', '13:00'],
        ['14:00', '14:30'],
        ['14:30', '15:00'],
        ['15:00', '15:30'],
        ['15:30', '16:00'],
        ['16:00', '16:30'],
        ['16:30', '17:00'],
        ['17:00', '17:30'],
        ['17:30', '18:00'],
        ['18:00', '18:30'],
        ['18:30', '19:00'],
        ['19:00', '19:30'],
    ];

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return AgendaSlot::class;
    }

    /**
     * @param Proxy<HealthSpecialist> $specialist
     */
    public function specialist(Proxy $specialist): self
    {
        return $this->with(static fn (): array => [
            'agenda' => AgendaFactory::find(['owner' => $specialist]),
        ]);
    }

    public function availableOn(string $date, string $time, string $duration): self
    {
        return $this->with(static function () use ($date, $time, $duration): array {
            $openingAt = new DateTimeImmutable($date . ' ' . $time);

            return [
                'openingAt' => $openingAt,
                'closingAt' => $openingAt->modify('+' . $duration),
                'status' => AgendaSlotStatus::OPEN,
            ];
        });
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return function (): array {
            $date = self::faker()->dateTime()->format('Y-m-d');
            $slot = self::faker()->randomElement(self::DAILY_AGENDA_SLOTS);

            return [
                'agenda' => AgendaFactory::randomOrCreate(),
                'openingAt' => $date . ' ' . $slot[0],
                'closingAt' => $date . ' ' . $slot[1],
                'status' => AgendaSlotStatus::OPEN,
            ];
        };
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->beforeInstantiate(function (array $parameters, string $class, self $factory): array {
                if (\is_string($parameters['openingAt'] ?? null)) {
                    $parameters['openingAt'] = new DateTimeImmutable($parameters['openingAt']);
                }

                if (\is_string($parameters['closingAt'] ?? null)) {
                    $parameters['closingAt'] = new DateTimeImmutable($parameters['closingAt']);
                }

                return $parameters;
            })
            // ->afterInstantiate(function(AgendaSlot $agendaSlot): void {})
            ;
    }
}
