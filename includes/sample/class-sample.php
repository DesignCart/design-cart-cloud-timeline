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

final class DCCT_Sample {
    public const OPTION_SEEDED = 'dcct_sample_seeded';

    public static function seed(): void {
        if (get_option(self::OPTION_SEEDED) === '1') {
            return;
        }

        $existing = get_posts([
            'post_type' => DCCT_Data::POST_TYPE,
            'posts_per_page' => 1,
            'post_status' => 'any',
            'fields' => 'ids',
        ]);

        if ($existing) {
            update_option(self::OPTION_SEEDED, '1', false);
            return;
        }

        $author_image = self::sideload_plugin_image('author.jpg');
        $entries = [];

        foreach (self::demo_entries() as $item) {
            $attachment_id = self::sideload_plugin_image($item['file']);
            $entries[] = [
                'title' => $item['title'],
                'date' => $item['date'],
                'content' => $item['content'],
                'image_id' => $attachment_id > 0 ? $attachment_id : 0,
            ];
        }

        $timeline_id = wp_insert_post([
            'post_type' => DCCT_Data::POST_TYPE,
            'post_status' => 'publish',
            'post_title' => 'Sample — Elena Hart Journey',
        ], true);

        if (is_wp_error($timeline_id) || !$timeline_id) {
            return;
        }

        $timeline_id = (int) $timeline_id;

        $author = DCCT_Data::sanitize_author([
            'name' => 'Elena Hart',
            'image_id' => $author_image > 0 ? $author_image : 0,
            'bio' => '<p><strong>Elena Hart</strong> is a product designer and founder of <em>Northline Studio</em> — a fictional creative lab exploring calm interfaces, thoughtful motion, and human-centered tools.</p>'
                . '<ul><li>Based in Lisbon</li><li>Focus: design systems &amp; storytelling</li><li>Motto: „Make complexity feel quiet.”</li></ul>',
        ]);

        $settings = DCCT_Data::default_settings();
        $settings['heading'] = 'Our story';
        $settings['subheading'] = 'A sample timeline with a fictional author — replace it with your own.';
        $settings = DCCT_Data::sanitize_settings($settings);

        update_post_meta($timeline_id, DCCT_Data::META_AUTHOR, $author);
        update_post_meta($timeline_id, DCCT_Data::META_ENTRIES, DCCT_Data::sanitize_entries($entries));
        update_post_meta($timeline_id, DCCT_Data::META_SETTINGS, $settings);

        update_option(self::OPTION_SEEDED, '1', false);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function demo_entries(): array {
        return [
            [
                'file' => 'entry-1.jpg',
                'title' => 'First sketch',
                'date' => 'January 2023',
                'content' => 'A napkin sketch turned into a late-night Figma file. The idea for Northline Studio was born.',
            ],
            [
                'file' => 'entry-2.jpg',
                'title' => 'Team of three',
                'date' => 'April 2023',
                'content' => 'Two friends joined. Friday workshops became a ritual — coffee, sticky notes, and honest critique.',
            ],
            [
                'file' => 'entry-3.jpg',
                'title' => 'Working prototype',
                'date' => 'July 2023',
                'content' => 'The first clickable prototype shipped to a handful of designers. Feedback poured in within days.',
            ],
            [
                'file' => 'entry-4.jpg',
                'title' => 'Early adopters',
                'date' => 'October 2023',
                'content' => 'One hundred people tried the beta. Their notes shaped the next six months of work.',
            ],
            [
                'file' => 'entry-5.jpg',
                'title' => 'Public launch',
                'date' => 'March 2024',
                'content' => 'Official launch day: new site, polished motion, and a calm product ready for the world.',
            ],
            [
                'file' => 'entry-7.jpg',
                'title' => 'Design system v1',
                'date' => 'June 2024',
                'content' => 'Tokens, components, and a shared Figma library finally spoke the same language across products.',
            ],
            [
                'file' => 'entry-8.jpg',
                'title' => 'First agency partner',
                'date' => 'September 2024',
                'content' => 'A studio in Berlin adopted Northline for client pitches. Word of mouth did the rest.',
            ],
            [
                'file' => 'entry-9.jpg',
                'title' => 'Motion guidelines',
                'date' => 'December 2024',
                'content' => 'Soft easing, short delays, zero noise — a motion guide that felt calm instead of flashy.',
            ],
            [
                'file' => 'entry-10.jpg',
                'title' => 'Remote retreat',
                'date' => 'February 2025',
                'content' => 'The team met in Porto for a week of sketches, walks, and decisions that stuck.',
            ],
            [
                'file' => 'entry-11.jpg',
                'title' => 'Open workshop',
                'date' => 'May 2025',
                'content' => 'A free online workshop drew hundreds of designers. The recording became a quiet hit.',
            ],
            [
                'file' => 'entry-12.jpg',
                'title' => 'Chapter two begins',
                'date' => 'Autumn 2025',
                'content' => 'A new product line took shape — same philosophy, wider canvas. The story continues.',
            ],
            [
                'file' => 'entry-6.jpg',
                'title' => 'What comes next',
                'date' => '2026 & beyond',
                'content' => 'New chapters are already sketched. The timeline keeps growing — just like yours will.',
            ],
        ];
    }

    /**
     * Expand / refresh the sample timeline on an existing install (idempotent by title).
     */
    public static function refresh_existing_demo(): void {
        $posts = get_posts([
            'post_type' => DCCT_Data::POST_TYPE,
            'posts_per_page' => 20,
            'post_status' => 'any',
        ]);

        $timeline_id = 0;
        foreach ($posts as $post) {
            if ($post->post_title === 'Sample — Elena Hart Journey') {
                $timeline_id = (int) $post->ID;
                break;
            }
        }

        if ($timeline_id <= 0) {
            delete_option(self::OPTION_SEEDED);
            self::seed();
            return;
        }

        $entries = [];
        foreach (self::demo_entries() as $item) {
            $attachment_id = self::find_attachment_by_filename($item['file']);
            if ($attachment_id <= 0) {
                $attachment_id = self::sideload_plugin_image($item['file']);
            }
            $entries[] = [
                'title' => $item['title'],
                'date' => $item['date'],
                'content' => $item['content'],
                'image_id' => $attachment_id > 0 ? $attachment_id : 0,
            ];
        }

        $current = DCCT_Data::get_timeline($timeline_id);
        $settings = DCCT_Data::sanitize_settings(
            wp_parse_args($current['settings'], DCCT_Data::default_settings())
        );

        update_post_meta($timeline_id, DCCT_Data::META_ENTRIES, DCCT_Data::sanitize_entries($entries));
        update_post_meta($timeline_id, DCCT_Data::META_SETTINGS, $settings);
    }

    private static function find_attachment_by_filename(string $filename): int {
        global $wpdb;
        $base = pathinfo($filename, PATHINFO_FILENAME);
        // Match uploaded files like entry-7.jpg / entry-7-scaled.jpg / entry-7-300x300.jpg
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta}
                WHERE meta_key = '_wp_attached_file'
                AND meta_value LIKE %s
                ORDER BY post_id ASC
                LIMIT 1",
                '%/' . $wpdb->esc_like($base) . '%'
            )
        );

        return $id ? (int) $id : 0;
    }

    private static function sideload_plugin_image(string $filename): int {
        $source = DCCT_DIR . 'assets/frontend/sample-images/' . $filename;
        if (!is_readable($source)) {
            return 0;
        }

        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $tmp = wp_tempnam($filename);
        if (!$tmp || !copy($source, $tmp)) {
            return 0;
        }

        $file_array = [
            'name' => $filename,
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, null, [
            'post_title' => pathinfo($filename, PATHINFO_FILENAME),
        ]);

        if (is_wp_error($attachment_id)) {
            wp_delete_file($tmp);
            return 0;
        }

        return (int) $attachment_id;
    }
}
