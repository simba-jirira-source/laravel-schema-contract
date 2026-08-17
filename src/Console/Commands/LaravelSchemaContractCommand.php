<?php

declare(strict_types=1);

namespace LaravelSchemaContract\LaravelSchemaContract\Console\Commands;

use Illuminate\Console\Command;

class LaravelSchemaContractCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'laravel-schema-contract:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package laravel-schema-contract.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('LaravelSchemaContract placeholder command executed.');

        return self::SUCCESS;
    }
}
