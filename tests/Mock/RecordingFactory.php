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

namespace Tobento\App\Seeding\Test\Mock;

use ArrayObject;
use Tobento\App\Seeding\FactoryInterface;
use Throwable;

final class RecordingFactory implements FactoryInterface
{
    public int $amount = 1;

    public null|Throwable $throw = null;

    public function __construct(
        private ArrayObject $log,
    ) {}

    public static function new(array $replaces = []): static
    {
        return new static(new ArrayObject());
    }

    public function definition(): array
    {
        $this->log->append('factory.definition');

        return ['foo' => 'bar'];
    }

    public function times(int $amount): static
    {
        $this->log->append('factory.times');
        $this->amount = $amount;

        return $this;
    }

    public function create(): array
    {
        $this->log->append('factory.create');

        if ($this->throw !== null) {
            throw $this->throw;
        }

        return array_map(
            static fn (int $i): object => new \stdClass(),
            range(1, $this->amount)
        );
    }

    public function createOne(): object
    {
        $this->log->append('factory.createOne');

        if ($this->throw !== null) {
            throw $this->throw;
        }

        return new \stdClass();
    }

    public function make(): array
    {
        $this->log->append('factory.make');

        return [new \stdClass()];
    }

    public function makeOne(): object
    {
        $this->log->append('factory.makeOne');

        return new \stdClass();
    }

    public function raw(): array
    {
        $this->log->append('factory.raw');

        return [['foo' => 'bar']];
    }

    public function rawOne(): array
    {
        $this->log->append('factory.rawOne');

        return ['foo' => 'bar'];
    }
}