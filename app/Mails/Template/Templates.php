<?php

declare(strict_types=1);

namespace App\Mails\Template;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @extends Collection<string, Template>
 */
final class Templates extends Collection
{
    public static function build(array $templates): self
    {
        $collection = new self();

        foreach ($templates as $type => $template) {
            $collection->put($type, new Template($type, (int)$template['id'], $template['enabled'], $template['subject']));
        }

        return $collection;
    }

    public function getOrFail(string $key): Template
    {
        $template = $this->get($key);
        if ($template instanceof Template) {
            return $template;
        }

        throw new InvalidArgumentException(sprintf('Invalid mail template %s.', $key));
    }
}
