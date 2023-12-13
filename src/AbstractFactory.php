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

namespace Tobento\App\Seeding;

use Psr\Container\ContainerInterface;
use Tobento\Service\Seeder\SeedInterface;
use Tobento\Service\Seeder\Str;
use Tobento\Service\Seeder\Arr;
use Tobento\Service\Iterable\ItemFactoryIterator;
use Tobento\Service\Iterable\Iter;
use Tobento\Service\HelperFunction\Functions;
use Closure;

/**
 * AbstractFactory
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var SeedInterface $seed
     */
    protected SeedInterface $seed;
    
    /**
     * @var array<array-key, Closure>
     */
    protected array $modifiers = [];
    
    /**
     * @var array<array-key, Closure>
     */
    protected array $entityModifiers = [];
    
    /**
     * @var positive-int
     */
    protected int $amount = 1;
    
    /**
     * Create a new AbstractFactory.
     *
     * @param array $replaces
     */
    public function __construct(
        array $replaces = [],
    ) {
        $this->seed = $this->getService(SeedInterface::class);
        
        if (!empty($replaces)) {
            $this->modify(static fn () => $replaces);
        }
    }
    
    /**
     * Create a new instance.
     *
     * @param array $replaces
     */
    public static function new(array $replaces = []): static
    {
        return new static(replaces: $replaces);
    }
    
    /**
     * Returns the definition.
     *
     * @return array
     */
    abstract public function definition(): array;    

    /**
     * How many entities should be created.
     *
     * @param positive-int $amount
     * @return static $this
     */
    public function times(int $amount): static
    {
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Returns the created entities with storing.
     *
     * @return array
     */
    public function create(): array
    {
        $iterator = new ItemFactoryIterator(
            factory: [$this, 'storeEntityFromDefinition'],
            create: $this->amount,
        );
        
        return Iter::toArray(iterable: $iterator);
    }
    
    /**
     * Returns the created entity with storing.
     *
     * @return object
     */
    public function createOne(): object
    {
        $iterator = new ItemFactoryIterator(
            factory: [$this, 'storeEntityFromDefinition'],
            create: 1,
        );
        
        return Iter::toArray(iterable: $iterator)[0];
    }
    
    /**
     * Returns the made entities without storing.
     *
     * @return array
     */
    public function make(): array
    {
        $iterator = new ItemFactoryIterator(
            factory: [$this, 'createEntityFromDefinition'],
            create: $this->amount,
        );
        
        return Iter::toArray(iterable: $iterator);
    }
    
    /**
     * Returns the made entity without storing.
     *
     * @return object
     */
    public function makeOne(): object
    {
        $iterator = new ItemFactoryIterator(
            factory: [$this, 'createEntityFromDefinition'],
            create: 1,
        );
        
        return Iter::toArray(iterable: $iterator)[0];
    }
    
    /**
     * Returns the raw attributes without storing.
     *
     * @return array
     */
    public function raw(): array
    {
        $iterator = new ItemFactoryIterator(
            factory: [$this, 'createRawFromDefinition'],
            create: $this->amount,
        );
        
        return Iter::toArray(iterable: $iterator);
    }
    
    /**
     * Returns the raw attributes without storing.
     *
     * @return array
     */
    public function rawOne(): array
    {
        $iterator = new ItemFactoryIterator(
            factory: [$this, 'createRawFromDefinition'],
            create: 1,
        );
        
        return Iter::toArray(iterable: $iterator)[0];
    }
    
    /**
     * Add a definition modifier.
     *
     * @param Closure $modifier
     * @return static $this
     */
    public function modify(Closure $modifier): static
    {
        $this->modifiers[] = $modifier;
        return $this;
    }
    
    /**
     * Add an entity modifier.
     *
     * @param Closure $modifier
     * @return static $this
     */
    public function modifyEntity(Closure $modifier): static
    {
        $this->entityModifiers[] = $modifier;
        return $this;
    }
    
    /**
     * Create an entity from definition.
     *
     * @param array $definition
     * @return object
     */
    protected function createEntity(array $definition): object
    {
        return (object)$definition;
    }
    
    /**
     * Store an entity.
     *
     * @param array $definition
     * @return object
     */
    protected function storeEntity(array $definition): object
    {
        return $this->createEntity($definition);
    }
    
    /**
     * Create an entity from definition.
     *
     * @return object
     */
    public function createEntityFromDefinition(): object
    {
        $definition = $this->applyModifiers($this->definition());
        
        return $this->applyEntityModifiers($this->createEntity($definition));
    }
    
    /**
     * Create raw from definition.
     *
     * @return array
     */
    public function createRawFromDefinition(): array
    {
        return $this->applyModifiers($this->definition());
    }

    /**
     * Store an entity from definition.
     *
     * @return object
     */
    public function storeEntityFromDefinition(): object
    {
        $definition = $this->applyModifiers($this->definition());
            
        return $this->applyEntityModifiers($this->storeEntity($definition));
    }
    
    /**
     * Applies the definition modifiers.
     *
     * @param array $definition
     * @return array
     */
    protected function applyModifiers(array $definition): array
    {
        foreach($this->modifiers as $modifier) {
            $definition = array_merge(
                $definition,
                $modifier($this->seed, $definition)
            );
        }
        
        return $definition;
    }
    
    /**
     * Applies the entity modifiers.
     *
     * @param object $entity
     * @return object
     */
    protected function applyEntityModifiers(object $entity): object
    {
        foreach($this->entityModifiers as $modifier) {
            $entity = $modifier($this->seed, $entity);
        }
        
        return $entity;
    }
    
    /**
     * Returns a service by name.
     *
     * @param string $name
     * @return mixed
     */
    protected function getService(string $name): mixed
    {
        return Functions::get(ContainerInterface::class)->get($name);
    }
}