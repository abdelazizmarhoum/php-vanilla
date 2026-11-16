<?php
declare(strict_types=1);

namespace App\Core;

use League\CommonMark\CommonMarkConverter;

final class Markdown
{
    private function __construct()
    {
    }

    /**
     * The output is safe to echo directly into HTML — html_input=escape prevents user-supplied tags from rendering.
     */
    public static function toHtml(string $markdown): string
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);

        return (string) $converter->convert($markdown);
    }
}
