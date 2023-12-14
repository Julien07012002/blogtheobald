<?php


namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTest extends KernelTestCase
{
   
    protected function executeCommand(array $arguments, array $inputs = []): CommandTester
    {
        self::bootKernel();

        $command = static::getContainer()->get($this->getCommandFqcn());
        $command->setApplication(new Application(self::$kernel));

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);

        return $commandTester;
    }

    abstract protected function getCommandFqcn(): string;
}
