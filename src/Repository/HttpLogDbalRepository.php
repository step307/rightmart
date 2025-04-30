<?php

namespace App\Repository;

use App\DTO\HttpLogLine;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

class HttpLogDbalRepository implements HttpLogRepositoryInterface
{

    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function save(HttpLogLine $httpLogLine): void
    {
        // TODO: catch exception ?
        $this->connection->insert('http_log', [
            'id' => Uuid::uuid4()->toString(),
            'serviceName' => $httpLogLine->host,
            'dateTime' => $httpLogLine->date?->format(self::DATE_TIME_FORMAT),
            'request' => $httpLogLine->request,
            'httpStatusCode' => $httpLogLine->status,
            'logLine' => $httpLogLine->logLine,
        ]);
    }
}