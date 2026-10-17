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

final class DCCT_Admin_Form {
    public static function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $timeline_id = DCCT_Admin_Helpers::query_id('id');
        $section = DCCT_Admin_Helpers::query_text('section');
        if (!in_array($section, ['author', 'entries', 'settings'], true)) {
            $section = 'author';
        }

        $data = DCCT_Data::get_timeline($timeline_id);
        $is_edit = $timeline_id > 0;
        $page_title = $is_edit
            ? __('Edytuj timeline', 'design-cart-cloud-timeline')
            : __('Dodaj timeline', 'design-cart-cloud-timeline');

        $tab_map = [
            'author' => 'tab-author',
            'entries' => 'tab-entries',
            'settings' => 'tab-settings',
        ];
        $active_tab = $tab_map[$section];

        DCCT_Admin_Helpers::page_start($page_title);
        DCCT_Admin_Helpers::render_hero(
            $page_title,
            [
                [
                    'form' => 'dcct-form',
                    'label' => __('Zapisz', 'design-cart-cloud-timeline'),
                    'icon' => 'fa fa-save',
                    'class' => 'dc-btn dc-btn--light',
                ],
                [
                    'href' => admin_url('admin.php?page=' . DCCT_Admin::PAGE_DASH),
                    'label' => '',
                    'icon' => 'fa fa-arrow-left',
                    'class' => 'dc-btn dc-btn--ghost',
                    'title' => __('Wróć do kokpitu', 'design-cart-cloud-timeline'),
                ],
            ],
            [
                ['href' => admin_url('plugins.php'), 'label' => __('Wtyczki', 'design-cart-cloud-timeline')],
                ['href' => admin_url('admin.php?page=' . DCCT_Admin::PAGE_DASH), 'label' => __('Cloud Timeline', 'design-cart-cloud-timeline')],
                ['label' => $page_title],
            ]
        );

        echo '<div class="dc-page__wrap"><div class="dc-form-card"><div class="dc-interface">';
        DCCT_Admin_Helpers::render_notices();

        echo '<form class="dc-form dc-form--full" id="dcct-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="dcct_save">';
        echo '<input type="hidden" name="timeline_id" value="' . esc_attr((string) $timeline_id) . '">';
        echo '<input type="hidden" name="active_section" id="dcct-active-section" value="' . esc_attr($section) . '">';
        wp_nonce_field('dcct_save');

        DCCT_Admin_Helpers::render_nav(
            [
                'tab-author' => ['fa fa-user', __('Autor', 'design-cart-cloud-timeline')],
                'tab-entries' => ['fa fa-list-ul', __('Wpisy timeline', 'design-cart-cloud-timeline')],
                'tab-settings' => ['fa fa-cog', __('Ustawienia', 'design-cart-cloud-timeline')],
            ],
            $active_tab
        );

        echo '<div class="dc-form-card__body">';

        // Title stays outside tabs so HTML5 validation works from every section (incl. Settings).
        DCCT_Admin_Helpers::section_start(
            'fa fa-tag',
            __('Nazwa timeline', 'design-cart-cloud-timeline'),
            __('Widoczna tylko w panelu administracyjnym', 'design-cart-cloud-timeline')
        );
        DCCT_Admin_Helpers::text_field(
            'title',
            'timeline-title',
            __('Nazwa', 'design-cart-cloud-timeline'),
            $data['title'],
            true
        );
        DCCT_Admin_Helpers::section_end();

        self::render_author_tab($data['author'], $active_tab === 'tab-author');
        self::render_entries_tab($data['entries'], $active_tab === 'tab-entries');
        self::render_settings_tab($data['settings'], $active_tab === 'tab-settings');

        echo '<div class="dc-actions">';
        echo '<button type="submit" class="dc-btn dc-btn--primary"><i class="fa fa-save"></i> ' .
            esc_html__('Zapisz timeline', 'design-cart-cloud-timeline') . '</button>';
        echo '</div></div></form></div></div></div>';

