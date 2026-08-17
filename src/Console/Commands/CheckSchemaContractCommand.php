<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Console\Commands;

use Illuminate\Console\Command;
use SimbaJirira\SchemaContract\Analysis\ContractAnalyzer;
use SimbaJirira\SchemaContract\Console\Exceptions\AmbiguousModelClassException;
use SimbaJirira\SchemaContract\Console\Exceptions\UnresolvableModelClassException;
use SimbaJirira\SchemaContract\Console\Rendering\AnalysisConsoleRenderer;
use SimbaJirira\SchemaContract\Console\Support\ModelClassResolver;
use SimbaJirira\SchemaContract\Discovery\EloquentModelDiscoverer;
use SimbaJirira\SchemaContract\Rules\RuleRegistry;
use Throwable;

final class CheckSchemaContractCommand extends Command
{
    public const int EXIT_CONTRACT_ERRORS = 1;

    public const int EXIT_RUNTIME_FAILURE = 2;

    protected $signature = 'schema-contract:check
                            {model? : The Eloquent model class or short name to analyze}';

    protected $description = 'Check Eloquent model casts against database schema contracts.';

    public function handle(
        EloquentModelDiscoverer $modelDiscoverer,
        ModelClassResolver $modelClassResolver,
        AnalysisConsoleRenderer $renderer,
    ): int {
        try {
            $modelClasses = $this->resolveModelClasses($modelDiscoverer, $modelClassResolver);

            if ($modelClasses === []) {
                $this->warn('No Eloquent models discovered for schema contract analysis.');

                return self::SUCCESS;
            }

            $analyzer = new ContractAnalyzer(
                ruleRegistry: RuleRegistry::withDefaults(),
                ignoreColumns: $this->ignoreColumns(),
            );

            $analysis = $analyzer->analyzeModels($modelClasses);

            foreach ($analysis->results as $result) {
                $renderer->render($this->output, $result, $this->ignoreColumns());
            }

            $renderer->renderSummary($this->output, $analysis->summary);

            return $analysis->hasErrors() ? self::EXIT_CONTRACT_ERRORS : self::SUCCESS;
        } catch (AmbiguousModelClassException|UnresolvableModelClassException $exception) {
            $this->error($exception->getMessage());

            return self::EXIT_RUNTIME_FAILURE;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::EXIT_RUNTIME_FAILURE;
        }
    }

    /**
     * @return list<string>
     */
    private function resolveModelClasses(
        EloquentModelDiscoverer $modelDiscoverer,
        ModelClassResolver $modelClassResolver,
    ): array {
        $modelArgument = $this->argument('model');

        if (! is_string($modelArgument) || trim($modelArgument) === '') {
            return $modelDiscoverer->discover();
        }

        return [
            $modelClassResolver->resolve($modelArgument, $modelDiscoverer->discover()),
        ];
    }

    /**
     * @return list<string>
     */
    private function ignoreColumns(): array
    {
        /** @var mixed $configured */
        $configured = config('schema-contract.ignore_columns', []);

        if (! is_array($configured) || $configured === []) {
            return [];
        }

        $columns = [];

        foreach ($configured as $value) {
            if (! is_array($value)) {
                continue;
            }

            foreach ($value as $column) {
                if (is_string($column)) {
                    $columns[] = $column;
                }
            }
        }

        return array_values(array_unique($columns));
    }
}
