<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Model Discovery Paths
    |--------------------------------------------------------------------------
    |
    | Directories searched recursively for concrete Eloquent models. When
    | empty, discovery falls back to app_path('Models').
    |
    */
    'model_paths' => [
        app_path('Models'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Models
    |--------------------------------------------------------------------------
    |
    | Fully-qualified Eloquent model class names excluded from discovery and
    | bulk schema-contract checks. Targeted analysis by FQCN still works.
    |
    */
    'ignore_models' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Columns
    |--------------------------------------------------------------------------
    |
    | Table-specific columns skipped during contract analysis. Keys are database
    | table names; values are lists of column names excluded from rule checks.
    |
    | Example:
    | 'users' => ['password', 'remember_token'],
    |
    */
    'ignore_columns' => [
        //
    ],

];
