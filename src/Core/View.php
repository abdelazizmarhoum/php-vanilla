<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

// Caller is responsible for escaping user data with e().
final class View
{
    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function render(
        string $view,
        array $data = [],
        string $layout = 'layouts/base',
    ): Response {
        $viewsPath = dirname(__DIR__, 2) . '/views';
        $viewFile = $viewsPath . '/' . $view . '.php';
        $layoutFile = $viewsPath . '/' . $layout . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException('View file not found: ' . $view);
        }

        if (!is_file($layoutFile)) {
            throw new RuntimeException('Layout file not found: ' . $layout);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($content === false) {
            throw new RuntimeException('Failed to render view: ' . $view);
        }

        $csrf = CSRF::token();
        ob_start();
        require $layoutFile;
        $html = ob_get_clean();

        if ($html === false) {
            throw new RuntimeException('Failed to render layout: ' . $layout);
        }

        return (new Response())->html($html);
    }
}
