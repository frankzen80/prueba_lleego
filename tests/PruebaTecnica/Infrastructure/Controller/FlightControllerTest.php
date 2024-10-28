<?php

namespace Tests\PruebaTecnica\Infrastructure\Controller;

use App\PruebaTecnica\Application\FlightService;
use App\PruebaTecnica\Domain\Segment;
use App\PruebaTecnica\Infrastructure\Controller\FlightController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FlightControllerTest extends TestCase
{
    private FlightController $controller;
    private FlightService $flightService;

    protected function setUp(): void
    {
        $this->flightService = $this->createMock(FlightService::class);
        $this->controller = new FlightController($this->flightService);
    }

    public function testGetAvailabilityWithMissingParameters(): void
    {
        $request = new Request();
        $response = $this->controller->getAvailability($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Campo/s obligatorio/s incompleto/s']),
            $response->getContent()
        );
    }

    public function testGetAvailabilityReturnsSuccess(): void
    {
        // Arrange
        $request = new Request([
            'origin' => 'PMI',
            'destination' => 'MAD',
            'date' => '2024-10-28'
        ]);

        $segment = new Segment();
        $segment->setOriginCode('PMI')
            ->setOriginName('Palma de Mallorca')
            ->setDestinationCode('MAD')
            ->setDestinationName('Madrid')
            ->setStart(new \DateTime('2024-10-28 10:00'))
            ->setEnd(new \DateTime('2024-10-28 11:30'))
            ->setTransportNumber('IB3975')
            ->setCompanyCode('IB')
            ->setCompanyName('Iberia');

        $this->flightService
            ->expects($this->once())
            ->method('getAvailableFlights')
            ->with('PMI', 'MAD', '2024-10-28')
            ->willReturn([$segment]);

   
        $response = $this->controller->getAvailability($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $expectedData = [[
            'originCode' => 'PMI',
            'originName' => 'Palma de Mallorca',
            'destinationCode' => 'MAD',
            'destinationName' => 'Madrid',
            'start' => '2024-10-28 10:00:00',
            'end' => '2024-10-28 11:30:00',
            'transportNumber' => 'IB3975',
            'companyCode' => 'IB',
            'companyName' => 'Iberia'
        ]];
        
        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedData),
            $response->getContent()
        );
    }
}
