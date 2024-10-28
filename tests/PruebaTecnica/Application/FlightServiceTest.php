<?php

namespace Tests\PruebaTecnica\Application;

use App\PruebaTecnica\Application\FlightService;
use App\PruebaTecnica\Domain\FlightAvailabilityPort;
use App\PruebaTecnica\Domain\Segment;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class FlightServiceTest extends TestCase
{
    private FlightService $flightService;
    private MockObject $flightProvider;

    protected function setUp(): void
    {
        $this->flightProvider = $this->createMock(FlightAvailabilityPort::class);
        $this->flightService = new FlightService($this->flightProvider);
    }

    public function testGetAvailableFlightsReturnsExpectedSegments(): void
    {
        // Arrange
        $origin = 'PMI';
        $destination = 'MAD';
        $date = '2024-10-28';

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

        $expectedSegments = [$segment];

        // Configure mock
        $this->flightProvider
            ->expects($this->once())
            ->method('search')
            ->with($origin, $destination, $date)
            ->willReturn($expectedSegments);

        // Act
        $result = $this->flightService->getAvailableFlights($origin, $destination, $date);

        // Assert
        $this->assertEquals($expectedSegments, $result);
    }
}
