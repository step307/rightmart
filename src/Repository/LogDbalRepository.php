<?php

namespace App\Repository;

use App\DTO\LogLine;
use App\Enum\HttpStatusCode;
use App\Exception\RepositoryException;
use App\Service\LogCounterInterface;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

// Normally we have a service between controller and repository. But to avoid 1-line-classes, because of YAGNI and some
// laziness repository will stand also for the service interface (LogCounterInterface). It is easy to change in the future.
class LogDbalRepository implements LogRepositoryInterface, LogCounterInterface
{

    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const TABLE_NAME_LOG = 'http_log';

    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function save(LogLine $httpLogLine): void
    {

        try {
            $this->connection->insert(self::TABLE_NAME_LOG, [
                'id' => Uuid::uuid4()->toString(),
                'serviceName' => $httpLogLine->host,
                'dateTime' => $httpLogLine->date?->format(self::DATE_TIME_FORMAT),
                'request' => $httpLogLine->request,
                'httpStatusCode' => $httpLogLine->status?->value,
                'logLine' => $httpLogLine->logLine,
            ]);
        } catch (Exception $e) {
            $this->logger->alert(
                'Error during DB insert. {error}',
                [
                    'error' => $e->getMessage(),
                    'line' => $httpLogLine->logLine,
                    'exception' => $e,
                ]
            );

            throw new RepositoryException(
                sprintf('Error during DB insert. %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    public function count(array $serviceNames = [], ?HttpStatusCode $statusCode = null, ?DateTimeImmutable $startDate = null, ?DateTimeImmutable $endDate = null): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('count(*)')
        ->from(self::TABLE_NAME_LOG);

        if (count($serviceNames)) {
            $qb->where($qb->expr()->in('serviceName', ':serviceNames'));
            $qb->setParameter('serviceNames', $serviceNames, ArrayParameterType::STRING);
        }

        if ($statusCode !== null) {
            $qb->andWhere($qb->expr()->eq('httpStatusCode', ':statusCode'));
            $qb->setParameter('statusCode', $statusCode->value, ParameterType::STRING);
        }

        if ($startDate !== null) {
            $qb->andWhere($qb->expr()->gte('dateTime', ':startDate'));
            $qb->setParameter(
                'startDate',
                $startDate->setTimezone(new DateTimeZone('UTC'))->format(self::DATE_TIME_FORMAT)
            );
        }

        if ($endDate !== null) {
            $qb->andWhere($qb->expr()->lte('dateTime', ':endDate'));
            $qb->setParameter(
                'endDate',
                $endDate->setTimezone(new DateTimeZone('UTC'))->format(self::DATE_TIME_FORMAT)
            );
        }

        try {
            $qb->executeQuery();
            return $qb->fetchOne();
        } catch (Exception $e) {
            $this->logger->alert(
                'Error during DB select. {error}',
                [
                    'error' => $e->getMessage(),
                    'query' => $qb->getSQL(),
                    'exception' => $e,
                ]
            );

            throw new RepositoryException(
                sprintf('Error during DB select. %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

    }
}