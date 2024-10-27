<?php

    namespace App\PruebaTecnica\Infrastructure;

    use App\PruebaTecnica\Domain\FlightAvailabilityPort;
    use App\PruebaTecnica\Domain\Segment;
    use Symfony\Contracts\HttpClient\HttpClientInterface;
    use SimpleXMLElement;

    class FlightProvider implements FlightAvailabilityPort
    {
        private const API_URL = 'https://testapi.lleego.com/prueba-tecnica/availability-price';

        public function __construct(
            private readonly HttpClientInterface $httpClient
        ) {}

        public function search(string $origin, string $destination, string $date): array
        {
            $response = $this->httpClient->request('GET', self::API_URL, [
                'query' => [
                    'origin' => $origin,
                    'destination' => $destination,
                    'date' => $date
                ]
            ]);

            $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response->getContent());
            $xml = new SimpleXMLElement($response);

            return $this->parseXmlResponse($xml);
        }

        private function parseXmlResponse(\SimpleXMLElement $xml): array
        {
            $segments = [];

            //dd($xml->AirlineOffers); exit;

            foreach ($xml->soapBody->AirShoppingRS->DataLists->FlightSegmentList->FlightSegment as $flightXml) {
                $segment = new Segment();

                $segment
                    ->setOriginCode((string)$flightXml->Departure->AirportCode)
                    ->setOriginName((string)$flightXml->Departure->AirportName)
                    ->setDestinationCode((string)$flightXml->Arrival->AirportCode)
                    ->setDestinationName((string)$flightXml->Arrival->AirportName)
                    ->setStart(new \DateTime((string)$flightXml->Departure->Date.' '.(string)$flightXml->Departure->Time))
                    ->setEnd((new \DateTime((string)$flightXml->Arrival->Date.' '.(string)$flightXml->Arrival->Time)))
                    ->setTransportNumber((string)$flightXml->MarketingCarrier->FlightNumber)
                    ->setCompanyCode((string)$flightXml->OperatingCarrier->AirlineID)
                    ->setCompanyName((string)$flightXml->OperatingCarrier->Name);
                
                $segments[] = $segment; 
            }

            return $segments;
        }
    }