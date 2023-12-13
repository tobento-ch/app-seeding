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

namespace Tobento\App\Seeding\Test\User;

use PHPUnit\Framework\TestCase;
use Tobento\App\Seeding\Boot\Seeding;
use Tobento\App\Seeding\SeedersInterface;
use Tobento\App\Seeding\User\UserFactory;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\AddressRepositoryInterface;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Acl\Role;
use Tobento\App\User\UserInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Boot;
use Tobento\Service\Filesystem\Dir;

class UserFactoryTest extends TestCase
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
        
        $app->boot(Seeding::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        $app->booting();
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testMakeMethod()
    {
        $app = $this->createApp();
        
        $users = UserFactory::new()->times(2)->make();
        
        $this->assertCount(2, $users);
    }
    
    public function testMakeOneMethod()
    {
        $app = $this->createApp();
        
        $user = UserFactory::new()->makeOne();
        
        $this->assertInstanceof(UserInterface::class, $user);
    }
    
    public function testCreateMethod()
    {
        $app = $this->createApp();
        $userRepository = $app->get(UserRepositoryInterface::class);
        $addressRepository = $app->get(AddressRepositoryInterface::class);
        
        $this->assertSame(0, $userRepository->count());
        $this->assertSame(0, $addressRepository->count());
        
        $users = UserFactory::new()->times(2)->create();
        
        $this->assertSame(2, $userRepository->count());
        $this->assertSame(2, $addressRepository->count());
        $this->assertCount(2, $users);
    }
    
    public function testCreateOneMethod()
    {
        $app = $this->createApp();
        $userRepository = $app->get(UserRepositoryInterface::class);
        $addressRepository = $app->get(AddressRepositoryInterface::class);
        
        $this->assertSame(0, $userRepository->count());
        $this->assertSame(0, $addressRepository->count());
        
        $user = UserFactory::new()->createOne();
        
        $this->assertSame(1, $userRepository->count());
        $this->assertSame(1, $addressRepository->count());
        $this->assertInstanceof(UserInterface::class, $user);
    }
    
    public function testRawMethod()
    {
        $app = $this->createApp();
        
        $users = UserFactory::new()->times(2)->raw();
        
        $this->assertCount(2, $users);
    }
    
    public function testRawOneMethod()
    {
        $app = $this->createApp();
        
        $user = UserFactory::new()->rawOne();
        
        $this->assertTrue(is_array($user));
    }
    
    public function testModifyMethods()
    {
        $app = $this->createApp();
        
        $user = UserFactory::new()
            ->withEmail('foo@example.com')
            ->withSmartphone('22334455')
            ->withUsername('Username')
            ->withPassword('123456')
            ->withRoleKey('admin')
            ->withAddress(['firstname' => 'Firstname'])
            ->makeOne();
        
        $this->assertSame('foo@example.com', $user->email());
        $this->assertSame('22334455', $user->smartphone());
        $this->assertSame('Username', $user->username());
        $this->assertSame('guest', $user->role()->key());
        $this->assertSame('Firstname', $user->address()->firstname());
    }
    
    public function testModifyRoleKey()
    {
        $app = $this->createApp();
        $acl = $app->get(AclInterface::class);
        $acl->setRoles([
            new Role('admin'),
        ]);
        
        $user = UserFactory::new()->withRoleKey('admin')->makeOne();
        
        $this->assertSame('admin', $user->role()->key());
    }
}