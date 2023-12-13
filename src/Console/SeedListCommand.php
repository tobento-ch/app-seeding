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

namespace Tobento\App\Seeding\Console;

use Tobento\App\Seeding\SeedersInterface;
use Tobento\Service\Console\AbstractCommand;
use Tobento\Service\Console\InteractorInterface;

class SeedListCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        seed:list | List the seeder names
    ';
    
    /**
     * Handle the command.
     *
     * @param InteractorInterface $io
     * @param SeedersInterface $seeders
     * @return int The exit status code: 
     *     0 SUCCESS
     *     1 FAILURE If some error happened during the execution
     *     2 INVALID To indicate incorrect command usage e.g. invalid options
     */
    public function handle(InteractorInterface $io, SeedersInterface $seeders): int
    {
        $io->table(
            headers: ['Seeder Name'],
            rows: [$seeders->names()],
        );
        
        return 0;
    }
}