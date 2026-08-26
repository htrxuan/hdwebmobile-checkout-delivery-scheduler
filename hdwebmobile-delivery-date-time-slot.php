<?php

/**
 * Plugin Name: HDWebmobile Delivery Date & Time Slot Picker
 * Plugin URI: https://hdwebmobile.com/plugins/hdwebmobile-delivery-date-time-slot/
 * Description: Lets customers pick a delivery date and time slot at checkout, with blocked weekdays and blackout dates. Works correctly on both classic and block-based Checkout.
 * Version: 1.0.0
 * Author: htrxuan - Han Tran
 * Author URI: https://hdwebmobile.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hdwebmobile-delivery-date-time-slot
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.9
 */

namespace htrxuan\hddts;

if (!defined('ABSPATH')) {
    exit;
}

define('HDDTS_VERSION', '1.0.0');
define('HDDTS_PLUGIN_FILE', __FILE__);
define('HDDTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HDDTS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once HDDTS_PLUGIN_DIR . 'includes/class-hddts-activator.php';

register_activation_hook(__FILE__, array(HDDTS_Activator::class, 'activate'));

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', HDDTS_PLUGIN_FILE, true);
    }
});

add_action('plugins_loaded', function () {
    require_once HDDTS_PLUGIN_DIR . 'includes/class-hddts-core.php';
    HDDTS_Core::get_instance();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $donate_link = '<a href="https://paypal.me/htrxuan/20" target="_blank" rel="noopener noreferrer">' . esc_html__('Donate', 'hdwebmobile-delivery-date-time-slot') . '</a>';
    array_unshift($links, $donate_link);
    return $links;
});
