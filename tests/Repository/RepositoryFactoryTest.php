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
use Tobento\App\Seeding\FactoryInterface;
use Tobento\App\Seeding\Repository\RepositoryFactory;
use Tobento\App\Seeding\Repository\AbstractFactory;
use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\Column;
use Tobento\Service\Storage\InMemoryStorage;

class RepositoryFactoryTest extends TestCase
{
    public function testNewMethodReturnsFactory()
    {
        $repo = new class(
            storage: new  InMemoryStorage(items: []),
            table: 'users',
            columns: [
                Column\Id::new(),
                Column\Text::new('email'),
            ],
        ) extends StorageRepository {
            //
        };        

        $factory = RepositoryFactory::new(repository: $repo);
        
        $this->assertInstanceof(FactoryInterface::class, $factory);
        $this->assertInstanceof(AbstractFactory::class, $factory);
    }
}