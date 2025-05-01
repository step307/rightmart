<?php

namespace App\Repository;

use App\DTO\LogLine;

interface LogRepositoryInterface
{
    public function save(LogLine $httpLogLine): void;

    public function count(
        array $serviceNames = [],
        ?string $statusCode = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): int;
}