<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AgendaSlot;
use App\Entity\AgendaSlotStatus;
use App\Form\MedicalAppointmentBookingType;
use App\Model\MedicalAppointmentBooking;
use App\Repository\DoctrineAgendaSlotRepository;
use App\Service\BookAppointment;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class BookAppointmentController extends AbstractController
{
    public function __construct(
        private readonly DoctrineAgendaSlotRepository $agendaSlotRepository,
        private readonly BookAppointment $bookAppointment,
    ) {
    }

    #[Route(
        path: '/appointments',
        name: 'app_appointment_book',
        defaults: ['section' => 'health_specialist'],
        methods: ['GET', 'POST'],
    )]
    public function __invoke(
        Request $request,
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] string $availabilityId,
    ): Response {
        $availability = $this->agendaSlotRepository->find($availabilityId);

        if (!$availability instanceof AgendaSlot) {
            throw $this->createNotFoundException(\sprintf('Availability %s not found.', $availabilityId));
        }

        if ($availability->getStatus() !== AgendaSlotStatus::OPEN) {
            throw $this->createNotFoundException(\sprintf('Availability %s not bookable.', $availabilityId));
        }

        // User should not be able to book an appointment less than 30 minutes before the opening time
        $minBookingDateTime = $availability->getOpeningAt()->modify('-30 minutes');
        if (new DateTimeImmutable() >= $minBookingDateTime) {
            throw $this->createNotFoundException(\sprintf('Availability %s can no longer be booked.', $availabilityId));
        }

        $dto = new MedicalAppointmentBooking();

        $form = $this
            ->createForm(MedicalAppointmentBookingType::class, $dto)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment = $this->bookAppointment->bookAppointment(
                $availability,
                $dto->getFirstName(),
                $dto->getLastName(),
                $dto->getEmail(),
                $dto->getPhone(),
            );

            return $this->redirectToRoute('app_appointment_show', [
                'id' => (string) $appointment->getId(),
            ]);
        }

        return $this->render('appointment/book.html.twig', [
            'availability' => $availability,
            'specialist' => $availability->getPractitioner(),
            'form' => $form->createView(),
        ]);
    }
}
