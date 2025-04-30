<?php

namespace E2e;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LogE2eTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::createClient();

        $application = new Application(self::$kernel);

        $command = $application->find('app:log:import-file');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filePath' => __DIR__ . '/Fixtures/logs.log',
        ]);
    }

    /**
     * @dataProvider requestsProvider
     */
    public function testSuccessfulImportAndCount(string $request, int $expectedCount): void
    {
        self::getClient()->request('GET', $request);

        $this->assertResponseIsSuccessful();
        $response = self::getClient()->getResponse()->getContent();

        self::assertEquals(
            ['count' => $expectedCount],
            json_decode($response, true),
            sprintf('Response should report count %d for request GET %s', $expectedCount, $request),
        );
    }

    public function requestsProvider(): array
    {
        return [
            ['/count', 21], // we include also "unknown" services from erroneous log lines
            ['/count?endDate=2019-01-01', 20], // erroneous lines might be excluded if their date is unknown
            ['/count?serviceNames[0]=USER-SERVICE', 14],
            ['/count?statusCode=400', 4],
            ['/count?startDate=2019-01-01', 0],
            ['/count?serviceNames[0]=USER-SERVICE&serviceNames[1]=USER-SERVICE2&statusCode=201&startDate=2018-08-17 09:22:58&endDate=2018-08-18 09:31:55', 5],
        ];
    }
}
