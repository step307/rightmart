<?php

namespace App\DTO;

use App\Enum\HttpStatus;
use DateTimeImmutable;

readonly class LogLine
{

    public function __construct(
        public string $logLine,
        public ?string $host = null,
        public ?string $ident = null,
        public ?string $authUser = null,
        public ?DateTimeImmutable $date = null,
        public ?string $request = null,
        public ?HttpStatus $status = null,
    ) {
    }

    public static function erroneous($line): self
    {
        return new self(logLine: $line);
    }
}