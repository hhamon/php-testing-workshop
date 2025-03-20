<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DoctrineMedicalAppointmentRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Uuid;

use function Symfony\Component\String\u;

#[ORM\UniqueConstraint(
    name: 'medical_appointment_reference_number_unique',
    fields: ['referenceNumber'],
)]
#[ORM\Entity(repositoryClass: DoctrineMedicalAppointmentRepository::class)]
class MedicalAppointment
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    #[Groups(['medical_appointment:read'])]
    private string $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private HealthSpecialist $practitioner;

    #[ORM\Column]
    private DateTimeImmutable $openingAt;

    #[ORM\Column]
    private DateTimeImmutable $closingAt;

    #[ORM\Column(length: 30)]
    #[Groups(['medical_appointment:read'])]
    private string $referenceNumber;

    #[ORM\Column]
    private string $firstName;

    #[ORM\Column]
    private string $foldedFirstName;

    #[ORM\Column]
    private string $lastName;

    #[ORM\Column]
    private string $foldedLastName;

    #[ORM\Column(nullable: true)]
    #[Groups(['medical_appointment:read'])]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['medical_appointment:read'])]
    private ?string $phone = null;

    #[ORM\Column]
    #[Groups(['medical_appointment:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['medical_appointment:read'])]
    private ?DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cancellationReason = null;

    // TODO: add semantic constructor to build a medical appointment from a practitioner and availability objects
    // TODO: check that practitioner is available and availability is open

    public function __construct(
        HealthSpecialist $practitioner,
        DateTimeImmutable $openingAt,
        DateTimeImmutable $closingAt,
        string $referenceNumber,
        string $firstName,
        string $lastName,
    ) {
        $this->id = (string) Uuid::v4();
        $this->practitioner = $practitioner;
        $this->referenceNumber = $referenceNumber;
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->openingAt = $openingAt;
        $this->closingAt = $closingAt;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return Uuid::fromString($this->id);
    }

    public function getPractitioner(): HealthSpecialist
    {
        return $this->practitioner;
    }

    #[SerializedName('doctor')]
    #[Groups(['medical_appointment:read'])]
    public function getPractitionerName(): string
    {
        return $this->practitioner->getFullName();
    }

    #[Groups(['medical_appointment:read'])]
    public function getSpecialty(): MedicalSpecialty
    {
        return $this->practitioner->getSpecialty();
    }

    #[SerializedName('patient')]
    #[Groups(['medical_appointment:read'])]
    public function getFullName(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    #[Groups(['medical_appointment:read'])]
    public function getDate(): string
    {
        return $this->openingAt->format('Y-m-d');
    }

    #[Groups(['medical_appointment:read'])]
    public function getTime(): string
    {
        return $this->openingAt->format('H:i');
    }

    public function getOpeningAt(): DateTimeInterface
    {
        return $this->openingAt;
    }

    public function getClosingAt(): DateTimeInterface
    {
        return $this->closingAt;
    }

    public function getReferenceNumber(): string
    {
        return $this->referenceNumber;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
        $this->foldedFirstName = u($firstName)->folded()->lower()->toString();
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getFoldedFirstName(): string
    {
        return $this->foldedFirstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
        $this->foldedLastName = u($lastName)->folded()->lower()->toString();
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFoldedLastName(): string
    {
        return $this->foldedLastName;
    }

    public function setEmail(string $email): void
    {
        if ($email === '') {
            throw new InvalidArgumentException('The email cannot be empty.');
        }

        $email = \filter_var($email, filter: \FILTER_VALIDATE_EMAIL, options: \FILTER_NULL_ON_FAILURE);

        if ($email === null) {
            throw new InvalidArgumentException('The email is not valid.');
        }

        $this->email = u($email)->lower()->toString();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPhone(string $phone): void
    {
        if ($phone === '') {
            throw new InvalidArgumentException('The phone number cannot be empty.');
        }

        $this->phone = $phone;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getCancelledAt(): ?DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function cancel(?DateTimeImmutable $cancelledAt = null, string $reason = ''): void
    {
        // TODO: check appointment is not already cancelled
        $this->cancelledAt = $cancelledAt ?? new DateTimeImmutable();
        $this->cancellationReason = $reason !== '' ? $reason : null;
    }

    public function isCancelled(): bool
    {
        return $this->cancelledAt instanceof DateTimeInterface;
    }
}
