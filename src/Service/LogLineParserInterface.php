<?php

namespace App\Service;

use App\DTO\LogLine;
use App\Exception\LogParsingException;

interface LogLineParserInterface
{
    /**
     * @throws LogParsingException
     */
    public function parse(string $line): LogLine;
}