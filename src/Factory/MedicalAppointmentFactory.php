<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\MedicalAppointment;
use DateTime;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<MedicalAppointment>
 */
final class MedicalAppointmentFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return MedicalAppointment::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public function cancelled(DateTimeImmutable|string|null $cancelledAt = null, ?string $cancellationReason = null): self
    {
        return $this->with(static function () use ($cancelledAt, $cancellationReason): array {
            if ($cancelledAt === null) {
                $cancelledAt = DateTimeImmutable::createFromMutable(
                    self::faker()->dateTimeBetween('-15 days', 'now'),
                );
            }

            return [
                'cancelledAt' => $cancelledAt,
                'cancellationReason' => $cancellationReason ?? self::faker()->realText(390),
            ];
        });
    }

    public function tomorrowAt(string $openingTime, ?string $closingTime = null): self
    {
        return $this->with(static function () use ($openingTime, $closingTime): array {
            $tomorrow = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');

            $openingAt = new DateTimeImmutable($tomorrow . ' ' . $openingTime);

            $closingAt = $closingTime === null
                ? $openingAt->modify('+30 minutes')
                : new DateTimeImmutable($tomorrow . ' ' . $closingTime);

            return [
                'openingAt' => $openingAt,
                'closingAt' => $closingAt,
            ];
        });
    }

    public function yesterdayAt(string $openingTime, ?string $closingTime = null): self
    {
        return $this->with(static function () use ($openingTime, $closingTime): array {
            $yesterday = (new DateTimeImmutable('yesterday'))->format('Y-m-d');

            $openingAt = new DateTimeImmutable($yesterday . ' ' . $openingTime);

            $closingAt = $closingTime === null
                ? $openingAt->modify('+30 minutes')
                : new DateTimeImmutable($yesterday . ' ' . $closingTime);

            return [
                'openingAt' => $openingAt,
                'closingAt' => $closingAt,
            ];
        });
    }

    private function scheduledOn(string $date, string $openingTime, string $closingTime): self
    {
        return $this->with(static function () use ($date, $openingTime, $closingTime): array {
            $date = (new DateTimeImmutable($date))->format('Y-m-d');

            return [
                'openingAt' => new DateTimeImmutable($date . ' ' . $openingTime),
                'closingAt' => new DateTimeImmutable($date . ' ' . $closingTime),
            ];
        });
    }

    public function forPatient(string $firstName, string $lastName): self
    {
        return $this->with([
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);
    }

    public function scheduled(string $referenceNumber): self
    {
        return $this->with(['referenceNumber' => $referenceNumber]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return function (): array {
            $openingAt = $this->generateOpeningDateTime();
            $closingAt = $openingAt->modify('+30 minutes');

            return [
                'referenceNumber' => self::faker()->unique()->regexify('/^[A-Z0-9]{6}/'),
                'practitioner' => HealthSpecialistFactory::randomOrCreate(),
                'firstName' => self::faker()->firstName(),
                'lastName' => self::faker()->lastName(),
                'email' => self::faker()->email(),
                'phone' => self::faker()->e164PhoneNumber(),
                'openingAt' => DateTimeImmutable::createFromMutable($openingAt),
                'closingAt' => DateTimeImmutable::createFromMutable($closingAt),
            ];
        };
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->beforeInstantiate(function(array $parameters, string $class, self $factory): array {
                $openingAt = $parameters['openingAt'] ?? null;

                if (\is_string($openingAt)) {
                    $parameters['openingAt'] = new DateTimeImmutable($openingAt);
                }

                $closingAt = $parameters['closingAt'] ?? null;

                if (\is_string($closingAt)) {
                    $parameters['closingAt'] = new DateTimeImmutable($closingAt);
                }

                return $parameters;
            })
            ->beforeInstantiate(function(array $parameters, string $class, self $factory): array {
                $cancelledAt = $parameters['cancelledAt'] ?? null;

                if (\is_string($cancelledAt)) {
                    $parameters['cancelledAt'] = new DateTimeImmutable($cancelledAt);
                }

                return $parameters;
            })
            ->afterInstantiate(function (MedicalAppointment $medicalAppointment, array $parameters, self $factory): void {
                $cancelledAt = $parameters['cancelledAt'] ?? null;

                if ($cancelledAt instanceof DateTimeImmutable) {
                    $medicalAppointment->cancel($cancelledAt, $parameters['cancellationReason'] ?? null);
                }
            })
            ;
    }

    private function generateOpeningDateTime(): DateTime
    {
        $hour = self::faker()->numberBetween(8, 18);
        $minutes = self::faker()->randomElement([0, 30]);

        return self::faker()
            ->dateTimeBetween('-15 days', '+60 days')
            ->setTime($hour, $minutes);
    }
}
