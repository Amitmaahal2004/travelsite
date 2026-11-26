<?php
declare(strict_types=1);

namespace Core;

/**
 * View Renderer
 */
class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'frontend'): void
    {
        $viewPath = BASE_PATH . '/views/' . ltrim($view, '/') . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutPath = BASE_PATH . '/views/layouts/' . $layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout not found: {$layout}");
            }
            require $layoutPath;
            return;
        }

        echo $content;
    }

    public static function partial(string $partial, array $data = []): string
    {
        $path = BASE_PATH . '/views/partials/' . ltrim($partial, '/') . '.php';
        if (!file_exists($path)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
