<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Console\Support;

use Illuminate\Database\Eloquent\Model;
use SimbaJirira\SchemaContract\Console\Exceptions\AmbiguousModelClassException;
use SimbaJirira\SchemaContract\Console\Exceptions\UnresolvableModelClassException;

final class ModelClassResolver
{
    /**
     * @param  list<string>  $discoveredModels
     */
    public function resolve(string $input, array $discoveredModels): string
    {
        $input = trim($input);

        if ($input === '') {
            throw new UnresolvableModelClassException('A model class or short name is required.');
        }

        if (class_exists($input) && is_subclass_of($input, Model::class)) {
            return $input;
        }

        $shortNameMatches = array_values(array_filter(
            $discoveredModels,
            static fn (string $class): bool => class_basename($class) === $input,
        ));

        if (count($shortNameMatches) > 1) {
            throw new AmbiguousModelClassException($input, $shortNameMatches);
        }

        if (count($shortNameMatches) === 1) {
            return $shortNameMatches[0];
        }

        $prefixed = 'App\\Models\\'.$input;

        if (class_exists($prefixed) && is_subclass_of($prefixed, Model::class)) {
            return $prefixed;
        }

        throw new UnresolvableModelClassException(
            sprintf('Unable to resolve Eloquent model [%s].', $input),
        );
    }
}
