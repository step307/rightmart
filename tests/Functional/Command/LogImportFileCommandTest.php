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

        $userServiceLine = $this->connection->executeQuery(
            'SELECT serviceName,dateTime,request,httpStatusCode,logLine FROM http_log WHERE serviceName = "USER-SERVICE"'
        )->fetchAssociative();

        self::assertEquals(
            [
                'serviceName' => 'USER-SERVICE',
                'dateTime' => '2018-08-18 10:33:59',
                'request' => 'POST /users HTTP/1.1',
                'httpStatusCode' => '201',
                "logLine" => 'USER-SERVICE - - [18/Aug/2018:10:33:59 +0000] "POST /users HTTP/1.1" 201',
            ],
            $userServiceLine,
            'There should be 1 log line imported into DB for USER-SERVICE',
        );

        $invoiceServiceLine = $this->connection->executeQuery(
            'SELECT serviceName,dateTime,request,httpStatusCode,logLine FROM http_log WHERE serviceName = "INVOICE-SERVICE"'
        )->fetchAssociative();

        self::assertEquals(
            [
                'serviceName' => 'INVOICE-SERVICE',
                'dateTime' => '2018-08-18 10:26:53',
                'request' => 'POST /invoices HTTP/1.1',
                'httpStatusCode' => '201',
                "logLine" => 'INVOICE-SERVICE - - [18/Aug/2018:10:26:53 +0000] "POST /invoices HTTP/1.1" 201',
            ],
            $invoiceServiceLine,
            'There should be 1 log line imported into DB for INVOICE-SERVICE',
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
