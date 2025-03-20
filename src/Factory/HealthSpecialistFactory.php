<?php

namespace App\Factory;

use App\Entity\HealthSpecialist;
use App\Entity\MedicalSpecialty;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<HealthSpecialist>
 */
final class HealthSpecialistFactory extends PersistentProxyObjectFactory
{
    private const array PROFILE_PICTURE_URLS = [
        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?fit=facearea&facepad=2&w=256&h=256&q=80',
        'https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?fit=facearea&facepad=2&w=256&h=256&q=80',
        'https://images.unsplash.com/photo-1517841905240-472988babdf9?fit=facearea&facepad=2&w=256&h=256&q=80',
        'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?fit=facearea&facepad=2&w=256&h=256&q=80',
        'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?fit=facearea&facepad=2&w=256&h=256&q=80',
        'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?fit=facearea&facepad=2&w=256&h=256&q=80',
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
        return HealthSpecialist::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'specialty' => self::faker()->randomElement(MedicalSpecialty::cases()),
            'introduction' => self::faker()->text(maxNbChars: 164),
            'biography' => self::faker()->paragraphs(self::faker()->numberBetween(2, 6), asText: true),
            'profilePictureUrl' => self::faker()->randomElement(self::PROFILE_PICTURE_URLS),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(HealthSpecialist $healthSpecialist): void {})
            ;
    }
}
