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

use Closure;

/**
 * Wraps a FactoryInterface, running $before immediately before and $after
 * immediately after every storing call (create/createOne), so ambient state
 * (e.g. ACL permissions/current user) needed only for the moment of writing
 * can be granted and reliably restored — even across multiple calls on the
 * same factory instance. make()/makeOne()/raw()/rawOne() never store, so
 * they pass through unwrapped.
 */
final class CallbackWrappingFactory implements FactoryInterface
{
    /**
     * Create a new CallbackWrappingFactory.
     *
     * @param FactoryInterface $factory The factory to wrap.
     * @param Closure(): void $before Called before storing.
     * @param Closure(): void $after Called after storing, even if it fails.
     */
    public function __construct(
        private FactoryInterface $factory,
        private Closure $before,
        private Closure $after,
    ) {}

    /**
     * Not supported, use the constructor instead.
     *
     * @param array<array-key, mixed> $replaces
     * @return static
     * @throws \LogicException Always.
     */
    public static function new(array $replaces = []): static
    {
        throw new \LogicException(sprintf(
            '%s must be constructed with an existing factory and callbacks — use the constructor directly.',
            static::class
        ));
    }

    /**
     * Returns the definition of the wrapped factory.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return $this->factory->definition();
    }

    /**
     * Sets the number of items to create or make.
     *
     * @param positive-int $amount
     * @return static
     */
    public function times(int $amount): static
    {
        $this->factory->times($amount);
        return $this;
    }

    /**
     * Creates and stores the items, wrapped by the callbacks.
     *
     * @return array<int, object> The created items.
     */
    public function create(): array
    {
        ($this->before)();
        try {
            return $this->factory->create();
        } finally {
            ($this->after)();
        }
    }

    /**
     * Creates and stores a single item, wrapped by the callbacks.
     *
     * @return object The created item.
     */
    public function createOne(): object
    {
        ($this->before)();
        try {
            return $this->factory->createOne();
        } finally {
            ($this->after)();
        }
    }

    /**
     * Makes the items without storing them.
     *
     * @return array<int, object> The made items.
     */
    public function make(): array
    {
        return $this->factory->make();
    }

    /**
     * Makes a single item without storing it.
     *
     * @return object The made item.
     */
    public function makeOne(): object
    {
        return $this->factory->makeOne();
    }

    /**
     * Returns the raw attributes without storing them.
     *
     * @return array<int, array<string, mixed>> The raw attributes of each item.
     */
    public function raw(): array
    {
        return $this->factory->raw();
    }

    /**
     * Returns the raw attributes of a single item without storing it.
     *
     * @return array<string, mixed> The raw attributes.
     */
    public function rawOne(): array
    {
        return $this->factory->rawOne();
    }
}