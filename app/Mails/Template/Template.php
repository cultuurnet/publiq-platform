<?php

declare(strict_types=1);

namespace App\Mails\Template;

final class Template
{
    public function __construct(
        public string $type,
        public int $id,
        public bool $enabled,
        public string $subject
    ) {
    }
}