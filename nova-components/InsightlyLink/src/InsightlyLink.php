<?php

namespace Publiq\InsightlyLink;

use Laravel\Nova\Fields\Field;

class InsightlyLink extends Field
{
    private string $baseUrl = 'https://crm.na1.insightly.com/details/%s/';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'insightly-link';

    public function __construct($name, $attribute, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->onlyOnDetail();
        $this->readonly();
    }

    public function type(InsightlyType $type): self
    {
        $baseUrl = sprintf(
            $this->baseUrl,
            strtolower($type->value)
        );

        return $this->withMeta([
            'baseUrl' => $baseUrl,
        ]);
    }
}
