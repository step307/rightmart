<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class LogImportFileCommandTest extends KernelTestCase
{
    public function testSomething(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:log:import-file');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filePath' => __DIR__ . '/Fixtures/logs.log',
        ]);

        $commandTester->assertCommandIsSuccessful();
    }
}
