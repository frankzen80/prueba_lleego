<?php

    namespace App\PruebaTecnica\Infrastructure\Command;

    use App\PruebaTecnica\Application\FlightService;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;

    #[AsCommand(name: 'lleego:avail')]
    class FlightCommand extends Command
    {
        public function __construct(
            private readonly FlightService $flightService
        ) {
            parent::__construct();
        }

        protected function configure(): void
        {
            $this
                ->addArgument('origin', InputArgument::REQUIRED)
                ->addArgument('destination', InputArgument::REQUIRED)
                ->addArgument('date', InputArgument::REQUIRED);
        }

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $io = new SymfonyStyle($input, $output);

            try {
                $segments = $this->flightService->getAvailableFlights(
                    $input->getArgument('origin'),
                    $input->getArgument('destination'),
                    $input->getArgument('date')
                );

                $rows = array_map(fn($segment) => [
                    $segment->getOriginCode(),
                    $segment->getOriginName(),
                    $segment->getDestinationCode(),
                    $segment->getDestinationName(),
                    $segment->getStart()->format('Y-m-d H:i'),
                    $segment->getEnd()->format('Y-m-d H:i'),
                    $segment->getTransportNumber(),
                    $segment->getCompanyCode(),
                    $segment->getCompanyName()
                ], $segments);

                $io->table(
                    ['Origin Code', 'Origin Name', 'Destination Code', 'Destination Name', 'Start', 'End', 'Transport Number', 'Company Code', 'Company Name'],
                    $rows
                );

                return Command::SUCCESS;
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                return Command::FAILURE;
            }
        }
    }