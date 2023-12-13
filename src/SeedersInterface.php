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

/**
 * SeedersInterface
 */
interface SeedersInterface
{
    /**
     * Add a seeder.
     *
     * @param string $name
     * @param string|SeederInterface $seeder
     * @return static $this
     */
    public function addSeeder(string $name, string|SeederInterface $seeder): static;
    
    /**
     * Returns true if seeder exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function hasSeeder(string $name): bool;
    
    /**
     * Returns the seeder.
     *
     * @param string $name
     * @return SeederInterface
     * @throws SeedingException
     */
    public function getSeeder(string $name): SeederInterface;
    
    /**
     * Returns all seeder names.
     *
     * @return array<int, string>
     */
    public function names(): array;
}