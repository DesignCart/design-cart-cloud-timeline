<?php
/**
 * Design Cart Cloud Timeline
 * Code: design-cart-cloud-timeline
 * Autor: Paweł Nosko — https://www.designcart.pl/pawel-nosko.html
 * Firma: Design Cart — https://www.designcart.pl/
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$posts = get_posts([
    'post_type' => 'dc_cloud_timeline',
    'numberposts' => -1,
    'post_status' => 'any',
    'fields' => 'ids',
]);

foreach ($posts as $post_id) {
    wp_delete_post((int) $post_id, true);
}
