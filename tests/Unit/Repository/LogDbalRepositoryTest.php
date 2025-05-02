<?php

namespace App\Repository;

use App\DTO\LogLine;
use App\Enum\HttpStatus;
use App\Exception\RepositoryException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

class LogDbalRepositoryTest extends TestCase
{
    private readonly Connection|ObjectProphecy $connection;
    private readonly LoggerInterface|ObjectProphecy $logger;
    private readonly LogDbalRepository|ObjectProphecy $subject;
    private readonly Prophet $prophet;

    public function setUp(): void
    {
        parent::setUp();

        $this->prophet = new Prophet;
        $this->connection = $this->prophet->prophesize(Connection::class);
        $this->logger = $this->prophet->prophesize(LoggerInterface::class);

        $this->subject = new LogDbalRepository(
            $this->connection->reveal(),
            $this->logger->reveal(),
        );
    }

    public function testFailureOnSave(): void
    {
        $this->connection->insert( 'http_log', Argument::type('array'))
            ->shouldBeCalled()
            ->willThrow(\Doctrine\DBAL\ConnectionException::class)
        ;

        $this->logger->alert(Argument::type('string'), Argument::type('string'))
            ->shouldBeCalled()
        ;

        $this->expectException(RepositoryException::class);

        $this->subject->save(
            new LogLine(
                'whole-line',
                'some-service',
                '-',
                '-',
                new \DateTimeImmutable(),
                'GET /something 1.1',
                HttpStatus::from('200')
            )
        );
    }

    public function testFailureOnCount(): void
    {
        $qb = $this->prophet->prophesize(QueryBuilder::class);
        $qb->select(Argument::cetera())->willReturn($qb->reveal());
        $qb->from(Argument::cetera())->willReturn($qb->reveal());
        $qb->getSQL()->willReturn('some sql for logging');

        $qb->executeQuery()
            ->shouldBeCalled()
            ->willThrow(\Doctrine\DBAL\ConnectionException::class)
        ;

        $this->connection->createQueryBuilder()
            ->shouldBeCalled()
            ->willReturn($qb->reveal())
        ;

        $this->logger->alert(Argument::type('string'), Argument::type('string'))
            ->shouldBeCalled()
        ;

        $this->expectException(RepositoryException::class);

        $this->subject->count();
    }
}
