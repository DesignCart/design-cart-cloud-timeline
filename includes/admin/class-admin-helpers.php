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

final class DCCT_Admin_Helpers {
    /**
     * @param array<int, array<string, string>> $actions
     * @param array<int, array<string, string>> $crumbs
     */
    public static function render_hero(string $title, array $actions, array $crumbs): void {
        $logo = DCCT_URL . 'assets/admin/images/dc-logo-white.png';

        echo '<div class="dc-hero">';
        echo '<div class="dc-hero__mesh"></div>';
        echo '<img class="dc-hero__logo" src="' . esc_url($logo) . '" alt="" aria-hidden="true" width="384" height="384">';
        echo '<div class="dc-hero__orb dc-hero__orb--1"></div>';
        echo '<div class="dc-hero__orb dc-hero__orb--2"></div>';
        echo '<div class="dc-hero__inner"><div class="dc-hero__row">';
        echo '<div class="dc-hero__brand">';
        echo '<span class="dc-hero__icon"><i class="fa fa-cloud"></i></span>';
        echo '<div><p class="dc-hero__eyebrow">Design Cart</p>';
        echo '<h1 class="dc-hero__title">' . esc_html($title) . '</h1></div></div>';
        echo '<div class="dc-hero__actions">';

        foreach ($actions as $action) {
            $class = $action['class'] ?? 'dc-btn dc-btn--light';
            $icon = $action['icon'] ?? '';
            $label = $action['label'] ?? '';
            $title_attr = $action['title'] ?? '';

            if (!empty($action['form'])) {
                echo '<button type="submit" form="' . esc_attr($action['form']) . '" class="' . esc_attr($class) . '"';
                if ($title_attr !== '') {
                    echo ' title="' . esc_attr($title_attr) . '"';
                }
                echo '>';
                if ($icon !== '') {
                    echo '<i class="' . esc_attr($icon) . '"></i> ';
                }
                echo esc_html($label) . '</button>';
            } else {
                echo '<a class="' . esc_attr($class) . '" href="' . esc_url($action['href'] ?? '') . '"';
                if ($title_attr !== '') {
                    echo ' title="' . esc_attr($title_attr) . '"';
                }
                echo '>';
                if ($icon !== '') {
                    echo '<i class="' . esc_attr($icon) . '"></i> ';
                }
                echo esc_html($label) . '</a>';
            }
        }

        echo '</div></div><ul class="dc-hero__bc">';
        foreach ($crumbs as $crumb) {
            if (!empty($crumb['href'])) {
                echo '<li><a href="' . esc_url($crumb['href']) . '">' . esc_html($crumb['label']) . '</a></li>';
            } else {
                echo '<li>' . esc_html($crumb['label']) . '</li>';
            }
        }
        echo '</ul></div></div>';
    }

    /**
     * @param array<string, array{0:string,1:string}> $tabs
     * @param string $active Active tab id.
     */
    public static function render_nav(array $tabs, string $active = ''): void {
        echo '<nav class="dc-nav dc-tabs" role="tablist">';
        $first = true;
        foreach ($tabs as $id => $meta) {
            $is_active = ($active !== '' && $active === $id) || ($active === '' && $first);
            $active_class = $is_active ? ' dc-active' : '';
            $selected = $is_active ? 'true' : 'false';
            echo '<button type="button" class="dc-nav__btn dc-tabs__btn' . esc_attr($active_class) . '" data-dc-tab="' . esc_attr($id) . '" role="tab" aria-selected="' . esc_attr($selected) . '">';
            echo '<i class="' . esc_attr($meta[0]) . '"></i> ' . esc_html($meta[1]);
            echo '</button>';
            $first = false;
        }
        echo '</nav>';
    }

    public static function render_notices(): void {
        $map = [
            'updated' => __('Zapisano pomyślnie.', 'design-cart-cloud-timeline'),
            'deleted' => __('Usunięto pomyślnie.', 'design-cart-cloud-timeline'),
            'copied' => __('Utworzono kopię.', 'design-cart-cloud-timeline'),
        ];

        foreach ($map as $key => $message) {
            if (self::query_text($key) === '1') {
                echo '<div class="dcct-flash dcct-flash--ok"><i class="fa fa-check-circle"></i> ' .
                    esc_html($message) . '</div>';
            }
        }
    }

    public static function page_start(string $title): void {
        echo '<div class="wrap dcct-wrap"><h1>' . esc_html($title) . '</h1>';
        echo '<div class="dc-page dcct-page">';
    }

    public static function page_end(): void {
        echo '</div></div>';
    }

    public static function section_start(string $icon, string $title, string $sub = ''): void {
        echo '<div class="dc-section-card dc-section">';
        echo '<div class="dc-section-card__head">';
        echo '<span class="dc-section-card__icon"><i class="' . esc_attr($icon) . '"></i></span>';
        echo '<div><h3 class="dc-section-card__title">' . esc_html($title) . '</h3>';
        if ($sub !== '') {
            echo '<p class="dc-section-card__sub">' . esc_html($sub) . '</p>';
        }
        echo '</div></div>';
    }

