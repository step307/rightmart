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

        $this->connection = self::getContainer()->get(Connection::class);

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
            ['/count', 20],
        ];
    }
}
