<?php

namespace Publiq\InsightlyLink;

use Laravel\Nova\Fields\Field;

class InsightlyLink extends Field
{
    /**
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
        return $this->withMeta([
            'baseUrl' => 'https://crm.na1.insightly.com/details/' . $type->value . '/',
        ]);
    }
}
