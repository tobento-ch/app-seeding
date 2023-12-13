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

use Tobento\App\Seeding\SeederInterface;

final class Seeder implements SeederInterface
{
    public function __construct(
        private array $items = []
    ) {}
    
    public function run(): \Generator
    {
        yield $this->items;
    }
}