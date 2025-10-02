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

use Closure;
use Tobento\App\Seeding\FactoryInterface;
use Tobento\Service\Repository\WriteRepositoryInterface;

/**
 * RepositoryFactory
 */
class RepositoryFactory
{
    /**
     * Create a new repository seed factory.
     *
     * @param string|WriteRepositoryInterface $repository
     * @param null|Closure $definition
     * @param array $replaces
     */
    public static function new(
        string|WriteRepositoryInterface $repository,
        null|Closure $definition = null,
        array $replaces = [],
    ): FactoryInterface {
        return new class($repository, $definition, $replaces) extends AbstractFactory {
            //
        };
    }
}