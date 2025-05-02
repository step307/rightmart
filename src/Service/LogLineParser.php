<?php

namespace App\Service;

use App\DTO\LogLine;
use App\Enum\HttpStatus;
use App\Exception\LogParsingException;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use TypeError;
use ValueError;

class LogLineParser implements LogLineParserInterface
{

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function parse(string $line): LogLine
    {
        $this->logger->debug('Parsing log line: {line}', ['line' => $line]);

        preg_match('/(.+) (.+) (.+) \[(.+)] "(.+)" (.+)/', $line, $values);

        if (count($values) !== 7) {
            // it might be inappropriate to put the whole line into log/exception, e.g. due to eventual private data in it
            throw new LogParsingException(sprintf(
                'Could not parse log line, number of values not 6. Log line: %s. Parsed values: %s',
                $line,
                implode(', ', $values)
            ));
        }

        try {
            $dateTime = (new DateTimeImmutable($values[4]))->setTimezone(new DateTimeZone('UTC'));
        } catch (DateMalformedStringException $e) {
            $dateTime = null;
            // error level could be also appropriate as this should never happen actually
            $this->logger->warning(
                'Could not parse log line, malformed date. {error}',
                [
                    'error' => $e->getMessage(),
                    'line' => $line,
                    'exception' => $e,
                ]
            );
        }

        try {
            $status = HttpStatus::from($values[6]);
        } catch (TypeError|ValueError $e) {
            $status = null;
            // error level could be also appropriate as this should never happen actually
            $this->logger->warning(
                'Could not parse log line, malformed status. {error}',
                [
                    'error' => $e->getMessage(),
                    'line' => $line,
                    'exception' => $e,
                ]
            );
        }


        $logLineDto = new LogLine(
            $line,
            $values[1],
            $values[2],
            $values[3],
            $dateTime,
            $values[5],
            $status,
        );

        $this->logger->debug('Parsed data: {line}', ['line' => json_encode($logLineDto)]);

        return $logLineDto;
    }
}