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

final class DCCT_Frontend {
    /** @var self|null */
    private static $instance = null;

    /** @var bool */
    private $assets_queued = false;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void {
        add_shortcode('dc_cloud_timeline', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }

    public function register_assets(): void {
        wp_register_style(
            'dcct-nunito',
            'https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap',
            [],
            DCCT_VERSION
        );
        wp_register_style(
            'dcct-frontend',
            DCCT_URL . 'assets/frontend/css/frontend.css',
            ['dcct-nunito'],
            DCCT_VERSION
        );
        wp_register_script(
            'dcct-frontend',
            DCCT_URL . 'assets/frontend/js/timeline.js',
            [],
            DCCT_VERSION,
            true
        );
    }

    /**
     * @param array<string, string>|string $atts
     */
    public function render_shortcode($atts): string {
        $atts = shortcode_atts(
            ['id' => '0'],
            is_array($atts) ? $atts : [],
            'dc_cloud_timeline'
        );

        $id = absint($atts['id']);
        if ($id <= 0) {
            return '';
        }

        $payload = DCCT_Data::frontend_payload($id);
        if ($payload === null) {
            return '';
        }

        $this->enqueue_assets();

        $settings = $payload['settings'];
        $author = $payload['author'];
        $uid = 'dcct-' . $id;

        $style = sprintf(
            '--dcct-bg:%1$s;--dcct-bar1:%2$s;--dcct-bar2:%3$s;--dcct-line:%4$s;--dcct-card:%5$s;--dcct-text:%6$s;--dcct-accent:%7$s;' .
            '--dcct-heading-size:%8$dpx;--dcct-subheading-size:%9$dpx;--dcct-author-name-size:%10$dpx;--dcct-author-bio-size:%11$dpx;' .
            '--dcct-entry-title-size:%12$dpx;--dcct-entry-date-size:%13$dpx;--dcct-entry-content-size:%14$dpx;' .
            '--dcct-author-width:%15$dpx;--dcct-layout-gap:%16$dpx;--dcct-author-avatar-size:%17$dpx;' .
            '--dcct-author-name-color:%18$s;--dcct-author-bio-color:%19$s;--dcct-author-bg:%20$s;' .
            '--dcct-heading-color:%21$s;--dcct-subheading-color:%22$s;' .
            '--dcct-author-shadow-blur:%23$dpx;--dcct-author-shadow-opacity:%24$s;' .
            '--dcct-cloud-shadow-blur:%25$dpx;--dcct-cloud-shadow-y:%26$dpx;--dcct-cloud-shadow-opacity:%27$s;',
            esc_attr((string) $settings['bg']),
            esc_attr((string) $settings['bar1']),
            esc_attr((string) $settings['bar2']),
            esc_attr((string) $settings['line']),
            esc_attr((string) $settings['card']),
            esc_attr((string) $settings['text']),
            esc_attr((string) $settings['accent']),
            (int) $settings['heading_size'],
            (int) $settings['subheading_size'],
            (int) $settings['author_name_size'],
            (int) $settings['author_bio_size'],
            (int) $settings['entry_title_size'],
            (int) $settings['entry_date_size'],
            (int) $settings['entry_content_size'],
            (int) $settings['author_width'],
            (int) $settings['layout_gap'],
            (int) ($settings['author_avatar_size'] ?? 160),
            esc_attr((string) ($settings['author_name_color'] ?? '#ffffff')),
            esc_attr((string) ($settings['author_bio_color'] ?? '#ffffff')),
            esc_attr((string) ($settings['author_bg'] ?? '#1a1d38')),
            esc_attr((string) ($settings['heading_color'] ?? '#ffffff')),
            esc_attr((string) ($settings['subheading_color'] ?? '#ffffff')),
            (int) ($settings['author_shadow_blur'] ?? 28),
            esc_attr(number_format(max(0, min(100, (int) ($settings['author_shadow_opacity'] ?? 18))) / 100, 2, '.', '')),
            (int) ($settings['cloud_shadow_blur'] ?? 18),
            (int) ($settings['cloud_shadow_y'] ?? 10),
            esc_attr(number_format(max(0, min(100, (int) ($settings['cloud_shadow_opacity'] ?? 35))) / 100, 2, '.', ''))
        );

        $config = wp_json_encode(
            [
                'orientation' => (string) $settings['orientation'],
                'amp' => (int) $settings['amp'],
                'speed' => (float) $settings['speed'],
                'drift' => (int) $settings['drift'],
                'entries' => $payload['entries'],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        ob_start();
        ?>
        <div class="dcct" id="<?php echo esc_attr($uid); ?>" style="<?php echo esc_attr($style); ?>" data-dcct-config="<?php echo esc_attr((string) $config); ?>">
            <div class="dcct__layout">
                <aside class="dcct__author">
                    <?php if (!empty($author['image_url'])) : ?>
                        <div class="dcct__author-photo">
                            <img src="<?php echo esc_url((string) $author['image_url']); ?>" alt="<?php echo esc_attr((string) $author['name']); ?>">
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($author['name'])) : ?>
                        <h2 class="dcct__author-name"><?php echo esc_html((string) $author['name']); ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($author['bio'])) : ?>
                        <div class="dcct__author-bio"><?php echo wp_kses_post((string) $author['bio']); ?></div>
                    <?php endif; ?>
                </aside>
                <div class="dcct__timeline">
                    <?php if (!empty($settings['heading'])) : ?>
                        <h2 class="dcct__heading"><?php echo esc_html((string) $settings['heading']); ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($settings['subheading'])) : ?>
                        <p class="dcct__subheading"><?php echo esc_html((string) $settings['subheading']); ?></p>
                    <?php endif; ?>
                    <div class="dcct__wrap">
                        <div class="dcct__stage">
                            <div class="dcct__bar"></div>
                            <svg class="dcct__lines" xmlns="http://www.w3.org/2000/svg"></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private function enqueue_assets(): void {
        if ($this->assets_queued) {
            return;
        }
        $this->assets_queued = true;
        wp_enqueue_style('dcct-frontend');
        wp_enqueue_script('dcct-frontend');
    }
}
