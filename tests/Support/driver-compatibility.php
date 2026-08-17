<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\TableDefinition;

function skipUnlessDatabaseDriver(string $expected): void
{
    if (env('SCHEMA_CONTRACT_DB_DRIVER') !== $expected) {
        test()->markTestSkipped("Requires SCHEMA_CONTRACT_DB_DRIVER={$expected}");
    }
}

function configureDriverTestingConnection(string $driver): string
{
    $connection = 'driver_testing';

    $config = match ($driver) {
        'mysql', 'mariadb' => [
            'driver' => $driver,
            'host' => env('SCHEMA_CONTRACT_MYSQL_HOST', '127.0.0.1'),
            'port' => env('SCHEMA_CONTRACT_MYSQL_PORT', '3306'),
            'database' => env('SCHEMA_CONTRACT_MYSQL_DATABASE', 'schema_contract'),
            'username' => env('SCHEMA_CONTRACT_MYSQL_USERNAME', 'root'),
            'password' => env('SCHEMA_CONTRACT_MYSQL_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('SCHEMA_CONTRACT_PGSQL_HOST', '127.0.0.1'),
            'port' => env('SCHEMA_CONTRACT_PGSQL_PORT', '5432'),
            'database' => env('SCHEMA_CONTRACT_PGSQL_DATABASE', 'schema_contract'),
            'username' => env('SCHEMA_CONTRACT_PGSQL_USERNAME', 'postgres'),
            'password' => env('SCHEMA_CONTRACT_PGSQL_PASSWORD', 'postgres'),
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],
        default => throw new InvalidArgumentException("Unsupported driver [{$driver}]"),
    };

    config(["database.connections.{$connection}" => $config]);

    try {
        DB::connection($connection)->getPdo();
    } catch (Throwable $exception) {
        test()->markTestSkipped("{$driver} connection unavailable: {$exception->getMessage()}");
    }

    return $connection;
}

function createDriverCompatibilityTables(string $connection, string $driver): void
{
    Schema::connection($connection)->dropIfExists('driver_compatibility_profiles');

    Schema::connection($connection)->create('driver_compatibility_profiles', function (Blueprint $table) use ($driver): void {
        $table->boolean('active')->default(true);
        $table->smallInteger('small_count');
        $table->integer('count');
        $table->bigInteger('big_count');
        $table->decimal('amount', 10, 2);
        $table->float('ratio');
        $table->double('precise_ratio');
        $table->string('code', 36);
        $table->text('bio');

        if ($driver === 'pgsql') {
            $table->jsonb('payload')->nullable();
        } else {
            $table->json('payload')->nullable();
        }

        $table->uuid('external_id')->nullable();
        $table->date('starts_on')->nullable();
        $table->dateTime('published_at')->nullable();
        $table->timestamp('archived_at')->nullable();
        $table->timestamps();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $table->enum('status', ['active', 'inactive'])->nullable();
        } else {
            $table->string('status')->nullable();
        }
    });
}

function driverCompatibilityColumn(TableDefinition $table, string $name): ColumnDefinition
{
    foreach ($table->columns as $column) {
        if ($column->name === $name) {
            return $column;
        }
    }

    throw new InvalidArgumentException("Column [{$name}] was not found on table [{$table->name}].");
}
