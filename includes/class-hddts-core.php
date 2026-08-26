<?php

namespace htrxuan\hddts;

if (!defined('ABSPATH')) {
    exit;
}

final class HDDTS_Core
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->includes();
        $this->init_hooks();
    }

    private function __clone()
    {
    }

    private function includes()
    {
        require_once HDDTS_PLUGIN_DIR . 'includes/class-hddts-admin.php';
        require_once HDDTS_PLUGIN_DIR . 'includes/class-hddts-availability.php';
        require_once HDDTS_PLUGIN_DIR . 'includes/class-hddts-checkout-blocks.php';
        require_once HDDTS_PLUGIN_DIR . 'includes/class-hddts-checkout-classic.php';
    }

    private function init_hooks()
    {
        add_action('admin_notices', array($this, 'render_missing_woocommerce_notice'));

        if (!class_exists('WooCommerce')) {
            return;
        }

        $options = HDDTS_Admin::get_options();

        if (is_admin()) {
            HDDTS_Admin::get_instance();
        }

        if (!empty($options['enabled'])) {
            HDDTS_Checkout_Blocks::get_instance();
            HDDTS_Checkout_Classic::get_instance();
        }
    }

    public function render_missing_woocommerce_notice()
    {
        $screen = get_current_screen();
        if (!$screen || 'plugins' !== $screen->id) {
            return;
        }

        if (!get_transient('hddts_wc_missing_notice')) {
            return;
        }
        delete_transient('hddts_wc_missing_notice');
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php esc_html_e('HDWebmobile Delivery Date & Time Slot Picker requires WooCommerce to be installed and active. The plugin has been deactivated.', 'hdwebmobile-delivery-date-time-slot'); ?>
            </p>
        </div>
        <?php
    }
}
