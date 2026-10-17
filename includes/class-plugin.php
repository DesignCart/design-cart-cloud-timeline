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

final class DCCT_Plugin {
    /** @var self|null */
    private static $instance = null;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void {
        add_action('init', [$this, 'register_post_type']);

        if (is_admin()) {
            DCCT_Admin::instance()->boot();
        }

        DCCT_Frontend::instance()->boot();
    }

    public function register_post_type(): void {
        register_post_type(
            DCCT_Data::POST_TYPE,
            [
                'labels' => [
                    'name' => __('Cloud Timelines', 'design-cart-cloud-timeline'),
                    'singular_name' => __('Cloud Timeline', 'design-cart-cloud-timeline'),
                ],
                'public' => false,
                'show_ui' => false,
                'supports' => ['title'],
            ]
        );
    }

    public static function activate(): void {
        self::instance()->register_post_type();
        DCCT_Sample::seed();
        flush_rewrite_rules();
    }
}
