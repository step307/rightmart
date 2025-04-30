<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LogImportFileCommandTest extends WebTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->connection = self::getContainer()->get(Connection::class);
    }

    public function testSuccessfulLogFileImport(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:log:import-file');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filePath' => __DIR__ . '/Fixtures/logs.log',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $countInDb = $this->connection->executeQuery(
            'SELECT COUNT(*) FROM http_log'
        )->fetchOne();

        self::assertSame(21, $countInDb, 'There should be 21 log files imported into DB');
    }
}
