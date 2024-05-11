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

namespace Tobento\App\Seeding\Repository;

use Tobento\App\Seeding\AbstractFactory as BaseAbstractFactory;
use Tobento\Service\Seeder\Arr;
use Tobento\Service\Seeder\Lorem;
use Tobento\Service\Seeder\Num;
use Tobento\Service\Seeder\Str;
use Tobento\Service\Repository\RepositoryInterface;
use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\Column\ColumnInterface;
use Tobento\Service\Repository\Storage\Column\Translatable;
use Closure;

/**
 * AbstractFactory
 */
abstract class AbstractFactory extends BaseAbstractFactory
{
    /**
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;
    
    /**
     * Create a new AbstractFactory.
     *
     * @param null|string|RepositoryInterface $repository
     * @param null|Closure $definition
     * @param array $replaces
     */
    public function __construct(
        null|string|RepositoryInterface $repository = null,
        protected null|Closure $definition = null,
        array $replaces = [],
    ) {
        if (is_null($repository)) {
            if (!defined(sprintf('%s::%s', static::class, 'REPOSITORY'))) {
                throw new \InvalidArgumentException('Pass a repository or define a REPOSITORY constant');
            }
            
            $repository = static::REPOSITORY;
        }
        
        $this->repository = is_string($repository)
            ? $this->getService($repository)
            : $repository;
        
        parent::__construct($replaces);
    }

    /**
     * Returns the repository.
     *
     * @return RepositoryInterface
     */
    public function repository(): RepositoryInterface
    {
        return $this->repository;
    }
    
    /**
     * Returns the definition.
     *
     * @return array
     */
    public function definition(): array
    {
        if (is_null($this->definition)) {
            return $this->createDefinitionFromRepositoryColumns();
        }

        return ($this->definition)($this->seed);
    }

    /**
     * Returns the created defintion based from repository columns.
     *
     * @return array
     * @psalm-suppress UndefinedInterfaceMethod
     */
    protected function createDefinitionFromRepositoryColumns(): array
    {
        if (! $this->repository() instanceof StorageRepository) {
            throw new \LogicException(
                'You need to define a definition. Only Storage Repositories with columns supports auto definitions'
            );
        }
        
        $definition = [];
        
        foreach($this->repository()->columns()->storable() as $col) {
            if ($col->getType()->isPrimary()) {
                continue;
            }
            
            if ($col->isTranslatable()) {
                foreach($col->getLocales() as $locale) {
                    $definition[$col->name()][$locale] = $this->seedColumn($col);
                }
                
                continue;
            }
            
            $definition[$col->name()] = $this->seedColumn($col);
        }

        return $definition;
    }

    /**
     * Returns the seed value for the column.
     *
     * @param ColumnInterface $column
     * @return mixed
     * @psalm-suppress UndefinedInterfaceMethod
     */
    protected function seedColumn(ColumnInterface $column): mixed
    {
        $type = $column->getType();
        $dataType = $type->type();
        
        if ($column instanceof Translatable) {
            $dataType = $column->getSubtype();
        }
        
        // check for special names first:
        $value = match ($column->name()) {
            'email' => $this->seed->email(),
            'smartphone' => $this->seed->smartphone(),
            'telephone' => $this->seed->telephone(),
            'password' => $this->seed->password(),
            default => null,
        };
        
        if (!is_null($value)) {
            return $value;
        }
        
        // next we seed based on column type:
        switch ($dataType) {
            case 'bigInt':
                return Num::int(min: 1, max: 10000);
            case 'bigPrimary':
                return Num::int(min: 1, max: 10000);
            case 'bool':
                return Arr::item([true, false]);
            case 'char':
                return Str::length(min: 1, max: $type->get('length', 5));
            case 'date':
                return $this->seed->dateTime(from: '-30 years', to: 'now')->format('Y-m-d');
            case 'datetime':
                return $this->seed->dateTime(from: '-30 years', to: 'now')->format('Y-m-d H:i:s');
            case 'decimal':
                return Num::float(min: 0, max: 1000);
            case 'double':
                return Num::float(min: 0, max: 1000);
            case 'float':
                return Num::float(min: 0, max: 1000);
            case 'int':
                return Num::int(min: 1, max: 1000);
            case 'json':
                return json_encode(['foo' => 'Foo']);
            case 'array':
                return json_encode(['foo' => 'Foo']);
            case 'primary':
                return Num::int(min: 1, max: $type->get('length', 11));
            case 'string':
                return Str::length(min: 1, max: $type->get('length', 255));
            case 'text':
                return Lorem::words(minWords: 1, maxWords: 10);
            case 'time':
                return $this->seed->dateTime(from: '-30 years', to: 'now')->format('H:i:s');
            case 'timestamp':
                return $this->seed->dateTime(from: '-30 years', to: 'now')->getTimestamp();
            case 'tinyInt':
                return Num::int(min: 1, max: 10);
        }
        
        return null;
    }
    
    /**
     * Create an entity from definition.
     *
     * @param array $definition
     * @return object
     */
    protected function createEntity(array $definition): object
    {
        $repo = $this->repository();
        
        if ($repo instanceof StorageRepository) {
            return $repo->entityFactory()->createEntityFromArray($definition);
        }
        
        return parent::createEntity($definition);
    }

    /**
     * Store an entity.
     *
     * @param array $definition
     * @return object
     */
    protected function storeEntity(array $definition): object
    {
        return $this->repository()->create($definition);
    }
}