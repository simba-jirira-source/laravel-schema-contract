<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Inspectors;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use SimbaJirira\SchemaContract\Contracts\ModelInspector;
use SimbaJirira\SchemaContract\DTO\CastDefinition;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\Support\CastNormalizer;

final class EloquentModelInspector implements ModelInspector
{
    public function __construct(
        private readonly CastNormalizer $castNormalizer = new CastNormalizer,
    ) {}

    public function inspect(string $modelClass): ModelDefinition
    {
        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException("Model class [{$modelClass}] does not exist.");
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("Class [{$modelClass}] is not an Eloquent model.");
        }

        /** @var Model $model */
        $model = new $modelClass;

        /** @var array<string, CastDefinition> $casts */
        $casts = [];

        foreach ($model->getCasts() as $column => $cast) {
            $casts[$column] = $this->castNormalizer->normalize($column, $cast);
        }

        return new ModelDefinition(
            modelClass: $modelClass,
            connection: $this->resolveConnectionName($model),
            table: $model->getTable(),
            primaryKey: $model->getKeyName(),
            casts: $casts,
        );
    }

    private function resolveConnectionName(Model $model): string
    {
        $connection = $model->getConnectionName() ?? $model->getConnection()->getName();

        if (! is_string($connection) || $connection === '') {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve database connection for model [%s].',
                $model::class,
            ));
        }

        return $connection;
    }
}
