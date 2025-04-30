<?php

namespace App\Service;

use App\DTO\HttpLogLine;
use App\Exception\HttpLogParsingException;
use DateTimeImmutable;

class HttpLogLineParser implements HttpLogLineParserInterface
{
    public function parse(string $line): HttpLogLine
    {
        $values = str_getcsv($line, ' ',);

        preg_match('/(.+) (.+) (.+) \[(.+)\] "(.+)" (.+)/', $line, $values);

        // TODO: validate, parse, format

        if (count($values) !== 7) {
            // TODO: it might be inappropriate to put the whole line into exception, e.g. due to eventual private data in it
            throw new HttpLogParsingException(sprintf(
                'Could not parse log line, number of values not 6. Log line: %s. Parsed values: %s',
                $line,
                implode(', ', $values)
            ));
        }

        return new HttpLogLine(
            $line,
            $values[1],
            $values[2],
            $values[3],
            new DateTimeImmutable($values[4]),
            $values[5],
            $values[6],
        );
    }
}