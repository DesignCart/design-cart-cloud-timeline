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

final class DCCT_Admin_Dashboard {
    public static function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $query = new WP_Query([
            'post_type' => DCCT_Data::POST_TYPE,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        DCCT_Admin_Helpers::page_start(__('Cloud Timeline — Kokpit', 'design-cart-cloud-timeline'));
        DCCT_Admin_Helpers::render_hero(
            __('Cloud Timeline', 'design-cart-cloud-timeline'),
            [
                [
                    'href' => DCCT_Admin_Helpers::edit_url(0, 'author'),
                    'label' => __('Dodaj timeline', 'design-cart-cloud-timeline'),
                    'icon' => 'fa fa-plus',
                    'class' => 'dc-btn dc-btn--light',
                ],
            ],
            [
                ['href' => admin_url('plugins.php'), 'label' => __('Wtyczki', 'design-cart-cloud-timeline')],
                ['label' => __('Design Cart Cloud Timeline', 'design-cart-cloud-timeline')],
                ['label' => __('Kokpit', 'design-cart-cloud-timeline')],
            ]
        );

        echo '<div class="dc-page__wrap"><div class="dc-form-card"><div class="dc-interface">';
        DCCT_Admin_Helpers::render_notices();
        echo '<div class="dc-form-card__body">';

        DCCT_Admin_Helpers::section_start(
            'fa fa-th-large',
            __('Sekcje', 'design-cart-cloud-timeline'),
            __('Wybierz obszar edycji — otworzy formularz nowego lub wskazanego timeline', 'design-cart-cloud-timeline')
        );

        $tiles = [
            [
                'url' => DCCT_Admin_Helpers::edit_url(0, 'author'),
                'icon' => 'fa-user',
                'label' => __('Edycja autora', 'design-cart-cloud-timeline'),
                'color' => '#1fa28c',
            ],
            [
                'url' => DCCT_Admin_Helpers::edit_url(0, 'entries'),
                'icon' => 'fa-list-ul',
                'label' => __('Wpisy timeline', 'design-cart-cloud-timeline'),
                'color' => '#1ca7ca',
            ],
            [
                'url' => DCCT_Admin_Helpers::edit_url(0, 'settings'),
                'icon' => 'fa-cog',
                'label' => __('Ustawienia', 'design-cart-cloud-timeline'),
                'color' => '#6a49f0',
            ],
        ];

        echo '<div class="dc-tiles">';
        foreach ($tiles as $tile) {
            echo '<a class="dc-tile" href="' . esc_url($tile['url']) . '" style="--tile-color:' . esc_attr($tile['color']) . '">';
            echo '<span class="dc-tile__icon"><i class="fa ' . esc_attr($tile['icon']) . '"></i></span>';
            echo '<span class="dc-tile__label">' . esc_html($tile['label']) . '</span>';
            echo '</a>';
        }
        echo '</div>';
        DCCT_Admin_Helpers::section_end();

        DCCT_Admin_Helpers::section_start(
            'fa fa-cloud',
            __('Utworzone timelines', 'design-cart-cloud-timeline'),
            __('Shortcode: [dc_cloud_timeline id="ID"]', 'design-cart-cloud-timeline')
        );

        if ($query->have_posts()) {
            echo '<div class="dcct-list">';
            while ($query->have_posts()) {
                $query->the_post();
                $id = (int) get_the_ID();
                $data = DCCT_Data::get_timeline($id);
                $count = count($data['entries']);
                $edit_author = DCCT_Admin_Helpers::edit_url($id, 'author');
                $edit_entries = DCCT_Admin_Helpers::edit_url($id, 'entries');
                $edit_settings = DCCT_Admin_Helpers::edit_url($id, 'settings');
                $copy = wp_nonce_url(
                    admin_url('admin-post.php?action=dcct_copy&id=' . $id),
                    'dcct_copy_' . $id
                );
                $delete = wp_nonce_url(
                    admin_url('admin-post.php?action=dcct_delete&id=' . $id),
                    'dcct_delete_' . $id
                );

                echo '<div class="dcct-list__item">';
                echo '<div class="dcct-list__main">';
                echo '<strong class="dcct-list__title">' . esc_html(get_the_title()) . '</strong>';
                echo '<code class="dcct-list__shortcode">[dc_cloud_timeline id="' . esc_html((string) $id) . '"]</code>';
                echo '<span class="dcct-list__meta">' .
                    esc_html(sprintf(
                        /* translators: %d: number of entries */
                        _n('%d wpis', '%d wpisów', $count, 'design-cart-cloud-timeline'),
                        $count
                    ));
                if (!empty($data['author']['name'])) {
                    echo ' · ' . esc_html((string) $data['author']['name']);
                }
                echo '</span></div>';

                echo '<div class="dcct-list__actions">';
                echo '<a class="dc-btn dc-btn--secondary" href="' . esc_url($edit_author) . '" title="' . esc_attr__('Autor', 'design-cart-cloud-timeline') . '"><i class="fa fa-user"></i></a>';
                echo '<a class="dc-btn dc-btn--secondary" href="' . esc_url($edit_entries) . '" title="' . esc_attr__('Wpisy', 'design-cart-cloud-timeline') . '"><i class="fa fa-list-ul"></i></a>';
                echo '<a class="dc-btn dc-btn--secondary" href="' . esc_url($edit_settings) . '" title="' . esc_attr__('Ustawienia', 'design-cart-cloud-timeline') . '"><i class="fa fa-cog"></i></a>';
                echo '<a class="dc-btn dc-btn--secondary" href="' . esc_url($copy) . '" title="' . esc_attr__('Kopiuj', 'design-cart-cloud-timeline') . '"><i class="fa fa-files-o"></i></a>';
                echo '<a class="dc-btn dc-btn--secondary dcct-delete" href="' . esc_url($delete) . '" title="' . esc_attr__('Usuń', 'design-cart-cloud-timeline') . '"><i class="fa fa-trash"></i></a>';
                echo '</div></div>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p class="dc-hint">' . esc_html__('Brak timeline. Kliknij „Dodaj timeline” lub wybierz kafel sekcji.', 'design-cart-cloud-timeline') . '</p>';
        }

        DCCT_Admin_Helpers::section_end();
        echo '</div></div></div></div>';
        DCCT_Admin_Helpers::page_end();
    }
}