    public static function section_end(): void {
        echo '</div>';
    }

    public static function text_field(string $name, string $id, string $label, string $value, bool $required = false): void {
        $req = $required ? ' dc-label--required' : '';
        echo '<div class="dc-field">';
        echo '<label class="dc-label' . esc_attr($req) . '" for="' . esc_attr($id) . '">' . esc_html($label) . '</label>';
        echo '<input class="dc-input" type="text" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"' . ($required ? ' required' : '') . '>';
        echo '</div>';
    }

    public static function number_field(string $name, string $id, string $label, $value, $min, $max, string $step = '1'): void {
        echo '<div class="dc-field">';
        echo '<label class="dc-label" for="' . esc_attr($id) . '">' . esc_html($label) . '</label>';
        echo '<input class="dc-input" type="number" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '" min="' . esc_attr((string) $min) . '" max="' . esc_attr((string) $max) . '" step="' . esc_attr($step) . '">';
        echo '</div>';
    }

    /**
     * @param array<string, string> $options
     */
    public static function radio_group(string $name, string $label, array $options, string $current): void {
        echo '<div class="dc-field"><span class="dc-label">' . esc_html($label) . '</span>';
        echo '<div class="dc-switch-group dc-switch-group--solid" role="radiogroup">';
        foreach ($options as $value => $text) {
            echo '<label class="dc-switch-btn">';
            echo '<input class="dc-switch-btn__input" type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"' . checked($current, $value, false) . '>';
            echo '<span class="dc-switch-btn__label">' . esc_html($text) . '</span></label>';
        }
        echo '</div></div>';
    }

    public static function color_field(string $name, string $label, string $value): void {
        echo '<div class="dc-field">';
        echo '<label class="dc-label">' . esc_html($label) . '</label>';
        echo '<div class="dc-colorpicker" data-dc-colorpicker data-name="' . esc_attr($name) . '" data-value="' . esc_attr($value) . '" data-label="' . esc_attr($label) . '"></div>';
        echo '</div>';
    }

    public static function dimension_field(string $name, string $label, int $value, int $min, int $max): void {
        echo '<div class="dc-field">';
        echo '<div class="dc-dimension" data-dc-dimension data-name="' . esc_attr($name) . '" data-label="' . esc_attr($label) . '" data-value="' . esc_attr((string) $value) . '" data-fixed-unit="px" data-min="' . esc_attr((string) $min) . '" data-max-px="' . esc_attr((string) $max) . '"></div>';
        echo '</div>';
    }

    /**
     * Media picker control (image_id + preview).
     */
    public static function media_field(string $name, string $id_prefix, string $label, int $image_id): void {
        $url = $image_id > 0 ? DCCT_Data::image_url($image_id, 'medium') : '';

        echo '<div class="dc-field dcct-media" data-dcct-media>';
        echo '<label class="dc-label">' . esc_html($label) . '</label>';
        echo '<div class="dcct-media__preview" id="' . esc_attr($id_prefix) . '-preview">';
        if ($url !== '') {
            echo '<img src="' . esc_url($url) . '" alt="">';
        }
        echo '</div>';
        echo '<input type="hidden" class="dcct-media__id" id="' . esc_attr($id_prefix) . '-id" name="' . esc_attr($name) . '" value="' . esc_attr((string) $image_id) . '">';
        echo '<div class="dcct-media__actions">';
        echo '<button type="button" class="dc-btn dc-btn--secondary dcct-media__select"><i class="fa fa-folder-open"></i> ' .
            esc_html__('Wybierz zdjęcie', 'design-cart-cloud-timeline') . '</button>';
        echo '<button type="button" class="dc-btn dc-btn--ghost dcct-media__clear"><i class="fa fa-times"></i> ' .
            esc_html__('Usuń', 'design-cart-cloud-timeline') . '</button>';
        echo '</div></div>';
    }

    public static function query_text(string $key): string {
        $value = filter_input(INPUT_GET, $key, FILTER_UNSAFE_RAW);
        if (!is_string($value)) {
            return '';
        }

        return sanitize_text_field(wp_unslash($value));
    }

    public static function query_id(string $key): int {
        return absint(self::query_text($key));
    }

    public static function posted_text(string $key): string {
        $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);
        if (!is_string($value)) {
            return '';
        }

        return sanitize_text_field(wp_unslash($value));
    }

    public static function posted_id(string $key): int {
        return absint(self::posted_text($key));
    }

    /**
     * @return array<string, mixed>
     */
    public static function posted_array(string $key): array {
        if (
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by caller.
            !isset($_POST[$key])
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by caller.
            || !is_array($_POST[$key])
        ) {
            return [];
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verified + sanitized by caller.
        return wp_unslash($_POST[$key]);
    }

    public static function edit_url(int $id = 0, string $section = 'author'): string {
        $url = admin_url('admin.php?page=' . DCCT_Admin::PAGE_EDIT);
        if ($id > 0) {
            $url = add_query_arg('id', $id, $url);
        }
        if ($section !== '') {
            $url = add_query_arg('section', $section, $url);
        }

        return $url;
    }
}
