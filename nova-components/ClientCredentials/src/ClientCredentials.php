<?php

namespace Publiq\ClientCredentials;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Nova\ResourceTool;

/**
 * @method static static make(string $title, string $modelClassName, array $columns, string $filterColumn, ?string $filterValue)
 */
final class ClientCredentials extends ResourceTool
{
    public function __construct(
        private readonly string $title,
        string $modelClassName,
        array $columns,
        string $filterColumn,
        ?string $filterValue,
    ) {
        parent::__construct();

        if (!class_exists($modelClassName)) {
            throw new InvalidArgumentException($modelClassName . ' class not found or does not exist');
        }
        if (!is_subclass_of($modelClassName, Model::class)) {
            throw new InvalidArgumentException($modelClassName . ' class does not extend ' . Model::class);
        }

        $this->withMeta([
            'title' => $title,
            'headers' => array_values($columns),
            'rows' => [],
        ]);

        if ($filterValue) {
            $rows = $modelClassName::query()
                ->where($filterColumn, $filterValue)
                ->get()
                ->map(
                    static fn (object $model): array => array_values(
                        array_map(
                            static fn (string $column): string => (string) $model->{$column},
                            array_keys($columns)
                        )
                    )
                )
                ->toArray();
            $this->withMeta(['rows' => $rows]);
        }
    }

    public function name(): string
    {
        return $this->title;
    }

    public function component(): string
    {
        return 'client-credentials';
    }
}
