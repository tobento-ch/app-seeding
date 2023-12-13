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

namespace Tobento\App\Seeding\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\Seeding\Test\Mock;
use Tobento\App\Seeding\Seeders;
use Tobento\App\Seeding\SeedersInterface;
use Tobento\App\Seeding\SeedingException;
use Tobento\App\Seeding\User\UserStorageSeeder;
use Tobento\Service\Container\Container;

class SeedersTest extends TestCase
{
    public function testThatImplementsSeedersInterface()
    {
        $this->assertInstanceof(SeedersInterface::class, new Seeders(new Container()));
    }
    
    public function testUsingClassInstance()
    {
        $seeders = new Seeders(new Container());
        $seeder = new Mock\Seeder();
        
        $seeders->addSeeder(name: 'foo', seeder: $seeder);
        
        $this->assertTrue($seeders->hasSeeder(name: 'foo'));
        $this->assertSame($seeder, $seeders->getSeeder(name: 'foo'));
        $this->assertSame(['foo'], $seeders->names());
    }
    
    public function testUsingClassName()
    {
        $seeders = new Seeders(new Container());
        
        $seeders->addSeeder(name: 'foo', seeder: Mock\Seeder::class);
        
        $this->assertTrue($seeders->hasSeeder(name: 'foo'));
        $this->assertInstanceof(Mock\Seeder::class, $seeders->getSeeder(name: 'foo'));
        $this->assertSame(['foo'], $seeders->names());
    }

    public function testGetSeederMethodThrowsSeedingExceptionIfSeederNotExists()
    {
        $this->expectException(SeedingException::class);
        
        $seeders = new Seeders(new Container());
        $seeders->getSeeder(name: 'foo');
    }
    
    public function testGetSeederMethodThrowsSeedingExceptionIfCannotCreateSeeder()
    {
        $this->expectException(SeedingException::class);
        
        $seeders = new Seeders(new Container());
        $seeders->addSeeder(name: 'foo', seeder: UserStorageSeeder::class);
        $seeders->getSeeder(name: 'foo');
    }
}