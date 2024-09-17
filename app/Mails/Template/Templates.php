<?php

declare(strict_types=1);

namespace App\Mails\Template;

use http\Exception\InvalidArgumentException;
use Illuminate\Support\Collection;

/**
 * @extends Collection<string, Template>
 */
final class Templates extends Collection
{
    public const INTEGRATION_CREATED = 'integration_created';
    public const INTEGRATION_ACTIVATION_REMINDER = 'integration_activation_reminder';
    public const INTEGRATION_BLOCKED = 'integration_blocked';
    public const INTEGRATION_ACTIVATED = 'integration_activated';

    public static function build(array $mails): self
    {
        $collection = new self();

        foreach ($mails as $type => $config) {
            $collection->put($type, new Template($type, $config['id'], $config['enabled'], $config['subject']));
        }

        return $collection;
    }

    public function getOrFail($key, $default = null): Template
    {
        $template = parent::get($key, $default);
        if ($template instanceof Template) {
            return $template;
        }

        throw new InvalidArgumentException(sprintf('Invalid mail template %s.', $key));
    }
}
