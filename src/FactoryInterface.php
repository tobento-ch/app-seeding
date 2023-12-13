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
 * FactoryInterface
 */
interface FactoryInterface
{
    /**
     * Create a new instance.
     *
     * @param array $replaces
     */
    public static function new(array $replaces = []): static;
    
    /**
     * Returns the definition.
     *
     * @return array
     */
    public function definition(): array;

    /**
     * How many entities should be created.
     *
     * @param positive-int $amount
     */
    public function times(int $amount): static;
    
    /**
     * Returns the created entities with storing.
     *
     * @return array
     */
    public function create(): array;
    
    /**
     * Returns the created entity with storing.
     *
     * @return object
     */
    public function createOne(): object;
    
    /**
     * Returns the made entities without storing.
     *
     * @return array
     */
    public function make(): array;
    
    /**
     * Returns the made entity without storing.
     *
     * @return object
     */
    public function makeOne(): object;
    
    /**
     * Returns the raw attributes without storing.
     *
     * @return array
     */
    public function raw(): array;
    
    /**
     * Returns the raw attributes without storing.
     *
     * @return array
     */
    public function rawOne(): array;
}