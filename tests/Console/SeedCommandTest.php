<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Seeding\Test\Console;

use PHPUnit\Framework\TestCase;
use Tobento\App\Seeding\Console\SeedCommand;
use Tobento\App\Seeding\Test\Mock;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\App\Seeding\Seeders;
use Tobento\App\Seeding\SeedersInterface;
use Tobento\Service\Container\Container;

class SeedCommandTest extends TestCase
{
    public function testCommand()
    {
        $container = new Container();
        $container->set(SeedersInterface::class, Seeders::class);
        
        $seeders = $container->get(SeedersInterface::class);
        $seeders->addSeeder('foo', Mock\Seeder::class);
        $seeders->addSeeder('bar', Mock\Seeder::class);
        
        (new TestCommand(
            command: SeedCommand::class,
        ))
        ->expectsOutputToContain('Seeder foo starting')
        ->expectsOutputToContain('Seeder foo finished')
        ->expectsOutputToContain('Seeder bar starting')
        ->expectsOutputToContain('Seeder bar finished')
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testCommandRunsSpecificSeeder()
    {
        $container = new Container();
        $container->set(SeedersInterface::class, Seeders::class);
        
        $seeders = $container->get(SeedersInterface::class);
        $seeders->addSeeder('foo', Mock\Seeder::class);
        $seeders->addSeeder('bar', Mock\Seeder::class);
        
        (new TestCommand(
            command: SeedCommand::class,
            input: ['--name' => ['bar']],
        ))
        ->doesntExpectOutputToContain('Seeder foo starting')
        ->doesntExpectOutputToContain('Seeder foo finished')
        ->expectsOutputToContain('Seeder bar starting')
        ->expectsOutputToContain('Seeder bar finished')
        ->expectsExitCode(0)
        ->execute($container);
    }    
    
    public function testCommandWithVerbosity()
    {
        $container = new Container();
        $container->set(SeedersInterface::class, Seeders::class);
        
        $seeders = $container->get(SeedersInterface::class);
        $seeders->addSeeder('foo', new Mock\Seeder([['name' => 'Foo']]));
        
        (new TestCommand(
            command: SeedCommand::class,
            input: ['-v' => null],
        ))
        ->expectsOutputToContain('Seeder foo starting')
        ->expectsOutputToContain('"name": "Foo"')
        ->expectsOutputToContain('Seeder foo finished')
        ->expectsExitCode(0)
        ->execute($container);
    }
}