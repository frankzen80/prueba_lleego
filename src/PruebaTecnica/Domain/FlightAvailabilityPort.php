<?php

    namespace App\PruebaTecnica\Domain;

    interface FlightAvailabilityPort
    {
        /** @return Segment[] */
        public function search(string $origin, string $destination, string $date): array;
    }