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
use Tobento\App\Seeding\SeederInterface;
use Tobento\Service\Console\AbstractCommand;
use Tobento\Service\Console\InteractorInterface;
use Tobento\Service\Support\Arrayable;
use JsonSerializable;

class SeedCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        seed | Runs the seeders
        {--N|name[] : Runs only the specific seeders}
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
        $name = $io->option(name: 'name');
        
        if (is_array($name) && !empty($name)) {
            $seederNames = $name;
        } else {
            $seederNames = $seeders->names();
        }

        foreach($seederNames as $seederName) {
            
            if (! $seeders->hasSeeder($seederName)) {
                $io->error(sprintf('Seeder %s not found', $seederName));
                continue;
            }
            
            $seeder = $seeders->getSeeder($seederName);
            
            $io->info(sprintf('Seeder %s starting', $seederName));
            
            $this->runSeeder($io, $seeder);
            
            $io->info(sprintf('Seeder %s finished', $seederName));
        }
        
        return 0;
    }

    /**
     * Run the seeder.
     *
     * @param InteractorInterface $io
     * @param SeederInterface $seeder
     * @return void
     */    
    protected function runSeeder(InteractorInterface $io, SeederInterface $seeder): void
    {
        foreach($seeder->run() as $item) {
            if ($io->isVerbose('v')) {
                $io->write($this->itemToJsonString($item));
                $io->newLine();
            }
        }
    }
    
    /**
     * Itme to json string.
     *
     * @param mixed $item
     * @return string
     */    
    protected function itemToJsonString(mixed $item): string
    {
        $array = match (true) {
            is_array($item) => $item,
            $item instanceof JsonSerializable => $item->jsonSerialize(),
            $item instanceof Arrayable => $item->toArray(),
            is_object($item) => (array)$item,
            default => [],
        };
        
        return json_encode($array, JSON_PRETTY_PRINT);
    }
}