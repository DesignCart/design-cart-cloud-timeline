<?php
/**
 * Design Cart Cloud Timeline
 * Code: design-cart-cloud-timeline
 * Autor: Paweł Nosko — https://www.designcart.pl/pawel-nosko.html
 * Firma: Design Cart — https://www.designcart.pl/
 *
 * Plugin Name:       Design Cart Cloud Timeline
 * Plugin URI:        https://www.designcart.pl/
 * Description:       Sticky author biography with animated cloud timeline. Manage multiple timelines and embed them with a shortcode.
 * Version:           1.0.5
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Paweł Nosko
 * Author URI:        https://www.designcart.pl/pawel-nosko.html
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       design-cart-cloud-timeline
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DCCT_VERSION', '1.0.5');
define('DCCT_FILE', __FILE__);
define('DCCT_DIR', plugin_dir_path(__FILE__));
define('DCCT_URL', plugin_dir_url(__FILE__));
define('DCCT_BASENAME', plugin_basename(__FILE__));

require_once DCCT_DIR . 'includes/class-data.php';
require_once DCCT_DIR . 'includes/sample/class-sample.php';
require_once DCCT_DIR . 'includes/admin/class-admin-helpers.php';
require_once DCCT_DIR . 'includes/admin/class-admin-handlers.php';
require_once DCCT_DIR . 'includes/admin/class-admin-dashboard.php';
require_once DCCT_DIR . 'includes/admin/class-admin-form.php';
require_once DCCT_DIR . 'includes/admin/class-admin.php';
require_once DCCT_DIR . 'includes/frontend/class-frontend.php';
require_once DCCT_DIR . 'includes/class-plugin.php';

register_activation_hook(__FILE__, ['DCCT_Plugin', 'activate']);

DCCT_Plugin::instance()->boot();
