<?php
/**
 * AeroCanada v2 — Template Rendering Engine
 * Simple PHP-based view rendering with layout support.
 */

namespace AeroCanada\Core;

class View
{
    private static string $layoutDir;
    private static string $viewDir;

    public static function init(): void
    {
        self::$layoutDir = __DIR__ . '/../templates/layouts/';
        self::$viewDir   = __DIR__ . '/../templates/views/';
    }

    /**
     * Render a view inside a layout.
     */
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        self::init();

        // Make data available as variables
        extract($data);

        // Capture view content
        ob_start();
        $viewFile = self::$viewDir . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "View not found: $view";
        }
        $__content = ob_get_clean();

        // Page title
        $__title = $data['title'] ?? 'AeroCanada ERP';

        // Render layout
        $layoutFile = self::$layoutDir . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $__content;
        }
    }

    /**
     * Render a partial (no layout).
     */
    public static function partial(string $view, array $data = []): string
    {
        self::init();
        extract($data);

        ob_start();
        $viewFile = self::$viewDir . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        }
        return ob_get_clean();
    }

    /**
     * Escape HTML.
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format a date.
     */
    public static function date(?string $date, string $format = 'Y-m-d'): string
    {
        if (empty($date)) return '';
        return date($format, strtotime($date));
    }

    /**
     * Format currency.
     */
    public static function money(float $amount, string $currency = 'USD'): string
    {
        return number_format($amount, 2, '.', ',') . ' ' . $currency;
    }
}
