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

final class DCCT_Admin_Handlers {
    public static function boot(): void {
        add_action('admin_post_dcct_save', [__CLASS__, 'save']);
        add_action('admin_post_dcct_delete', [__CLASS__, 'delete']);
        add_action('admin_post_dcct_copy', [__CLASS__, 'copy']);
    }

    public static function save(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Brak uprawnień.', 'design-cart-cloud-timeline'));
        }
        check_admin_referer('dcct_save');

        $timeline_id = DCCT_Admin_Helpers::posted_id('timeline_id');
        $title = DCCT_Admin_Helpers::posted_text('title');
        $section = DCCT_Admin_Helpers::posted_text('active_section');
        if (!in_array($section, ['author', 'entries', 'settings'], true)) {
            $section = 'author';
        }

        $author = DCCT_Data::sanitize_author(DCCT_Admin_Helpers::posted_array('author'));
        // Bio may come from wp_editor which posts as author[bio] — also accept raw POST for KSES.
        if (isset($_POST['author']['bio']) && is_string($_POST['author']['bio'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $author['bio'] = wp_kses_post(wp_unslash($_POST['author']['bio'])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        }

        $entries = DCCT_Data::sanitize_entries(DCCT_Admin_Helpers::posted_array('entries'));
        $settings = DCCT_Data::sanitize_settings(DCCT_Admin_Helpers::posted_array('settings'));

        $post_data = [
            'post_type' => DCCT_Data::POST_TYPE,
            'post_title' => $title !== '' ? $title : __('Nowy Cloud Timeline', 'design-cart-cloud-timeline'),
            'post_status' => 'publish',
        ];

        if ($timeline_id > 0) {
            $post_data['ID'] = $timeline_id;
            $timeline_id = wp_update_post($post_data, true);
        } else {
            $timeline_id = wp_insert_post($post_data, true);
        }

        if (is_wp_error($timeline_id)) {
            wp_die(esc_html($timeline_id->get_error_message()));
        }

        $timeline_id = (int) $timeline_id;
        update_post_meta($timeline_id, DCCT_Data::META_AUTHOR, $author);
        update_post_meta($timeline_id, DCCT_Data::META_ENTRIES, $entries);
        update_post_meta($timeline_id, DCCT_Data::META_SETTINGS, $settings);

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => DCCT_Admin::PAGE_EDIT,
                    'id' => $timeline_id,
                    'section' => $section,
                    'updated' => 1,
                ],
                admin_url('admin.php')
            )
        );
        exit;
    }

    public static function delete(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Brak uprawnień.', 'design-cart-cloud-timeline'));
        }

        $id = DCCT_Admin_Helpers::query_id('id');
        check_admin_referer('dcct_delete_' . $id);

        if ($id > 0) {
            wp_delete_post($id, true);
        }

        wp_safe_redirect(admin_url('admin.php?page=' . DCCT_Admin::PAGE_DASH . '&deleted=1'));
        exit;
    }

    public static function copy(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Brak uprawnień.', 'design-cart-cloud-timeline'));
        }

        $id = DCCT_Admin_Helpers::query_id('id');
        check_admin_referer('dcct_copy_' . $id);
        $post = get_post($id);

        if ($post && $post->post_type === DCCT_Data::POST_TYPE) {
            $new_id = wp_insert_post([
                'post_type' => DCCT_Data::POST_TYPE,
                'post_status' => 'publish',
                'post_title' => $post->post_title . ' ' . __('(Kopia)', 'design-cart-cloud-timeline'),
            ]);

            if ($new_id && !is_wp_error($new_id)) {
                update_post_meta($new_id, DCCT_Data::META_AUTHOR, get_post_meta($id, DCCT_Data::META_AUTHOR, true));
                update_post_meta($new_id, DCCT_Data::META_ENTRIES, get_post_meta($id, DCCT_Data::META_ENTRIES, true));
                update_post_meta($new_id, DCCT_Data::META_SETTINGS, get_post_meta($id, DCCT_Data::META_SETTINGS, true));
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=' . DCCT_Admin::PAGE_DASH . '&copied=1'));
        exit;
    }
}
