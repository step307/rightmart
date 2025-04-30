<?php

namespace App\Command;

use App\Service\LogFileImporterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:log:import-file',
    description: 'Imports a logfile into database',
)]
class ImportHttpLogFileCommand extends Command
{
    public function __construct(
        private readonly LogFileImporterInterface $logFileImporter,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to log file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filePath');

        $io->note(sprintf('Importing file: %s', $filePath));

        $this->logFileImporter->importFile($filePath);

        return Command::SUCCESS;
    }
}
