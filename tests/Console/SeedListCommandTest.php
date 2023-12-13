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
use Tobento\App\Seeding\Console\SeedListCommand;
use Tobento\App\Seeding\Test\Mock;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\App\Seeding\Seeders;
use Tobento\App\Seeding\SeedersInterface;
use Tobento\Service\Container\Container;

class SeedListCommandTest extends TestCase
{
    public function testCommand()
    {
        $container = new Container();
        $container->set(SeedersInterface::class, Seeders::class);
        
        $seeders = $container->get(SeedersInterface::class);
        $seeders->addSeeder('foo', Mock\Seeder::class);
        $seeders->addSeeder('bar', Mock\Seeder::class);
        
        (new TestCommand(
            command: SeedListCommand::class,
        ))
        ->expectsTable(
            headers: ['Seeder Name'],
            rows: [
                ['foo', 'bar'],
            ],
        )
        ->expectsExitCode(0)
        ->execute($container);
    }
}