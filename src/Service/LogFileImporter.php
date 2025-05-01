<?php

namespace App\Service;

use App\DTO\LogLine;
use App\Exception\LogParsingException;
use App\Repository\LogRepositoryInterface;
use Psr\Log\LoggerInterface;

class LogFileImporter implements LogFileImporterInterface
{

    public function __construct(
        private readonly FileIteratorInterface  $reader,
        private readonly LogLineParserInterface $parser,
        private readonly LogRepositoryInterface $repository,
        private readonly LoggerInterface        $logger,
    ) {
    }

    public function importFile(string $filePath): void
    {
        $firstLine = true;
        $this->reader->openFile($filePath);

        foreach ($this->reader as $line) {

            if ($firstLine) {
                $line = $this->removeBom($line);
                $firstLine = false;
            }

            $trimmedLine = trim($line);

            if ($trimmedLine === '') {
                continue;
            }

            try {
                $log = $this->parser->parse($trimmedLine);
            } catch (LogParsingException $e) {
                $this->logger->warning(
                    'Exception during importing file: {filePath}. {error}',
                    [
                        'error' => $e->getMessage(),
                        'filePath' => $filePath,
                        'exception' => $e,
                    ]
                );

                $log = LogLine::erroneous($line);
            }

            $this->repository->save($log);

            //var_dump($log);
        }
    }

    public function removeBom(string $line): string
    {
        return str_replace("\xEF\xBB\xBF", '', $line);
    }
}