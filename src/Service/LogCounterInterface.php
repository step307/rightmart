<?php

namespace App\Service;

use App\Enum\HttpStatusCode;
use DateTimeImmutable;

interface LogCounterInterface
{
    public function count(
        array              $serviceNames = [],
        ?HttpStatusCode    $statusCode = null,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null
    ): int;
}