<?php

namespace htrxuan\hddts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the Delivery Date and Delivery Time Slot fields on the block-based Checkout via
 * woocommerce_register_additional_checkout_field() -- confirmed directly in WooCommerce core
 * source (Blocks/Domain/Services/functions.php + CheckoutFields.php) to be the correct, native
 * way to add a field there; WooCommerce's own Checkout block renders it, no custom React/JS
 * needed. Only 'text'/'select'/'checkbox' field types exist (no native date-input type,
 * confirmed via CheckoutFields::$supported_field_types), so both fields are 'select' dropdowns
 * populated from HDDTS_Availability -- the same source the classic-checkout path uses, so the
 * two checkout types can never offer different options.
 */
class HDDTS_Checkout_Blocks
{

    private static $instance = null;

    const DATE_FIELD_ID = 'hddts/delivery-date';
    const SLOT_FIELD_ID = 'hddts/delivery-slot';

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', array($this, 'register_fields'));
    }

    public function register_fields()
    {
        if (!function_exists('woocommerce_register_additional_checkout_field')) {
            return;
        }

        $options = HDDTS_Admin::get_options();

        $dates = self::to_field_options(HDDTS_Availability::get_valid_dates());
        $slots = self::to_field_options(HDDTS_Availability::get_time_slots());

        if (empty($dates) || empty($slots)) {
            // Nothing valid to offer (e.g. every upcoming day is blocked, or no slots
            // configured yet) -- registering a select field with zero options would just be
            // a broken required field the shopper can never satisfy.
            return;
        }

        woocommerce_register_additional_checkout_field(array(
            'id'                => self::DATE_FIELD_ID,
            'label'             => $options['date_field_label'],
            'location'          => 'order',
            'type'              => 'select',
            'required'          => true,
            'options'           => $dates,
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => array($this, 'validate_date'),
        ));

        woocommerce_register_additional_checkout_field(array(
            'id'                => self::SLOT_FIELD_ID,
            'label'             => $options['slot_field_label'],
            'location'          => 'order',
            'type'              => 'select',
            'required'          => true,
            'options'           => $slots,
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => array($this, 'validate_slot'),
        ));
    }

    private static function to_field_options($items)
    {
        $options = array();
        foreach ($items as $item) {
            $options[] = array('value' => $item['value'], 'label' => $item['label']);
        }
        return $options;
    }

    /**
     * Re-validates server-side against the live availability rules -- never trusts that a
     * submitted value actually came from the dropdown the shopper was shown, since the field
     * options were only current at page-load time.
     */
    public function validate_date($value)
    {
        if (!HDDTS_Availability::is_valid_date($value)) {
            return new \WP_Error('hddts_invalid_date', __('Please choose a valid delivery date.', 'hdwebmobile-delivery-date-time-slot'));
        }
        return true;
    }

    public function validate_slot($value)
    {
        if (!HDDTS_Availability::is_valid_slot($value)) {
            return new \WP_Error('hddts_invalid_slot', __('Please choose a valid delivery time slot.', 'hdwebmobile-delivery-date-time-slot'));
        }
        return true;
    }
}
