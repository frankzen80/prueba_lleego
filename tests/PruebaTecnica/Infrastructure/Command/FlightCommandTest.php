<?php
    namespace Tests\PruebaTecnica\Infrastructure\Command;

    use App\PruebaTecnica\Application\FlightService;
    use App\PruebaTecnica\Domain\Segment;
    use App\PruebaTecnica\Infrastructure\Command\FlightCommand;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;

    class FlightCommandTest extends TestCase
    {
        private CommandTester $commandTester;
        private FlightService $flightService;

        protected function setUp(): void
        {
            $this->flightService = $this->createMock(FlightService::class);
            
            $application = new Application();
            $application->add(new FlightCommand($this->flightService));
            
            $command = $application->find('lleego:avail');
            $this->commandTester = new CommandTester($command);
        }

        public function testExecuteSuccess(): void
        {
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
                ->willReturn([$segment]);

            $this->commandTester->execute([
                'origin' => 'PMI',
                'destination' => 'MAD',
                'date' => '2024-10-28'
            ]);

            $this->assertEquals(0, $this->commandTester->getStatusCode());
            $display = $this->commandTester->getDisplay();
            $this->assertStringContainsString('PMI', $display);
            $this->assertStringContainsString('MAD', $display);
            $this->assertStringContainsString('IB3975', $display);
        }

        public function testExecuteFailure(): void
        {
            $this->flightService
                ->expects($this->once())
                ->method('getAvailableFlights')
                ->willThrowException(new \Exception('Error al buscar vuelos'));

            $this->commandTester->execute([
                'origin' => 'PMI',
                'destination' => 'MAD',
                'date' => '2024-10-28'
            ]);

            $this->assertEquals(1, $this->commandTester->getStatusCode());
            $this->assertStringContainsString('Error al buscar vuelos', $this->commandTester->getDisplay());
        }
    }