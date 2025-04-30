<?php

namespace App\Service;

use App\DTO\HttpLogLine;
use App\Exception\HttpLogParsingException;
use App\Repository\HttpLogRepositoryInterface;
use Psr\Log\LoggerInterface;

class LogFileImporter implements LogFileImporterInterface
{

    public function __construct(
        private readonly FileIteratorInterface      $reader,
        private readonly HttpLogLineParserInterface $parser,
        private readonly HttpLogRepositoryInterface $repository,
        private readonly LoggerInterface            $logger,
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
            } catch (HttpLogParsingException $e) {
                $this->logger->warning(
                    sprintf('Exception during importing file: %s. %s', $filePath, $e->getMessage()),
                    [
                        'error' => $e->getMessage(),
                        'filePath' => $filePath,
                        'exception' => $e,
                    ]
                );

                $log = HttpLogLine::erroneous($line);
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