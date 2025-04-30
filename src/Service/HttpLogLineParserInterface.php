<?php

namespace App\Service;

use App\DTO\HttpLogLine;
use App\Exception\HttpLogParsingException;

interface HttpLogLineParserInterface
{
    /**
     * @throws HttpLogParsingException
     */
    public function parse(string $line): HttpLogLine;
}