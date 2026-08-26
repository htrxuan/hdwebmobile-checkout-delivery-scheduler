<?php

namespace htrxuan\hddts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The single source of truth for which delivery dates/slots are currently valid. Both the
 * block-checkout field registration and the classic-checkout render/validate code call into
 * this same class, so the two checkout paths can never disagree about what's offered or
 * silently accept a value the other would have rejected.
 */
class HDDTS_Availability
{

    /**
     * Returns the currently valid delivery dates as {value: 'Y-m-d', label: display text},
     * starting at (today + lead days) and walking forward through the configured window,
     * skipping blocked weekdays and blackout dates. Computed fresh on every call rather than
     * cached, so "today" is always correct.
     */
    public static function get_valid_dates()
    {
        $options          = HDDTS_Admin::get_options();
        $lead_days        = max(0, (int) $options['lead_days']);
        $days_ahead       = max(1, (int) $options['days_ahead']);
        $blocked_weekdays = array_map('absint', (array) $options['blocked_weekdays']);
        $blackout_dates   = (array) $options['blackout_dates'];

        $dates = array();
        $cursor = strtotime((int) $lead_days . ' days', current_time('timestamp')); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- local (site timezone) date math, not a data-storage timestamp.

        // Walk forward enough calendar days to yield $days_ahead valid entries, capped at a
        // generous ceiling so a misconfigured "block every weekday" setting can't loop forever.
        $checked = 0;
        $max_check = ($days_ahead + 14) * 3;

        while (count($dates) < $days_ahead && $checked < $max_check) {
            $date_string = gmdate('Y-m-d', $cursor);
            $weekday     = (int) gmdate('w', $cursor);

            if (!in_array($weekday, $blocked_weekdays, true) && !in_array($date_string, $blackout_dates, true)) {
                $dates[] = array(
                    'value' => $date_string,
                    'label' => date_i18n(get_option('date_format'), $cursor),
                );
            }

            $cursor += DAY_IN_SECONDS;
            $checked++;
        }

        return $dates;
    }

    /**
     * Returns the configured time slots as {value: slug, label: display text}. A stable slug
     * (sanitize_title of the label) is used as the stored value so relabeling a slot in
     * settings doesn't silently invalidate the exact string previously stored on old orders
     * in the same way a raw label match would.
     */
    public static function get_time_slots()
    {
        $options = HDDTS_Admin::get_options();
        $slots   = array();

        foreach ((array) $options['time_slots'] as $label) {
            $label = trim($label);
            if ('' === $label) {
                continue;
            }
            $slots[] = array(
                'value' => sanitize_title($label),
                'label' => $label,
            );
        }

        return $slots;
    }

    public static function is_valid_date($date_string)
    {
        foreach (self::get_valid_dates() as $date) {
            if ($date['value'] === $date_string) {
                return true;
            }
        }
        return false;
    }

    public static function is_valid_slot($slot_value)
    {
        foreach (self::get_time_slots() as $slot) {
            if ($slot['value'] === $slot_value) {
                return true;
            }
        }
        return false;
    }
}
