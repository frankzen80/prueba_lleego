<?php 

namespace App\PruebaTecnica\Infrastructure\Controller;

use App\PruebaTecnica\Application\FlightService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class FlightController extends AbstractController
{
    public function __construct(
        private readonly FlightService $flightService
    ) {}

    #[Route('/avail', name: 'api_flight_availability', methods: ['GET'])]
    public function getAvailability(Request $request): JsonResponse
    {
        $origin = $request->query->get('origin');
        $destination = $request->query->get('destination');
        $date = $request->query->get('date');

        if (!$origin || !$destination || !$date) {
            return new JsonResponse(
                ['error' => 'Campo/s obligatorio/s incompleto/s'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $segments = $this->flightService->getAvailableFlights($origin, $destination, $date);
            
            // Convertir los segmentos a array
            $response = array_map(function($segment) {
                return [
                    'originCode' => $segment->getOriginCode(),
                    'originName' => $segment->getOriginName(),
                    'destinationCode' => $segment->getDestinationCode(),
                    'destinationName' => $segment->getDestinationName(),
                    'start' => $segment->getStart() instanceof \DateTime ? $segment->getStart()->format('Y-m-d H:i:s') : $segment->getStart(),
                    'end' => $segment->getEnd() instanceof \DateTime ? $segment->getEnd()->format('Y-m-d H:i:s') : $segment->getEnd(),
                    'transportNumber' => $segment->getTransportNumber(),
                    'companyCode' => $segment->getCompanyCode(),
                    'companyName' => $segment->getCompanyName(),
                ];
            }, $segments);

            return new JsonResponse($response, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Se produjo un error: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}