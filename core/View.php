<?php

declare(strict_types=1);

namespace DoubleE\Core;

class View
{
    private string $viewsPath;

    public function __construct(?string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?? (Application::getInstance()?->rootPath() . '/views');
    }

    /**
     * Render a view template within a layout.
     */
    public function render(string $template, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $content = $this->renderTemplate($template, $data);

        if ($layout !== null) {
            $data['content'] = $content;
            return $this->renderTemplate($layout, $data);
        }

        return $content;
    }

    /**
     * Render a partial template (no layout).
     */
    public function partial(string $template, array $data = []): string
    {
        return $this->renderTemplate($template, $data);
    }

    /**
     * Render a template file and return the output.
     */
    private function renderTemplate(string $template, array $data): string
    {
        $file = $this->viewsPath . '/' . $template . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("View template not found: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        try {
            require $file;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Escape a string for safe HTML output.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/**
 * Global helper for escaping output.
 */
function e(mixed $value): string
{
    return View::e($value);
}
