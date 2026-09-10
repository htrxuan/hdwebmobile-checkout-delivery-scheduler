=== HDWebmobile Checkout Delivery Scheduler ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, delivery date, time slot, checkout, delivery scheduling
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers pick a delivery date and time slot at checkout. Supports both the classic and block-based Checkout.

== Description ==

HDWebmobile Checkout Delivery Scheduler adds a "Delivery date" and "Delivery time slot" dropdown to checkout, so shoppers can choose exactly when they want their order delivered. Merchants control which weekdays are never available (e.g. no Sunday delivery), block out specific dates (holidays, stock-take days), and set how many days of lead time are needed before the first available delivery date.

The plugin registers its fields using WooCommerce's own native Checkout Fields API on the block-based Checkout, and a traditional hook-based implementation on the classic Checkout. Both paths save to the same order data, so the chosen date and time slot appear on the admin order screen and in order emails regardless of which Checkout your store uses.

= Key Features =
* Supports both the classic (shortcode) Checkout and the block-based Checkout
* Native field rendering with zero custom JavaScript on the block-based Checkout, using WooCommerce's own Checkout Fields API
* Blocked weekdays (e.g. no Sunday/Monday delivery) and specific blackout dates (holidays, stock-take days)
* Configurable lead time (minimum days ahead) and how many days ahead to offer
* Delivery date and time slot are shown automatically in order emails and on the admin Edit Order screen, using WooCommerce's own order-field display
* Every submitted value is re-validated on the server against the live availability rules, so a tampered or stale value can never be saved
* Works for guests and logged-in shoppers alike

= Limitations (please read before installing) =
* No per-slot capacity limits — every time slot is offered on every valid date regardless of how many orders have already picked it; there's no "this slot is full" cutoff in this version
* Time slots are a fixed global list, not date-specific — the same slots are offered for every valid date; slots that vary by day of week aren't supported

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-checkout-delivery-scheduler` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Checkout Delivery Scheduler** to configure lead time, blocked weekdays, blackout dates, and time slots.

== How to Use ==

= 1. Configure availability =
Go to **WooCommerce > HDWebmobile > Checkout Delivery Scheduler** (Screenshot 1). Set how many days of lead time are needed, how many days ahead to offer, which weekdays are never available, and list any blackout dates (one per line). Add your time slots as plain text, one per line (e.g. "9:00 AM - 12:00 PM").

= 2. Customers choose at checkout =
On checkout, shoppers see "Delivery date" and "Delivery time slot" dropdowns (Screenshot 2), populated only with dates that respect your blocked weekdays and blackout dates. Both fields are required.

= 3. View the chosen delivery info =
The selected date and time slot appear automatically on the order confirmation page, in order emails, and on the admin Edit Order screen (Screenshot 3), since they're saved as standard WooCommerce order fields.

== Screenshots ==

1. The settings page: lead time, blocked weekdays, blackout dates, and time slots.
2. The Delivery date and Delivery time slot dropdowns on checkout.
3. The chosen delivery date and time slot shown on the admin Edit Order screen.

== Changelog ==

= 1.0.1 =
* Renamed the plugin to "HDWebmobile Checkout Delivery Scheduler" (new slug: hdwebmobile-checkout-delivery-scheduler).
* Rewrote the description and corrected the settings-page location (WooCommerce > HDWebmobile > Checkout Delivery Scheduler).

= 1.0.0 =
* Initial release: blocked weekdays, blackout dates, configurable lead time, support for both classic and block-based Checkout, server-side validation of every submission.
