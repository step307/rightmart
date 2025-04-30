<?php

namespace App\Service;

interface LogCounterInterface
{
    public function count(
        ?array $serviceNames = [],
        ?string $statusCode = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): int;
}