        self::render_entry_template();
        DCCT_Admin_Helpers::page_end();
    }

    /**
     * @param array<string, mixed> $author
     */
    private static function render_author_tab(array $author, bool $active): void {
        $cls = $active ? ' dc-active' : '';
        echo '<div id="tab-author" class="dc-tab-panel' . esc_attr($cls) . '" role="tabpanel" data-dcct-section="author">';

        DCCT_Admin_Helpers::section_start(
            'fa fa-user',
            __('Autor — biografia', 'design-cart-cloud-timeline'),
            __('Lewy sticky box na froncie: zdjęcie, imię i opis', 'design-cart-cloud-timeline')
        );

        DCCT_Admin_Helpers::text_field(
            'author[name]',
            'author-name',
            __('Imię / nazwa autora', 'design-cart-cloud-timeline'),
            (string) ($author['name'] ?? '')
        );

        DCCT_Admin_Helpers::media_field(
            'author[image_id]',
            'author-image',
            __('Zdjęcie główne autora', 'design-cart-cloud-timeline'),
            (int) ($author['image_id'] ?? 0)
        );

        echo '<div class="dc-field">';
        echo '<label class="dc-label" for="author-bio">' . esc_html__('Biografia / opis', 'design-cart-cloud-timeline') . '</label>';
        wp_editor(
            (string) ($author['bio'] ?? ''),
            'author-bio',
            [
                'textarea_name' => 'author[bio]',
                'textarea_rows' => 12,
                'media_buttons' => true,
                'teeny' => false,
                'quicktags' => true,
            ]
        );
        echo '</div>';

        DCCT_Admin_Helpers::section_end();
        echo '</div>';
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     */
    private static function render_entries_tab(array $entries, bool $active): void {
        $cls = $active ? ' dc-active' : '';
        echo '<div id="tab-entries" class="dc-tab-panel' . esc_attr($cls) . '" role="tabpanel" data-dcct-section="entries">';

        DCCT_Admin_Helpers::section_start(
            'fa fa-list-ul',
            __('Wpisy timeline', 'design-cart-cloud-timeline'),
            __('Dodawaj kafle dynamicznie — tytuł, zdjęcie (media WP) i treść', 'design-cart-cloud-timeline')
        );

        echo '<div class="dcct-entries" id="dcct-entries">';
        if ($entries) {
            foreach ($entries as $index => $entry) {
                self::render_entry_row((int) $index, $entry);
            }
        }
        echo '</div>';

        echo '<button type="button" class="dc-btn dc-btn--secondary" id="dcct-add-entry">';
        echo '<i class="fa fa-plus"></i> ' . esc_html__('Dodaj wpis', 'design-cart-cloud-timeline');
        echo '</button>';

        DCCT_Admin_Helpers::section_end();
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $entry
     */
    private static function render_entry_row(int $index, array $entry): void {
        $image_id = (int) ($entry['image_id'] ?? 0);
        $url = $image_id > 0 ? DCCT_Data::image_url($image_id, 'medium') : '';

        echo '<div class="dcct-entry" data-index="' . esc_attr((string) $index) . '">';
        echo '<div class="dcct-entry__head">';
        echo '<span class="dcct-entry__handle" title="' . esc_attr__('Przeciągnij', 'design-cart-cloud-timeline') . '"><i class="fa fa-bars"></i></span>';
        echo '<strong class="dcct-entry__num">' . esc_html((string) ($index + 1)) . '</strong>';
        echo '<button type="button" class="dc-btn dc-btn--ghost dcct-entry__remove" title="' . esc_attr__('Usuń', 'design-cart-cloud-timeline') . '"><i class="fa fa-trash"></i></button>';
        echo '</div>';

        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::text_field(
            'entries[' . $index . '][title]',
            'entry-title-' . $index,
            __('Tytuł', 'design-cart-cloud-timeline'),
            (string) ($entry['title'] ?? '')
        );
        DCCT_Admin_Helpers::text_field(
            'entries[' . $index . '][date]',
            'entry-date-' . $index,
            __('Data / etykieta', 'design-cart-cloud-timeline'),
            (string) ($entry['date'] ?? '')
        );
        echo '</div>';

        echo '<div class="dc-field dcct-media" data-dcct-media>';
        echo '<label class="dc-label">' . esc_html__('Zdjęcie', 'design-cart-cloud-timeline') . '</label>';
        echo '<div class="dcct-media__preview">';
        if ($url !== '') {
            echo '<img src="' . esc_url($url) . '" alt="">';
        }
        echo '</div>';
        echo '<input type="hidden" class="dcct-media__id" name="entries[' . esc_attr((string) $index) . '][image_id]" value="' . esc_attr((string) $image_id) . '">';
        echo '<div class="dcct-media__actions">';
        echo '<button type="button" class="dc-btn dc-btn--secondary dcct-media__select"><i class="fa fa-folder-open"></i> ' .
            esc_html__('Wybierz zdjęcie', 'design-cart-cloud-timeline') . '</button>';
        echo '<button type="button" class="dc-btn dc-btn--ghost dcct-media__clear"><i class="fa fa-times"></i></button>';
        echo '</div></div>';

        echo '<div class="dc-field">';
        echo '<label class="dc-label">' . esc_html__('Treść', 'design-cart-cloud-timeline') . '</label>';
        echo '<textarea class="dc-input dc-textarea" name="entries[' . esc_attr((string) $index) . '][content]" rows="4">' .
            esc_textarea((string) ($entry['content'] ?? '')) . '</textarea>';
        echo '</div>';

        echo '</div>';
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function render_settings_tab(array $settings, bool $active): void {
        $cls = $active ? ' dc-active' : '';
        echo '<div id="tab-settings" class="dc-tab-panel' . esc_attr($cls) . '" role="tabpanel" data-dcct-section="settings">';

        DCCT_Admin_Helpers::section_start(
            'fa fa-header',
            __('Nagłówki na froncie', 'design-cart-cloud-timeline'),
            __('Tytuł i podtytuł nad timeline', 'design-cart-cloud-timeline')
        );
        DCCT_Admin_Helpers::text_field(
            'settings[heading]',
            'settings-heading',
            __('Tytuł sekcji', 'design-cart-cloud-timeline'),
            (string) ($settings['heading'] ?? '')
        );
        DCCT_Admin_Helpers::text_field(
            'settings[subheading]',
            'settings-subheading',
            __('Podtytuł', 'design-cart-cloud-timeline'),
            (string) ($settings['subheading'] ?? '')
        );
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::dimension_field('settings[heading_size]', __('Rozmiar tytułu', 'design-cart-cloud-timeline'), (int) $settings['heading_size'], 12, 72);
        DCCT_Admin_Helpers::dimension_field('settings[subheading_size]', __('Rozmiar podtytułu', 'design-cart-cloud-timeline'), (int) $settings['subheading_size'], 10, 32);
        echo '</div>';
        DCCT_Admin_Helpers::section_end();

        DCCT_Admin_Helpers::section_start(
            'fa fa-font',
            __('Typografia — autor i wpisy', 'design-cart-cloud-timeline'),
            __('Wielkości czcionek i kolory tekstu autora', 'design-cart-cloud-timeline')
        );
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::dimension_field('settings[author_name_size]', __('Nazwa autora', 'design-cart-cloud-timeline'), (int) $settings['author_name_size'], 12, 48);
        DCCT_Admin_Helpers::dimension_field('settings[author_bio_size]', __('Treść o autorze', 'design-cart-cloud-timeline'), (int) $settings['author_bio_size'], 10, 28);
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::color_field(
            'settings[author_name_color]',
            __('Kolor nazwy autora', 'design-cart-cloud-timeline'),
            (string) ($settings['author_name_color'] ?? '#ffffff')
        );
        DCCT_Admin_Helpers::color_field(
            'settings[author_bio_color]',
            __('Kolor tekstu bio', 'design-cart-cloud-timeline'),
            (string) ($settings['author_bio_color'] ?? '#ffffff')
        );
        echo '</div>';
        DCCT_Admin_Helpers::color_field(
            'settings[author_bg]',
            __('Tło kolumny autora / bio', 'design-cart-cloud-timeline'),
            (string) ($settings['author_bg'] ?? '#1a1d38')
        );
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::color_field(
            'settings[heading_color]',
            __('Kolor tytułu sekcji', 'design-cart-cloud-timeline'),
            (string) ($settings['heading_color'] ?? '#ffffff')
        );
        DCCT_Admin_Helpers::color_field(
            'settings[subheading_color]',
            __('Kolor podtytułu', 'design-cart-cloud-timeline'),
            (string) ($settings['subheading_color'] ?? '#ffffff')
        );
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::dimension_field('settings[entry_title_size]', __('Tytuł wpisu', 'design-cart-cloud-timeline'), (int) $settings['entry_title_size'], 10, 32);
        DCCT_Admin_Helpers::dimension_field('settings[entry_date_size]', __('Data wpisu', 'design-cart-cloud-timeline'), (int) $settings['entry_date_size'], 10, 24);
        echo '</div>';
        DCCT_Admin_Helpers::dimension_field('settings[entry_content_size]', __('Treść wpisu', 'design-cart-cloud-timeline'), (int) $settings['entry_content_size'], 10, 24);
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::dimension_field('settings[author_width]', __('Szerokość boxu autora', 'design-cart-cloud-timeline'), (int) $settings['author_width'], 180, 480);
        DCCT_Admin_Helpers::dimension_field(
            'settings[author_avatar_size]',
            __('Rozmiar avatara', 'design-cart-cloud-timeline'),
            (int) ($settings['author_avatar_size'] ?? 160),
            64,
            400
        );
        echo '</div>';
        DCCT_Admin_Helpers::dimension_field('settings[layout_gap]', __('Odstęp layoutu', 'design-cart-cloud-timeline'), (int) $settings['layout_gap'], 12, 80);
        DCCT_Admin_Helpers::section_end();

        DCCT_Admin_Helpers::section_start(
            'fa fa-moon-o',
            __('Cienie — bio i chmurki', 'design-cart-cloud-timeline'),
            __('Rozmycie, przesunięcie i moc cienia (0 = wyłączony)', 'design-cart-cloud-timeline')
        );
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::dimension_field(
            'settings[author_shadow_blur]',
            __('Bio — rozmycie cienia', 'design-cart-cloud-timeline'),
            (int) ($settings['author_shadow_blur'] ?? 28),
            0,
            80
        );
        DCCT_Admin_Helpers::number_field(
            'settings[author_shadow_opacity]',
            'settings-author-shadow-opacity',
            __('Bio — moc cienia (%)', 'design-cart-cloud-timeline'),
            (int) ($settings['author_shadow_opacity'] ?? 18),
            0,
            100
        );
        echo '</div>';
        echo '<div class="dc-row dc-row--3">';
        DCCT_Admin_Helpers::dimension_field(
            'settings[cloud_shadow_blur]',
            __('Chmurki — rozmycie', 'design-cart-cloud-timeline'),
            (int) ($settings['cloud_shadow_blur'] ?? 18),
            0,
            60
        );
        DCCT_Admin_Helpers::dimension_field(
            'settings[cloud_shadow_y]',
            __('Chmurki — przesunięcie Y', 'design-cart-cloud-timeline'),
            (int) ($settings['cloud_shadow_y'] ?? 10),
            0,
            40
        );
        DCCT_Admin_Helpers::number_field(
            'settings[cloud_shadow_opacity]',
            'settings-cloud-shadow-opacity',
            __('Chmurki — moc (%)', 'design-cart-cloud-timeline'),
            (int) ($settings['cloud_shadow_opacity'] ?? 35),
            0,
            100
        );
        echo '</div>';
        DCCT_Admin_Helpers::section_end();

        DCCT_Admin_Helpers::section_start(
            'fa fa-arrows',
            __('Orientacja timeline', 'design-cart-cloud-timeline'),
            __('Jak w wtyczce dc-cloud-timeline', 'design-cart-cloud-timeline')
        );
        DCCT_Admin_Helpers::radio_group(
            'settings[orientation]',
            __('Układ', 'design-cart-cloud-timeline'),
            [
                'v' => __('Pionowa', 'design-cart-cloud-timeline'),
                'h' => __('Pozioma', 'design-cart-cloud-timeline'),
            ],
            (string) $settings['orientation']
        );
        DCCT_Admin_Helpers::section_end();

        DCCT_Admin_Helpers::section_start(
            'fa fa-paint-brush',
            __('Kolory timeline', 'design-cart-cloud-timeline'),
            __('Paleta z dc-cloud-timeline', 'design-cart-cloud-timeline')
        );
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::color_field('settings[bg]', __('Tło', 'design-cart-cloud-timeline'), (string) $settings['bg']);
        DCCT_Admin_Helpers::color_field('settings[accent]', __('Akcent i daty', 'design-cart-cloud-timeline'), (string) $settings['accent']);
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::color_field('settings[bar1]', __('Belka — początek', 'design-cart-cloud-timeline'), (string) $settings['bar1']);
        DCCT_Admin_Helpers::color_field('settings[bar2]', __('Belka — koniec', 'design-cart-cloud-timeline'), (string) $settings['bar2']);
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DCCT_Admin_Helpers::color_field('settings[line]', __('Linie', 'design-cart-cloud-timeline'), (string) $settings['line']);
        DCCT_Admin_Helpers::color_field('settings[card]', __('Chmurki', 'design-cart-cloud-timeline'), (string) $settings['card']);
        echo '</div>';
        DCCT_Admin_Helpers::color_field('settings[text]', __('Tekst w chmurkach', 'design-cart-cloud-timeline'), (string) $settings['text']);
        DCCT_Admin_Helpers::section_end();

        DCCT_Admin_Helpers::section_start(
            'fa fa-magic',
            __('Animacja', 'design-cart-cloud-timeline'),
            __('Falowanie, prędkość fali, dryf chmurek', 'design-cart-cloud-timeline')
        );
        echo '<div class="dc-row dc-row--3">';
        DCCT_Admin_Helpers::number_field('settings[amp]', 'settings-amp', __('Falowanie (0–24)', 'design-cart-cloud-timeline'), (int) $settings['amp'], 0, 24);
        DCCT_Admin_Helpers::number_field('settings[speed]', 'settings-speed', __('Prędkość fali', 'design-cart-cloud-timeline'), (float) $settings['speed'], 0, 3, '0.1');
        DCCT_Admin_Helpers::number_field('settings[drift]', 'settings-drift', __('Dryf chmurek (0–30)', 'design-cart-cloud-timeline'), (int) $settings['drift'], 0, 30);
        echo '</div>';
        DCCT_Admin_Helpers::section_end();

        echo '</div>';
    }

    private static function render_entry_template(): void {
        ?>
        <template id="dcct-entry-template">
            <div class="dcct-entry" data-index="0">
                <div class="dcct-entry__head">
                    <span class="dcct-entry__handle"><i class="fa fa-bars"></i></span>
                    <strong class="dcct-entry__num">1</strong>
                    <button type="button" class="dc-btn dc-btn--ghost dcct-entry__remove"><i class="fa fa-trash"></i></button>
                </div>
                <div class="dc-row dc-row--2">
                    <div class="dc-field">
                        <label class="dc-label"><?php echo esc_html__('Tytuł', 'design-cart-cloud-timeline'); ?></label>
                        <input class="dc-input" type="text" name="entries[0][title]" value="">
                    </div>
                    <div class="dc-field">
                        <label class="dc-label"><?php echo esc_html__('Data / etykieta', 'design-cart-cloud-timeline'); ?></label>
                        <input class="dc-input" type="text" name="entries[0][date]" value="">
                    </div>
                </div>
                <div class="dc-field dcct-media" data-dcct-media>
                    <label class="dc-label"><?php echo esc_html__('Zdjęcie', 'design-cart-cloud-timeline'); ?></label>
                    <div class="dcct-media__preview"></div>
                    <input type="hidden" class="dcct-media__id" name="entries[0][image_id]" value="">
                    <div class="dcct-media__actions">
                        <button type="button" class="dc-btn dc-btn--secondary dcct-media__select"><i class="fa fa-folder-open"></i> <?php echo esc_html__('Wybierz zdjęcie', 'design-cart-cloud-timeline'); ?></button>
                        <button type="button" class="dc-btn dc-btn--ghost dcct-media__clear"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="dc-field">
                    <label class="dc-label"><?php echo esc_html__('Treść', 'design-cart-cloud-timeline'); ?></label>
                    <textarea class="dc-input dc-textarea" name="entries[0][content]" rows="4"></textarea>
                </div>
            </div>
        </template>
        <?php
    }
}
