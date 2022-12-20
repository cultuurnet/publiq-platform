<?php

namespace Publiq\ClientCredentials;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Nova\ResourceTool;

/**
 * @method static static make(string $title, string $modelClassName, string $idColumn, string $idLabel, string $secretColumn, string $secretLabel, string $environmentColumn, string $environmentLabel, string $environmentEnumClass, string $filterColumn, ?string $filterValue)
 */
final class ClientCredentials extends ResourceTool
{
    public function __construct(
        string $title,
        string $modelClassName,
        string $idColumn,
        string $idLabel,
        string $secretColumn,
        string $secretLabel,
        string $environmentColumn,
        string $environmentLabel,
        string $environmentEnumClass,
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
        if (!enum_exists($environmentEnumClass)) {
            throw new InvalidArgumentException($environmentEnumClass . ' is not an enum class');
        }

        $this->withMeta([
            'title' => $title,
            'idLabel' => $idLabel,
            'secretLabel' => $secretLabel,
            'environmentLabel' => $environmentLabel,
            'sets' => [],
        ]);

        if ($filterValue) {
            $sets = $modelClassName::query()
                ->where($filterColumn, $filterValue)
                ->get()
                ->map(fn (object $model): array => [
                    'id' => $model->{$idColumn},
                    'secret' => $model->{$secretColumn},
                    'env' => $environmentEnumClass::from($model->{$environmentColumn})->name,
                ])
                ->toArray();
            $this->withMeta(['sets' => $sets]);
        }
    }

    public function name(): string
    {
        return 'Client Credentials';
    }

    public function component(): string
    {
        return 'client-credentials';
    }
}
