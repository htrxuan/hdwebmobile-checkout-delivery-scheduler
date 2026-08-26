<?php

namespace htrxuan\hddts;

if (!defined('ABSPATH')) {
    exit;
}

class HDDTS_Admin
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
        require_once HDDTS_PLUGIN_DIR . 'includes/class-hddts-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['delivery-date-time-slot'] = array(
            'label'  => __('Delivery Date & Time Slot', 'hdwebmobile-delivery-date-time-slot'),
            'order'  => 40,
            'render' => array($this, 'render_settings_page'),
        );
        return $tabs;
    }

    public function render_settings_page()
    {
        ?>
        <p><?php esc_html_e('Let customers pick a delivery date and time slot at checkout. Works correctly on both classic and block-based Checkout.', 'hdwebmobile-delivery-date-time-slot'); ?></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('hddts_option_group');
            do_settings_sections('hddts-settings');
            submit_button();
            ?>
        </form>
        <?php
    }

    public function page_init()
    {
        register_setting(
            'hddts_option_group',
            'hddts_options',
            array(
                'type'              => 'array',
                'sanitize_callback' => array($this, 'sanitize'),
                'default'           => array(),
            )
        );

        add_settings_section(
            'hddts_section_general',
            __('General', 'hdwebmobile-delivery-date-time-slot'),
            '__return_false',
            'hddts-settings'
        );

        add_settings_field('enabled', __('Enable Delivery Date & Time Slot', 'hdwebmobile-delivery-date-time-slot'), array($this, 'enabled_callback'), 'hddts-settings', 'hddts_section_general');
        add_settings_field('lead_days', __('Minimum days ahead', 'hdwebmobile-delivery-date-time-slot'), array($this, 'lead_days_callback'), 'hddts-settings', 'hddts_section_general');
        add_settings_field('days_ahead', __('Days to offer', 'hdwebmobile-delivery-date-time-slot'), array($this, 'days_ahead_callback'), 'hddts-settings', 'hddts_section_general');
        add_settings_field('blocked_weekdays', __('Blocked weekdays', 'hdwebmobile-delivery-date-time-slot'), array($this, 'blocked_weekdays_callback'), 'hddts-settings', 'hddts_section_general');
        add_settings_field('blackout_dates', __('Blackout dates', 'hdwebmobile-delivery-date-time-slot'), array($this, 'blackout_dates_callback'), 'hddts-settings', 'hddts_section_general');
        add_settings_field('time_slots', __('Time slots', 'hdwebmobile-delivery-date-time-slot'), array($this, 'time_slots_callback'), 'hddts-settings', 'hddts_section_general');
        add_settings_field('date_field_label', __('"Delivery date" field label', 'hdwebmobile-delivery-date-time-slot'), array($this, 'date_field_label_callback'), 'hddts-settings', 'hddts_section_general');
        add_settings_field('slot_field_label', __('"Delivery time slot" field label', 'hdwebmobile-delivery-date-time-slot'), array($this, 'slot_field_label_callback'), 'hddts-settings', 'hddts_section_general');
    }

    public static function get_options()
    {
        $defaults = array(
            'enabled'          => 1,
            'lead_days'        => 1,
            'days_ahead'       => 14,
            'blocked_weekdays' => array(),
            'blackout_dates'   => array(),
            'time_slots'       => array(
                '9:00 AM - 12:00 PM',
                '12:00 PM - 3:00 PM',
                '3:00 PM - 6:00 PM',
            ),
            'date_field_label' => 'Delivery date',
            'slot_field_label' => 'Delivery time slot',
        );

        return wp_parse_args(get_option('hddts_options', array()), $defaults);
    }

    public function sanitize($input)
    {
        $new_input = array();

        $new_input['enabled']    = isset($input['enabled']) ? 1 : 0;
        $new_input['lead_days']  = isset($input['lead_days']) ? max(0, absint($input['lead_days'])) : 1;
        $new_input['days_ahead'] = isset($input['days_ahead']) ? max(1, absint($input['days_ahead'])) : 14;

        $new_input['blocked_weekdays'] = isset($input['blocked_weekdays']) && is_array($input['blocked_weekdays'])
            ? array_values(array_intersect(array_map('absint', $input['blocked_weekdays']), range(0, 6)))
            : array();

        $new_input['blackout_dates'] = isset($input['blackout_dates'])
            ? self::sanitize_date_lines($input['blackout_dates'])
            : array();

        $new_input['time_slots'] = isset($input['time_slots'])
            ? self::sanitize_text_lines($input['time_slots'])
            : array();

        $new_input['date_field_label'] = isset($input['date_field_label']) ? sanitize_text_field($input['date_field_label']) : __('Delivery date', 'hdwebmobile-delivery-date-time-slot');
        $new_input['slot_field_label'] = isset($input['slot_field_label']) ? sanitize_text_field($input['slot_field_label']) : __('Delivery time slot', 'hdwebmobile-delivery-date-time-slot');

        return $new_input;
    }

    private static function sanitize_text_lines($raw)
    {
        $lines = preg_split('/[\r\n]+/', (string) $raw);
        $lines = array_map('sanitize_text_field', $lines);
        $lines = array_map('trim', $lines);
        return array_values(array_filter($lines, function ($line) {
            return '' !== $line;
        }));
    }

    private static function sanitize_date_lines($raw)
    {
        $lines = self::sanitize_text_lines($raw);
        return array_values(array_filter($lines, function ($line) {
            // Basic Y-m-d shape check; date_create() rejects anything that isn't a real date.
            return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $line) && false !== date_create($line);
        }));
    }

    public function enabled_callback()
    {
        $options = self::get_options();
        printf(
            '<input type="checkbox" name="hddts_options[enabled]" value="1" %s />',
            checked(1, $options['enabled'], false)
        );
    }

    public function lead_days_callback()
    {
        $options = self::get_options();
        printf(
            '<input type="number" min="0" name="hddts_options[lead_days]" value="%s" class="small-text" /> %s',
            esc_attr($options['lead_days']),
            esc_html__('days from today before the earliest offered delivery date.', 'hdwebmobile-delivery-date-time-slot')
        );
    }

    public function days_ahead_callback()
    {
        $options = self::get_options();
        printf(
            '<input type="number" min="1" name="hddts_options[days_ahead]" value="%s" class="small-text" /> %s',
            esc_attr($options['days_ahead']),
            esc_html__('valid delivery dates to offer in the dropdown.', 'hdwebmobile-delivery-date-time-slot')
        );
    }

    public function blocked_weekdays_callback()
    {
        $options = self::get_options();
        $days = array(
            0 => __('Sunday', 'hdwebmobile-delivery-date-time-slot'),
            1 => __('Monday', 'hdwebmobile-delivery-date-time-slot'),
            2 => __('Tuesday', 'hdwebmobile-delivery-date-time-slot'),
            3 => __('Wednesday', 'hdwebmobile-delivery-date-time-slot'),
            4 => __('Thursday', 'hdwebmobile-delivery-date-time-slot'),
            5 => __('Friday', 'hdwebmobile-delivery-date-time-slot'),
            6 => __('Saturday', 'hdwebmobile-delivery-date-time-slot'),
        );
        foreach ($days as $value => $label) {
            printf(
                '<label style="margin-right:1em;"><input type="checkbox" name="hddts_options[blocked_weekdays][]" value="%1$d" %2$s /> %3$s</label>',
                (int) $value,
                checked(in_array($value, $options['blocked_weekdays'], true), true, false),
                esc_html($label)
            );
        }
    }

    public function blackout_dates_callback()
    {
        $options = self::get_options();
        printf(
            '<textarea name="hddts_options[blackout_dates]" rows="4" class="regular-text" placeholder="%s">%s</textarea><p class="description">%s</p>',
            esc_attr__('2026-12-25', 'hdwebmobile-delivery-date-time-slot'),
            esc_textarea(implode("\n", $options['blackout_dates'])),
            esc_html__('One date per line, in YYYY-MM-DD format (e.g. holidays).', 'hdwebmobile-delivery-date-time-slot')
        );
    }

    public function time_slots_callback()
    {
        $options = self::get_options();
        printf(
            '<textarea name="hddts_options[time_slots]" rows="4" class="regular-text">%s</textarea><p class="description">%s</p>',
            esc_textarea(implode("\n", $options['time_slots'])),
            esc_html__('One time slot per line (e.g. "9:00 AM - 12:00 PM"). Offered on every valid date.', 'hdwebmobile-delivery-date-time-slot')
        );
    }

    public function date_field_label_callback()
    {
        $options = self::get_options();
        printf('<input type="text" name="hddts_options[date_field_label]" value="%s" class="regular-text" />', esc_attr($options['date_field_label']));
    }

    public function slot_field_label_callback()
    {
        $options = self::get_options();
        printf('<input type="text" name="hddts_options[slot_field_label]" value="%s" class="regular-text" />', esc_attr($options['slot_field_label']));
    }
}
