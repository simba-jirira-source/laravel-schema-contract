<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Rules;

use SimbaJirira\SchemaContract\Contracts\ContractRule;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ContractViolation;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;

final class RuleRegistry
{
    /** @var list<ContractRule> */
    private array $rules;

    /**
     * @param  list<ContractRule>  $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public static function withDefaults(): self
    {
        return new self([
            new CastMatchesColumnTypeRule,
            new DecimalScaleMatchesRule,
            new JsonColumnHasCompatibleCastRule,
            new DateColumnHasCompatibleCastRule,
        ]);
    }

    public function register(ContractRule $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @return list<ContractRule>
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * @return list<ContractViolation>
     */
    public function analyze(ModelDefinition $model, ColumnDefinition $column): array
    {
        $violations = [];

        foreach ($this->rules as $rule) {
            array_push($violations, ...$rule->analyze($model, $column));
        }

        return $violations;
    }
}
