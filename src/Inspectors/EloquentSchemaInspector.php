<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Inspectors;

use Illuminate\Support\Facades\Schema;
use SimbaJirira\SchemaContract\Contracts\SchemaInspector;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\DTO\TableDefinition;
use SimbaJirira\SchemaContract\Exceptions\MissingTableException;
use SimbaJirira\SchemaContract\Support\DatabaseColumnNormalizer;
use SimbaJirira\SchemaContract\Support\SchemaColumnMetadataFactory;

final class EloquentSchemaInspector implements SchemaInspector
{
    public function __construct(
        private readonly SchemaColumnMetadataFactory $metadataFactory = new SchemaColumnMetadataFactory,
        private readonly DatabaseColumnNormalizer $columnNormalizer = new DatabaseColumnNormalizer,
    ) {}

    public function inspect(ModelDefinition $model): TableDefinition
    {
        $schema = Schema::connection($model->connection);

        if (! $schema->hasTable($model->table)) {
            throw new MissingTableException(
                modelClass: $model->modelClass,
                connection: $model->connection,
                table: $model->table,
            );
        }

        /** @var list<ColumnDefinition> $columns */
        $columns = [];

        foreach ($schema->getColumns($model->table) as $column) {
            $columns[] = $this->columnNormalizer->normalize(
                $this->metadataFactory->make(
                    $column,
                    $schema->getConnection()->getDriverName(),
                ),
            );
        }

        return new TableDefinition(
            name: $model->table,
            connection: $model->connection,
            columns: $columns,
        );
    }
}
