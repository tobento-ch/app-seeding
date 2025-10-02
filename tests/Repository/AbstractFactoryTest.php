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
use Tobento\Service\Repository\RepositoryInterface;
use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\StorageEntityFactoryInterface;
use Tobento\Service\Repository\Storage\Column\ColumnsInterface;
use Tobento\Service\Repository\Storage\Column;
use Tobento\Service\Storage\InMemoryStorage;

class AbstractFactoryTest extends TestCase
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
        
        $app->set(ProductRepository::class, function() {
            return new ProductRepository(
                storage: new  InMemoryStorage(items: []),
                table: 'products',
            );
        });
        
        $app->booting();
        
        return $app;
    }
    
    protected function createStorageRepository(
        iterable|ColumnsInterface $columns,
        string $table = 'users',
        null|StorageInterface $storage = null,
        null|StorageEntityFactoryInterface $entityFactory = null,
    ): RepositoryInterface {
        
        if (is_null($storage)) {
            $storage = new InMemoryStorage(items: []);
        }
        
        return new class(
            $storage,
            $table,
            $columns,
            $entityFactory,
        ) extends StorageRepository {
            //
        };
    }
    
    protected function createSeedFactory(
        string|RepositoryInterface $repository,
        null|Closure $definition = null,
        array $replaces = [],
    ): FactoryInterface {
        return new class($repository, $definition, $replaces = []) extends AbstractFactory {
            //
        };
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testFactoryWithConstantRepositoryDefined()
    {
        $app = $this->createApp();

        $products = ProductFactory::new()->times(2)->create();
        
        $this->assertCount(2, $products);
    }
    
    public function testRepositoryMethod()
    {
        $app = $this->createApp();
        $repo = $this->createStorageRepository(
            columns: [
                new Column\Id(),
                new Column\Text('email'),
            ],
        );
        
        $factory = $this->createSeedFactory($repo);
        
        $this->assertSame($repo, $factory->repository());
    }
    
    public function testAutoDefinitionPrimaryTypeIsSkipped()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Id(),
                ],
            )
        );
        
        $def = $factory->definition();
        $this->assertFalse(array_key_exists('id', $def));
    }
    
    public function testAutoDefinitionSpecialNames()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Id(),
                    new Column\Text('email'),
                    new Column\Text('smartphone'),
                    new Column\Text('telephone'),
                    new Column\Text('password'),
                ],
            )
        );
        
        $def = $factory->definition();
        $this->assertTrue(str_contains($def['email'] ?? '', '@'));
        $this->assertTrue((bool) preg_match('/^[0-9-]+$/', $def['smartphone'] ?? ''));
        $this->assertTrue((bool) preg_match('/^[0-9-]+$/', $def['telephone'] ?? ''));
        $this->assertTrue(is_string($def['password'] ?? ''));
    }
    
    public function testAutoDefinitionIntTypes()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Integer(name: 'foo', type: 'int')->type(length: 3),
                    new Column\Integer(name: 'bar', type: 'tinyInt')->type(length: 2),
                    new Column\Integer(name: 'baz', type: 'bigInt')->type(length: 5),
                ],
            )
        );
        
        $def = $factory->definition();
        $this->assertTrue(in_array($def['foo'] ?? 0, range(1,1000)));
        $this->assertTrue(in_array($def['bar'] ?? 0, range(1,100)));
        $this->assertTrue(in_array($def['baz'] ?? 0, range(1,10000)));
    }
    
    public function testAutoDefinitionBoolType()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Boolean(name: 'foo'),
                ],
            )
        );
        
        $def = $factory->definition();
        $this->assertTrue(is_bool($def['foo'] ?? null));
    }
    
    public function testAutoDefinitionStringTypes()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Text(name: 'foo', type: 'string')->type(length: 5),
                    new Column\Text(name: 'bar', type: 'char')->type(length: 3),
                    new Column\Text(name: 'baz', type: 'text'),
                ],
            )
        );
        
        $def = $factory->definition();
        $this->assertTrue(in_array(strlen($def['foo'] ?? ''), range(1,5)));
        $this->assertTrue(in_array(strlen($def['bar'] ?? ''), range(1,3)));
        $this->assertTrue(is_string($def['foo'] ?? ''));
    }
    
    public function testAutoDefinitionDateTypes()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Datetime(name: 'foo', type: 'datetime'),
                    new Column\Datetime(name: 'bar', type: 'date'),
                    new Column\Datetime(name: 'baz', type: 'time'),
                    new Column\Datetime(name: 'stamp', type: 'timestamp'),
                ],
            )
        );
        
        $def = $factory->definition();
        $this->assertTrue($this->isValidDateFormat('Y-m-d H:i:s', $def['foo'] ?? ''));
        $this->assertTrue($this->isValidDateFormat('Y-m-d', $def['bar'] ?? ''));
        $this->assertTrue($this->isValidDateFormat('H:i:s', $def['baz'] ?? ''));
        $this->assertTrue($this->isTimestamp($def['stamp'] ?? null));
    }
    
    public function testAutoDefinitionFloatTypes()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\FloatCol(name: 'foo', type: 'float'),
                    new Column\FloatCol(name: 'bar', type: 'double'),
                    new Column\FloatCol(name: 'baz', type: 'decimal'),
                ],
            )
        );
        
        $def = $factory->definition();
        $this->assertTrue(is_float($def['foo'] ?? 0));
        $this->assertTrue(is_float($def['bar'] ?? 0));
        $this->assertTrue(is_numeric($def['baz'] ?? 0));
    }
    
    public function testAutoDefinitionJsonType()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Json(name: 'foo'),
                ],
            )
        );
        
        $def = $factory->definition();
        $this->assertSame('{"foo":"Foo"}', $def['foo'] ?? null);
    }
    
    public function testAutoDefinitionTranslatableStringColumn()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Translatable(name: 'foo')->type(length: 5),
                ],
            )
        );
        
        $def = $factory->definition();
        $foo = $def['foo'] ?? [];
        $this->assertTrue(in_array(strlen($foo['en'] ?? ''), range(1,5)));
    }
    
    public function testAutoDefinitionTranslatableArrayColumn()
    {
        $app = $this->createApp();
        
        $factory = $this->createSeedFactory(
            repository: $this->createStorageRepository(
                columns: [
                    new Column\Translatable(name: 'foo', subtype: 'array'),
                ],
            )
        );
        
        $def = $factory->definition();
        $foo = $def['foo'] ?? [];
        $this->assertSame('{"foo":"Foo"}', $foo['en'] ?? null);
    }
    
    protected function isValidDateFormat(string $format, string $value): bool
    {
        $date = \DateTime::createFromFormat('!'.$format, $value);

        return $date && $date->format($format) == $value ? true : false;
    }
    
    protected function isTimestamp(mixed $value): bool
    {
        try {
            new \DateTime('@'.$value);
        } catch(\Exception $e) {
            return false;
        }
        
        return true;
    }
}

class ProductFactory extends AbstractFactory
{
    public const REPOSITORY = ProductRepository::class;
}

class ProductRepository extends StorageRepository
{
    protected function configureColumns(): iterable|ColumnsInterface
    {
        return [
            new Column\Id(),
            new Column\Text('sku'),
            new Column\Text('title'),
        ];
    }
}