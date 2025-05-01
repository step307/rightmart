<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LogImportFileCommandTest extends WebTestCase
{
    private Connection $connection;

    private function getServiceLine(string $serviceName): array|false
    {
        return $this->connection->executeQuery(
            'SELECT serviceName,dateTime,request,httpStatusCode,logLine FROM http_log WHERE serviceName = :serviceName',
            ['serviceName' => $serviceName]
        )->fetchAssociative();
    }

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

        self::assertSame(
            4,
            $this->connection->executeQuery('SELECT COUNT(*) FROM http_log')->fetchOne(),
            'All 4 non empty lines should be imported'
        );

        self::assertEquals(
            [
                'serviceName' => 'USER-SERVICE',
                'dateTime' => '2018-08-18 10:33:59',
                'request' => 'POST /users HTTP/1.1',
                'httpStatusCode' => '201',
                "logLine" => 'USER-SERVICE - - [18/Aug/2018:10:33:59 +0000] "POST /users HTTP/1.1" 201',
            ],
            $this->getServiceLine('USER-SERVICE'),
            'There should be 1 log line imported into DB for USER-SERVICE',
        );

        self::assertEquals(
            [
                'serviceName' => 'INVOICE-SERVICE',
                'dateTime' => '2018-08-18 10:26:53',
                'request' => 'POST /invoices HTTP/1.1',
                'httpStatusCode' => '201',
                "logLine" => 'INVOICE-SERVICE - - [18/Aug/2018:10:26:53 +0000] "POST /invoices HTTP/1.1" 201',
            ],
            $this->getServiceLine('INVOICE-SERVICE'),
            'There should be 1 log line imported into DB for INVOICE-SERVICE',
        );

        self::assertEquals(
            [
                'serviceName' => 'BAD-DATE',
                'dateTime' => null,
                'request' => 'POST /users HTTP/1.1',
                'httpStatusCode' => '201',
                "logLine" => 'BAD-DATE - - [18Augg2018:10:34:590000] "POST /users HTTP/1.1" 201',
            ],
            $this->getServiceLine('BAD-DATE'),
            'Also the invalid date line should be imported',
        );

        $erroneousLine = $this->connection->executeQuery(
            'SELECT logLine FROM http_log WHERE serviceName IS NULL AND httpStatusCode IS NULL AND request IS NULL'
        )->fetchAssociative();

        self::assertEquals(
            [
                "logLine" => 'IAMBROKEN',
            ],
            $erroneousLine,
            'There should be 1 empty line imported into DB',
        );
    }
}
