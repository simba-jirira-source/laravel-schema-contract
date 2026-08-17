<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Discovery;

use Illuminate\Database\Eloquent\Model;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SimbaJirira\SchemaContract\Contracts\ModelDiscoverer;
use SplFileInfo;

final class EloquentModelDiscoverer implements ModelDiscoverer
{
    /**
     * @return list<string>
     */
    public function discover(): array
    {
        /** @var list<string> $paths */
        $paths = config('schema-contract.model_paths', []);

        if ($paths === []) {
            $paths = [app_path('Models')];
        }

        /** @var list<string> $ignored */
        $ignored = config('schema-contract.ignore_models', []);

        return $this->discoverInPaths($paths, $ignored);
    }

    /**
     * @param  list<string>  $paths
     * @param  list<string>  $ignoredModels
     * @return list<string>
     */
    public function discoverInPaths(array $paths, array $ignoredModels = []): array
    {
        /** @var array<string, string> $discovered */
        $discovered = [];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            foreach ($this->phpFilesIn($path) as $file) {
                foreach ($this->qualifiedClassNamesFromFile($file) as $class) {
                    if (isset($discovered[$class])) {
                        continue;
                    }

                    if (! $this->isDiscoverableModel($class, $file, $ignoredModels)) {
                        continue;
                    }

                    $discovered[$class] = $class;
                }
            }
        }

        $models = array_values($discovered);
        sort($models);

        return $models;
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $path): array
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @return list<string>
     */
    private function qualifiedClassNamesFromFile(string $file): array
    {
        $contents = file_get_contents($file);

        if ($contents === false) {
            return [];
        }

        $namespace = null;

        if (preg_match('/^\s*namespace\s+([^;]+);/m', $contents, $match) === 1) {
            $namespace = trim($match[1]);
        }

        preg_match_all('/\b(?:abstract\s+|final\s+)*class\s+(\w+)\b/', $contents, $matches);

        if ($matches[1] === []) {
            return [];
        }

        return array_map(
            static fn (string $class): string => $namespace !== null ? $namespace.'\\'.$class : $class,
            $matches[1],
        );
    }

    /**
     * @param  list<string>  $ignoredModels
     */
    private function isDiscoverableModel(string $class, string $file, array $ignoredModels): bool
    {
        if (in_array($class, $ignoredModels, true)) {
            return false;
        }

        if (! class_exists($class)) {
            require_once $file;
        }

        if (! class_exists($class)) {
            return false;
        }

        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
            return false;
        }

        if ($reflection->isEnum()) {
            return false;
        }

        return is_subclass_of($class, Model::class);
    }
}
