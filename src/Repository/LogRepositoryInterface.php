<?php

namespace App\Repository;

use App\DTO\LogLine;
use App\Exception\RepositoryException;

interface LogRepositoryInterface
{
    /**
     * @throws RepositoryException
     */
    public function save(LogLine $httpLogLine): void;

    /**
     * @throws RepositoryException
     */
    public function count(
        array $serviceNames = [],
        ?string $statusCode = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): int;
}