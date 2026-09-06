<?php

namespace htrxuan\hddts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classic (shortcode) Checkout support. Confirmed directly in WooCommerce core that
 * woocommerce_register_additional_checkout_field() is a block-checkout-only mechanism (no
 * reference to it anywhere in includes/class-wc-checkout.php or the classic checkout
 * templates), so classic checkout needs this separate, traditional hook-based implementation.
 * Saves to the exact same `_wc_other/{field_id}` order-meta keys the Blocks API uses (confirmed
 * in CheckoutFields.php), so admin/email display is unified regardless of which checkout a
 * shopper used.
 */
class HDDTS_Checkout_Classic
{

    private static $instance = null;

    const META_PREFIX = '_wc_other/';

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('woocommerce_before_order_notes', array($this, 'render_fields'));
        add_action('woocommerce_checkout_process', array($this, 'validate_fields'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_fields'));
    }

    public function render_fields($checkout)
    {
        $dates = HDDTS_Availability::get_valid_dates();
        $slots = HDDTS_Availability::get_time_slots();

        if (empty($dates) || empty($slots)) {
            return;
        }

        $options = HDDTS_Admin::get_options();

        echo '<div class="hddts-checkout-fields">';

        woocommerce_form_field(HDDTS_Checkout_Blocks::DATE_FIELD_ID, array(
            'type'     => 'select',
            'label'    => $options['date_field_label'],
            'required' => true,
            'options'  => array('' => esc_html__('Select a date&hellip;', 'hdwebmobile-checkout-delivery-scheduler')) + self::pairs($dates),
        ), $checkout->get_value(HDDTS_Checkout_Blocks::DATE_FIELD_ID));

        woocommerce_form_field(HDDTS_Checkout_Blocks::SLOT_FIELD_ID, array(
            'type'     => 'select',
            'label'    => $options['slot_field_label'],
            'required' => true,
            'options'  => array('' => esc_html__('Select a time slot&hellip;', 'hdwebmobile-checkout-delivery-scheduler')) + self::pairs($slots),
        ), $checkout->get_value(HDDTS_Checkout_Blocks::SLOT_FIELD_ID));

        echo '</div>';
    }

    private static function pairs($items)
    {
        $pairs = array();
        foreach ($items as $item) {
            $pairs[$item['value']] = $item['label'];
        }
        return $pairs;
    }

    public function validate_fields()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce's own checkout nonce is verified upstream by WC_Checkout::process_checkout() before this action fires.
        $date = isset($_POST[HDDTS_Checkout_Blocks::DATE_FIELD_ID]) ? sanitize_text_field(wp_unslash($_POST[HDDTS_Checkout_Blocks::DATE_FIELD_ID])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce's own checkout nonce is verified upstream by WC_Checkout::process_checkout() before this action fires.
        $slot = isset($_POST[HDDTS_Checkout_Blocks::SLOT_FIELD_ID]) ? sanitize_text_field(wp_unslash($_POST[HDDTS_Checkout_Blocks::SLOT_FIELD_ID])) : '';

        if (!HDDTS_Availability::is_valid_date($date)) {
            wc_add_notice(__('Please choose a valid delivery date.', 'hdwebmobile-checkout-delivery-scheduler'), 'error');
        }

        if (!HDDTS_Availability::is_valid_slot($slot)) {
            wc_add_notice(__('Please choose a valid delivery time slot.', 'hdwebmobile-checkout-delivery-scheduler'), 'error');
        }
    }

    public function save_fields($order_id)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce's own checkout nonce is verified upstream by WC_Checkout::process_checkout() before this action fires.
        $date = isset($_POST[HDDTS_Checkout_Blocks::DATE_FIELD_ID]) ? sanitize_text_field(wp_unslash($_POST[HDDTS_Checkout_Blocks::DATE_FIELD_ID])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce's own checkout nonce is verified upstream by WC_Checkout::process_checkout() before this action fires.
        $slot = isset($_POST[HDDTS_Checkout_Blocks::SLOT_FIELD_ID]) ? sanitize_text_field(wp_unslash($_POST[HDDTS_Checkout_Blocks::SLOT_FIELD_ID])) : '';

        if (!HDDTS_Availability::is_valid_date($date) || !HDDTS_Availability::is_valid_slot($slot)) {
            // validate_fields() already blocked checkout via wc_add_notice() if invalid;
            // this is just a final guard against saving bad data if that hook was bypassed.
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $order->update_meta_data(self::META_PREFIX . HDDTS_Checkout_Blocks::DATE_FIELD_ID, $date);
        $order->update_meta_data(self::META_PREFIX . HDDTS_Checkout_Blocks::SLOT_FIELD_ID, $slot);
        $order->save();
    }
}
