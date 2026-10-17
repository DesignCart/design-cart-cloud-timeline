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

final class DCCT_Data {
    public const POST_TYPE = 'dc_cloud_timeline';
    public const META_AUTHOR = '_dcct_author';
    public const META_ENTRIES = '_dcct_entries';
    public const META_SETTINGS = '_dcct_settings';

    /**
     * @return array<string, mixed>
     */
    public static function default_author(): array {
        return [
            'name' => '',
            'image_id' => 0,
            'bio' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function default_entry(): array {
        return [
            'title' => '',
            'date' => '',
            'content' => '',
            'image_id' => 0,
        ];
    }

    /**
     * Defaults mirror options from dc-cloud-timeline demo.
     *
     * @return array<string, mixed>
     */
    public static function default_settings(): array {
        return [
            'orientation' => 'v',
            'bg' => '#12142b',
            'bar1' => '#7c5cff',
            'bar2' => '#18d6c8',
            'line' => '#9aa4ff',
            'card' => '#ffffff',
            'text' => '#1d2040',
            'accent' => '#6a49f0',
            'amp' => 7,
            'speed' => 1,
            'drift' => 10,
            'heading' => '',
            'subheading' => '',
            'heading_size' => 40,
            'subheading_size' => 14,
            'author_name_size' => 22,
            'author_bio_size' => 15,
            'author_name_color' => '#ffffff',
            'author_bio_color' => '#ffffff',
            'author_bg' => '#1a1d38',
            'heading_color' => '#ffffff',
            'subheading_color' => '#ffffff',
            'entry_title_size' => 15,
            'entry_date_size' => 12,
            'entry_content_size' => 13,
            'author_width' => 280,
            'author_avatar_size' => 160,
            'layout_gap' => 32,
            'author_shadow_blur' => 28,
            'author_shadow_opacity' => 18,
            'cloud_shadow_blur' => 18,
            'cloud_shadow_opacity' => 35,
            'cloud_shadow_y' => 10,
        ];
    }

    /**
     * @return array{
     *   title:string,
     *   author:array<string,mixed>,
     *   entries:array<int,array<string,mixed>>,
     *   settings:array<string,mixed>
     * }
     */
    public static function get_timeline(int $id = 0): array {
        $author = self::default_author();
        $entries = [];
        $settings = self::default_settings();
        $title = '';

        if ($id > 0) {
            $post = get_post($id);
            if ($post && $post->post_type === self::POST_TYPE) {
                $title = $post->post_title;
                $raw_author = get_post_meta($id, self::META_AUTHOR, true);
                $raw_entries = get_post_meta($id, self::META_ENTRIES, true);
                $raw_settings = get_post_meta($id, self::META_SETTINGS, true);
                $author = wp_parse_args(is_array($raw_author) ? $raw_author : [], $author);
                $entries = is_array($raw_entries) ? array_values($raw_entries) : [];
                $settings = wp_parse_args(is_array($raw_settings) ? $raw_settings : [], $settings);
            }
        }

        return [
            'title' => $title,
            'author' => $author,
            'entries' => $entries,
            'settings' => $settings,
        ];
    }

    /**
     * @param array<string, mixed> $author
     * @return array<string, mixed>
     */
    public static function sanitize_author(array $author): array {
        $defaults = self::default_author();

        return [
            'name' => isset($author['name']) ? sanitize_text_field((string) $author['name']) : $defaults['name'],
            'image_id' => isset($author['image_id']) ? absint($author['image_id']) : 0,
            'bio' => isset($author['bio']) ? wp_kses_post((string) $author['bio']) : '',
        ];
    }

    /**
     * @param array<int|string, mixed> $entries
     * @return array<int, array<string, mixed>>
     */
    public static function sanitize_entries(array $entries): array {
        $clean = [];

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $title = isset($entry['title']) ? sanitize_text_field((string) $entry['title']) : '';
            $date = isset($entry['date']) ? sanitize_text_field((string) $entry['date']) : '';
            $content = isset($entry['content']) ? wp_kses_post((string) $entry['content']) : '';
            $image_id = isset($entry['image_id']) ? absint($entry['image_id']) : 0;

            if ($title === '' && $date === '' && $content === '' && $image_id === 0) {
                continue;
            }

            $clean[] = [
                'title' => $title,
                'date' => $date,
                'content' => $content,
                'image_id' => $image_id,
            ];
        }

        return $clean;
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public static function sanitize_settings(array $settings): array {
        $defaults = self::default_settings();
        $clean = [];

        $orientation = isset($settings['orientation']) ? sanitize_key((string) $settings['orientation']) : $defaults['orientation'];
        $clean['orientation'] = in_array($orientation, ['v', 'h'], true) ? $orientation : 'v';

        foreach (
            [
                'bg',
                'bar1',
                'bar2',
                'line',
                'card',
                'text',
                'accent',
                'author_name_color',
                'author_bio_color',
                'author_bg',
                'heading_color',
                'subheading_color',
            ] as $color_key
        ) {
            $hex = isset($settings[$color_key]) ? sanitize_hex_color((string) $settings[$color_key]) : '';
            $clean[$color_key] = $hex ? $hex : $defaults[$color_key];
        }

        $clean['amp'] = isset($settings['amp']) ? max(0, min(24, (int) $settings['amp'])) : (int) $defaults['amp'];
        $clean['speed'] = isset($settings['speed']) ? max(0, min(3, (float) $settings['speed'])) : (float) $defaults['speed'];
        $clean['drift'] = isset($settings['drift']) ? max(0, min(30, (int) $settings['drift'])) : (int) $defaults['drift'];

        $clean['heading'] = isset($settings['heading']) ? sanitize_text_field((string) $settings['heading']) : '';
        $clean['subheading'] = isset($settings['subheading']) ? sanitize_text_field((string) $settings['subheading']) : '';

        foreach (
            [
                'heading_size' => [12, 72],
                'subheading_size' => [10, 32],
                'author_name_size' => [12, 48],
                'author_bio_size' => [10, 28],
                'entry_title_size' => [10, 32],
                'entry_date_size' => [10, 24],
                'entry_content_size' => [10, 24],
                'author_width' => [180, 480],
                'author_avatar_size' => [64, 400],
                'layout_gap' => [12, 80],
                'author_shadow_blur' => [0, 80],
                'author_shadow_opacity' => [0, 100],
                'cloud_shadow_blur' => [0, 60],
                'cloud_shadow_opacity' => [0, 100],
                'cloud_shadow_y' => [0, 40],
            ] as $key => $range
        ) {
            $val = isset($settings[$key]) ? (int) $settings[$key] : (int) $defaults[$key];
            $clean[$key] = max($range[0], min($range[1], $val));
        }

        return $clean;
    }

    /**
     * Resolve image URL for an attachment ID.
     */
    public static function image_url(int $attachment_id, string $size = 'medium'): string {
        if ($attachment_id <= 0) {
            return '';
        }

        $url = wp_get_attachment_image_url($attachment_id, $size);
        return is_string($url) ? $url : '';
    }

    /**
     * Frontend payload for a timeline.
     *
     * @return array<string, mixed>|null
     */
    public static function frontend_payload(int $id): ?array {
        $post = get_post($id);
        if (!$post || $post->post_type !== self::POST_TYPE) {
            return null;
        }

        $data = self::get_timeline($id);
        $author = $data['author'];
        $author['image_url'] = self::image_url((int) $author['image_id'], 'large');

        $entries = [];
        foreach ($data['entries'] as $entry) {
            $entries[] = [
                'title' => (string) $entry['title'],
                'date' => (string) $entry['date'],
                'content' => (string) $entry['content'],
                'image_url' => self::image_url((int) $entry['image_id'], 'medium'),
            ];
        }

        return [
            'id' => $id,
            'author' => $author,
            'entries' => $entries,
            'settings' => $data['settings'],
        ];
    }
}
