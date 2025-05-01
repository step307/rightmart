<?php

namespace App\Service;

use App\DTO\LogLine;
use App\Exception\LogParsingException;
use DateMalformedStringException;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class LogLineParser implements LogLineParserInterface
{

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function parse(string $line): LogLine
    {
        $values = str_getcsv($line, ' ',);

        preg_match('/(.+) (.+) (.+) \[(.+)] "(.+)" (.+)/', $line, $values);

        if (count($values) !== 7) {
            // TODO: it might be inappropriate to put the whole line into exception, e.g. due to eventual private data in it
            throw new LogParsingException(sprintf(
                'Could not parse log line, number of values not 6. Log line: %s. Parsed values: %s',
                $line,
                implode(', ', $values)
            ));
        }

        try {
            $dateTime = new DateTimeImmutable($values[4]);
        } catch (DateMalformedStringException $e) {
            $dateTime = null;
            $this->logger->warning(
                'Could not parse log line, malformed date. {error}',
                [
                    'error' => $e->getMessage(),
                    'line' => $line,
                    'exception' => $e,
                ]
            );
        }

        return new LogLine(
            $line,
            $values[1],
            $values[2],
            $values[3],
            $dateTime,
            $values[5],
            $values[6],
        );
    }
}