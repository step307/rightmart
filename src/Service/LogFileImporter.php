<?php

namespace App\Service;

use App\DTO\ImportResult;
use App\DTO\LogLine;
use App\Exception\LogParsingException;
use App\Exception\RepositoryException;
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

    public function importFile(string $filePath): ImportResult
    {
        $firstLine = true;
        $this->reader->openFile($filePath);
        $result = new ImportResult();

        foreach ($this->reader as $line) {
            $result->linesRead++;

            if ($firstLine) {
                $line = $this->removeBom($line);
                $firstLine = false;
            }

            $trimmedLine = trim($line);

            if ($trimmedLine === '') {
                $result->emptyLinesSkipped++;
                continue;
            }

            try {
                $log = $this->parser->parse($trimmedLine);
            } catch (LogParsingException $e) {
                $result->parseErrors++;
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

            try {
                $this->repository->save($log);
                $result->savedSuccessfully++;
            } catch (RepositoryException $e) {
                $result->saveErrors++;
                $this->logger->alert(
                    'Exception during saving log line: {filePath}. {error}',
                    [
                        'error' => $e->getMessage(),
                        'filePath' => $filePath,
                        'exception' => $e,
                    ]
                );
            }
        }

        return $result;
    }

    public function removeBom(string $line): string
    {
        return str_replace("\xEF\xBB\xBF", '', $line);
    }
}