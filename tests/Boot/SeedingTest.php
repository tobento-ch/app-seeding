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

namespace Tobento\App\Seeding\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\Seeding\Boot\Seeding;
use Tobento\App\Seeding\SeedersInterface;
use Tobento\App\Seeding\User\UserStorageSeeder;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\Service\Seeder\SeedInterface;
use Tobento\Service\Console\ConsoleInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Boot;
use Tobento\Service\Filesystem\Dir;

class SeedingTest extends TestCase
{
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Seeding::class);
        $app->booting();
        
        $this->assertInstanceof(SeedersInterface::class, $app->get(SeedersInterface::class));
        $this->assertInstanceof(SeedInterface::class, $app->get(SeedInterface::class));
    }
    
    public function testDefaultSeedersAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Seeding::class);
        $app->booting();
        $seed = $app->get(SeedInterface::class);
        
        // resource seeder:
        $this->assertIsString($seed->itemFrom(resource: 'countries'));
        
        // datetime seeder
        $this->assertIsString($seed->month(from: 2, to: 10));
        
        // user seeder
        $this->assertIsString($seed->email());
    }
    
    public function testConsoleCommandsAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Seeding::class);
        $app->booting();
        
        $console = $app->get(ConsoleInterface::class);
        $this->assertTrue($console->hasCommand('seed'));
        $this->assertTrue($console->hasCommand('seed:list'));
    }
    
    public function testSeedRunCommand()
    {
        $app = $this->createApp();
        $app->boot(Seeding::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        $app->booting();

        $app->on(SeedersInterface::class, static function ($seeders) {
            $seeders->addSeeder('users', UserStorageSeeder::class);
        });
        
        $userRepository = $app->get(UserRepositoryInterface::class);
        
        $this->assertSame(0, $userRepository->count());
        
        $executed = $app->get(ConsoleInterface::class)->execute(
            command: 'seed',
        );
        
        $this->assertSame(100, $userRepository->count());
        $output = $executed->output();
        $this->assertSame(0, $executed->code());
        $this->assertStringContainsString('Seeder users starting', $output);
        $this->assertStringContainsString('Seeder users finished', $output);
    }
}