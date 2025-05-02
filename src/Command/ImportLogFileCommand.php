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
class ImportLogFileCommand extends Command
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
        // TODO: do we need some semaphores, protection from importing the same file twice ? etc.

        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filePath');

        $io->info(sprintf('Importing file: %s', $filePath));

        $importResult = $this->logFileImporter->importFile($filePath);

        $io->info([
            sprintf('Total lines read: %d', $importResult->linesRead),
            sprintf('Skipped empty lines: %d', $importResult->emptyLinesSkipped),
            sprintf('Parse errors: %d', $importResult->parseErrors),
            sprintf('Saving errors: %d', $importResult->saveErrors),
            sprintf('Lines saved: %d', $importResult->savedSuccessfully)
        ]);

        return Command::SUCCESS;
    }
}
