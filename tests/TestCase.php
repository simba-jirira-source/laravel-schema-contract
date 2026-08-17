<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use SimbaJirira\SchemaContract\SchemaContractServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SchemaContractServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
            'use_native_json' => true,
        ]);

        $app['config']->set('database.connections.analytics', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
            'use_native_json' => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();
        Schema::connection('analytics')->dropAllTables();
    }

    protected function createSchemaInspectionTables(): void
    {
        Schema::create('schema_inspection_profiles', function (Blueprint $table): void {
            $table->id();
            $table->boolean('active')->default(true);
            $table->integer('quantity');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('label', 100)->default('draft');
            $table->text('bio')->nullable();
            $table->json('payload')->nullable();
            $table->date('starts_on')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('legacy_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
        });

        Schema::connection('analytics')->create('remote_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
    }
}
