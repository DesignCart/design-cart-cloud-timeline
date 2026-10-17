<?php
/**
 * Design Cart Cloud Timeline
 * Code: design-cart-cloud-timeline
 * Autor: Paweł Nosko — https://www.designcart.pl/pawel-nosko.html
 * Firma: Design Cart — https://www.designcart.pl/
 */

if (!defined('ABSPATH')) {
    exit;
}

final class DCCT_Admin {
    public const PAGE_DASH = 'design-cart-cloud-timeline';
    public const PAGE_EDIT = 'design-cart-cloud-timeline-edit';

    /** @var self|null */
    private static $instance = null;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('admin_body_class', [$this, 'admin_body_class']);
        DCCT_Admin_Handlers::boot();
    }

    public function register_menu(): void {
        add_menu_page(
            __('Cloud Timeline', 'design-cart-cloud-timeline'),
            __('Cloud Timeline', 'design-cart-cloud-timeline'),
            'manage_options',
            self::PAGE_DASH,
            ['DCCT_Admin_Dashboard', 'render'],
            'dashicons-cloud',
            58
        );

        add_submenu_page(
            self::PAGE_DASH,
            __('Kokpit', 'design-cart-cloud-timeline'),
            __('Kokpit', 'design-cart-cloud-timeline'),
            'manage_options',
            self::PAGE_DASH,
            ['DCCT_Admin_Dashboard', 'render']
        );

        add_submenu_page(
            self::PAGE_DASH,
            __('Edycja timeline', 'design-cart-cloud-timeline'),
            __('Dodaj / edytuj', 'design-cart-cloud-timeline'),
            'manage_options',
            self::PAGE_EDIT,
            ['DCCT_Admin_Form', 'render']
        );
    }

    public function admin_body_class(string $classes): string {
        if ($this->is_plugin_page()) {
            $classes .= ' dcct-admin';
        }

        return $classes;
    }

    public function enqueue_assets(string $hook): void {
        if (!$this->is_plugin_page($hook)) {
            return;
        }

        $base = DCCT_URL . 'assets/admin/';
        $css = $base . 'css/';
        $js = $base . 'js/';
        $ver = DCCT_VERSION;

        wp_enqueue_media();
        wp_enqueue_editor();
        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_style(
            'dcct-font-awesome',
            $base . 'vendor/font-awesome/font-awesome.min.css',
            [],
            '4.7.0'
        );

        $sheets = [
            'dc-tokens',
            'dc-hero',
            'dc-layout',
            'dc-tabs',
            'dc-accordion',
            'dc-switch',
            'dc-dimension',
            'dc-forms',
            'dc-colorpicker',
        ];
        $prev = 'dcct-font-awesome';
        foreach ($sheets as $sheet) {
            $handle = 'dcct-' . $sheet;
            wp_enqueue_style($handle, $css . $sheet . '.css', [$prev], $ver);
            $prev = $handle;
        }

        wp_enqueue_style('dcct-admin-overrides', $css . 'admin-overrides.css', [$prev], $ver);
        wp_enqueue_style('dcct-admin', $css . 'admin.css', ['dcct-admin-overrides'], $ver);

        wp_enqueue_script('dcct-colorpicker', $js . 'dc-colorpicker.js', [], $ver, true);
        wp_enqueue_script('dcct-dimension', $js . 'dc-dimension.js', [], $ver, true);
        wp_enqueue_script(
            'dcct-interface',
            $js . 'dc-interface.js',
            ['dcct-colorpicker', 'dcct-dimension'],
            $ver,
            true
        );
        wp_enqueue_script(
            'dcct-admin',
            $js . 'admin.js',
            ['jquery', 'jquery-ui-sortable', 'dcct-interface'],
            $ver,
            true
        );

        wp_localize_script('dcct-admin', 'dcctAdmin', [
            'i18n' => [
                'selectImage' => __('Wybierz zdjęcie', 'design-cart-cloud-timeline'),
                'useImage' => __('Użyj zdjęcia', 'design-cart-cloud-timeline'),
                'confirmDelete' => __('Usunąć ten timeline?', 'design-cart-cloud-timeline'),
                'confirmRemoveEntry' => __('Usunąć ten wpis?', 'design-cart-cloud-timeline'),
                'entry' => __('Wpis', 'design-cart-cloud-timeline'),
            ],
        ]);
    }

    private function is_plugin_page(string $hook = ''): bool {
        $page = DCCT_Admin_Helpers::query_text('page');
        if (in_array($page, [self::PAGE_DASH, self::PAGE_EDIT], true)) {
            return true;
        }

        if ($hook === '') {
            return false;
        }

        return strpos($hook, self::PAGE_DASH) !== false;
    }
}
