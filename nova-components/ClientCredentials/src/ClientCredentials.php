<?php

namespace Publiq\ClientCredentials;

use Laravel\Nova\ResourceTool;

/**
 * @method static static make(string $title, string $idLabel, string $secretLabel, string $environmentLabel)
 */
final class ClientCredentials extends ResourceTool
{
    private array $sets = [];

    public function __construct(
        string $title,
        string $idLabel,
        string $secretLabel,
        string $environmentLabel
    ) {
        parent::__construct();

        $this->withMeta([
            'title' => $title,
            'idLabel' => $idLabel,
            'secretLabel' => $secretLabel,
            'environmentLabel' => $environmentLabel,
        ]);
    }

    public function withSet(string $environment, string $id, string $secret): self
    {
        $this->sets[] = [
            'env' => $environment,
            'id' => $id,
            'secret' => $secret,
        ];
        $this->withMeta(['sets' => $this->sets]);
        return $this;
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
