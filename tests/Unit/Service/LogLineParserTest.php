<?php

namespace App\Service;

use App\DTO\LogLine;
use App\Enum\HttpStatusCode;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

class LogLineParserTest extends TestCase
{
    private LogLineParser $subject;

    public function setUp(): void
    {
        parent::setUp();

        $prophet = new Prophet();
        $logger = $prophet->prophesize(LoggerInterface::class);

        $this->subject = new LogLineParser($logger->reveal());
    }

    /**
     * @dataProvider parsingDataProvider
     */
    public function testParsing(string $logLine, LogLine $expectedDto): void
    {
        $this->assertEquals(
            $expectedDto,
            $this->subject->parse($logLine),
            sprintf('Parsing log line failed: %s', $logLine),
        );
    }

    public function parsingDataProvider(): array
    {
        return [
            'usual line' => [
                'USER-SERVICE - - [18/Aug/2018:10:33:59 +0000] "POST /users HTTP/1.1" 201',
                new LogLine(
                    'USER-SERVICE - - [18/Aug/2018:10:33:59 +0000] "POST /users HTTP/1.1" 201',
                    'USER-SERVICE',
                    '-',
                    '-',
                    new DateTimeImmutable('18/Aug/2018:10:33:59 +0000'),
                    'POST /users HTTP/1.1',
                    HttpStatusCode::from('201')
                ),
            ],
            'non UTC date' => [
                'USER-SERVICE - - [18/Aug/2018:10:33:59 +0300] "POST /users HTTP/1.1" 201',
                new LogLine(
                    'USER-SERVICE - - [18/Aug/2018:10:33:59 +0300] "POST /users HTTP/1.1" 201',
                    'USER-SERVICE',
                    '-',
                    '-',
                    new DateTimeImmutable('18/Aug/2018:07:33:59 +0000'),
                    'POST /users HTTP/1.1',
                    HttpStatusCode::from('201')
                ),
            ],
            'invalid date' => [
                'USER-SERVICE - - [18/aaa/0000:10:33:59 +0300] "POST /users HTTP/1.1" 201',
                new LogLine(
                    'USER-SERVICE - - [18/aaa/0000:10:33:59 +0300] "POST /users HTTP/1.1" 201',
                    'USER-SERVICE',
                    '-',
                    '-',
                    null,
                    'POST /users HTTP/1.1',
                    HttpStatusCode::from('201')
                ),
            ],
            'invalid http status' => [
                'USER-SERVICE - - [18/Aug/2018:10:33:59 +0300] "POST /users HTTP/1.1" XXX',
                new LogLine(
                    'USER-SERVICE - - [18/Aug/2018:10:33:59 +0300] "POST /users HTTP/1.1" XXX',
                    'USER-SERVICE',
                    '-',
                    '-',
                    new DateTimeImmutable('18/Aug/2018:07:33:59 +0000'),
                    'POST /users HTTP/1.1',
                    null
                ),
            ],
        ];
    }
}
