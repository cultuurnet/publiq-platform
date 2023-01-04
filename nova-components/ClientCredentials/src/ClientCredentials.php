<?php

namespace Publiq\ClientCredentials;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Nova\ResourceTool;

/**
 * @method static static make(string $title, string $modelClassName, array $columns, string $filterColumn, ?string $filterValue, ?string $actionLabel = null, ?callable $actionUrlCallback = null)
 */
final class ClientCredentials extends ResourceTool
{
    public function __construct(
        private readonly string $title,
        string $modelClassName,
        array $columns,
        string $filterColumn,
        ?string $filterValue,
        ?string $actionLabel = null,
        ?callable $actionUrlCallback = null
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
            'actionLabel' => $actionLabel,
            'actionUrls' => [],
        ]);

        $models = new Collection();
        if ($filterValue) {
            $models = $modelClassName::query()
                ->where($filterColumn, $filterValue)
                ->get();
        }

        $rows = $models
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

        if ($actionUrlCallback) {
            $actionUrls = $models
                ->map($actionUrlCallback)
                ->toArray();
            $this->withMeta(['actionUrls' => $actionUrls]);
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
