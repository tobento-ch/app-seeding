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

namespace Tobento\App\Seeding\Test\Repository;

use PHPUnit\Framework\TestCase;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Seeding\FactoryInterface;
use Tobento\App\Seeding\Repository\RepositoryFactory;
use Tobento\App\Seeding\Repository\AbstractFactory;
use Tobento\Service\Filesystem\Dir;
use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\Column;
use Tobento\Service\Storage\InMemoryStorage;

class RepositoryFactoryTest extends TestCase
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
        
        $app->boot(\Tobento\App\Seeding\Boot\Seeding::class);
        $app->booting();
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testNewMethodReturnsFactory()
    {
        $app = $this->createApp();
        
        $repo = new class(
            storage: new  InMemoryStorage(items: []),
            table: 'users',
            columns: [
                new Column\Id(),
                new Column\Text('email'),
            ],
        ) extends StorageRepository {
            //
        };

        $factory = RepositoryFactory::new(repository: $repo);
        
        $this->assertInstanceof(FactoryInterface::class, $factory);
        $this->assertInstanceof(AbstractFactory::class, $factory);
    }
}