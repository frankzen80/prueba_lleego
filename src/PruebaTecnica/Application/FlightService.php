<?php 

    namespace App\PruebaTecnica\Application;

    use App\PruebaTecnica\Domain\FlightAvailabilityPort;

    class FlightService
    {
        public function __construct(
            private readonly FlightAvailabilityPort $flightProvider
        ) {}

        public function getAvailableFlights(string $origin, string $destination, string $date): array
        {
            return $this->flightProvider->search($origin, $destination, $date);
        }
    }
