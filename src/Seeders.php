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
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Autowire\AutowireException;

/**
 * Seeders
 */
class Seeders implements SeedersInterface
{
    /**
     * @var array<string, string|SeederInterface>
     */
    protected array $seeders = [];
    
    /**
     * Create a new AbstractFactory.
     *
     * @param array $replaces
     */
    public function __construct(
        protected ContainerInterface $container,
    ) {}   

    /**
     * Add a seeder.
     *
     * @param string $name
     * @param string|SeederInterface $seeder
     * @return static $this
     */
    public function addSeeder(string $name, string|SeederInterface $seeder): static
    {
        $this->seeders[$name] = $seeder;
        return $this;
    }
    
    /**
     * Returns true if seeder exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function hasSeeder(string $name): bool
    {
        return isset($this->seeders[$name]);
    }
    
    /**
     * Returns the seeder.
     *
     * @param string $name
     * @return SeederInterface
     * @throws SeedingException
     */
    public function getSeeder(string $name): SeederInterface
    {
        if (! $this->hasSeeder($name)) {
            throw new SeedingException(sprintf('Seeder %s not found', $name));
        }
        
        return $this->seeders[$name] = $this->createSeeder($this->seeders[$name]);
    }
    
    /**
     * Returns all seeder names.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_keys($this->seeders);
    }    
    
    /**
     * Create a seeder.
     *
     * @param string|SeederInterface $seeder
     * @return SeederInterface
     * @throws SeedingException
     */
    protected function createSeeder(string|SeederInterface $seeder): SeederInterface
    {
        if (!is_string($seeder)) {
            return $seeder;
        }
        
        try {
            $seeder = (new Autowire($this->container))->resolve($seeder);
        } catch (AutowireException $e) {
            throw new SeedingException($e->getMessage(), (int)$e->getCode(), $e);
        }
        
        if ($seeder instanceof SeederInterface) {
            return $seeder;
        }
        
        throw new SeedingException(
            sprintf('Seeder %s must be an instanceof %s', $seeder::class, SeederInterface::class)
        );
    }
}