<?php

namespace App\Repository;

use App\DTO\LogLine;
use App\Service\LogCounterInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Ramsey\Uuid\Uuid;

// Normally we have a service between controller and repository. But to avoid 1-line-classes, because of YAGNI and some
// laziness repository will stand also for the service interface (LogCounterInterface). It is easy to change in the future.
class LogDbalRepository implements LogRepositoryInterface, LogCounterInterface
{

    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function save(LogLine $httpLogLine): void
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

    public function count(?array $serviceNames = [], ?string $statusCode = null, ?string $startDate = null, ?string $endDate = null): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('count(*)')
        ->from('http_log');

        if (count($serviceNames)) {
            $qb->where($qb->expr()->in('serviceName', ':serviceNames'));
            $qb->setParameter('serviceNames', $serviceNames, ArrayParameterType::STRING);
        }

        if ($statusCode !== null) {
            $qb->andWhere($qb->expr()->eq('httpStatusCode', ':statusCode'));
            $qb->setParameter('statusCode', $statusCode, ParameterType::STRING);
        }

        if ($startDate !== null) {
            $qb->andWhere($qb->expr()->gte('dateTime', ':startDate'));
            $qb->setParameter('startDate', $startDate, ParameterType::STRING);
        }

        if ($endDate !== null) {
            $qb->andWhere($qb->expr()->lte('dateTime', ':endDate'));
            $qb->setParameter('endDate', $endDate, ParameterType::STRING);
        }

        $qb->executeQuery();

        return $qb->fetchOne();
    }
}