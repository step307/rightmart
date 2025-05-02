<?php

namespace App\Repository;

use App\DTO\LogLine;
use App\Enum\HttpStatusCode;
use App\Exception\RepositoryException;
use DateTimeImmutable;

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
        array              $serviceNames = [],
        ?HttpStatusCode    $statusCode = null,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null
    ): int;
}