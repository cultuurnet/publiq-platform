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
    public static function build(array $mails): self
    {
        $collection = new self();

        foreach ($mails as $type => $config) {
            $collection->put($type, new Template($type, (int)$config['id'], $config['enabled'], $config['subject']));
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
