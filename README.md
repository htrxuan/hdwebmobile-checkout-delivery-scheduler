# HDWebmobile Checkout Delivery Scheduler

Let customers pick a delivery date and time slot at checkout. Supports both the classic and block-based Checkout.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-checkout-delivery-scheduler/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

HDWebmobile Checkout Delivery Scheduler adds a "Delivery date" and "Delivery time slot" dropdown to checkout, so shoppers can choose exactly when they want their order delivered. Merchants control which weekdays are never available (e.g. no Sunday delivery), block out specific dates (holidays, stock-take days), and set how many days of lead time are needed before the first available delivery date.

The plugin registers its fields using WooCommerce's own native Checkout Fields API on the block-based Checkout, and a traditional hook-based implementation on the classic Checkout. Both paths save to the same order data, so the chosen date and time slot appear on the admin order screen and in order emails regardless of which Checkout your store uses.

## Features

* Supports both the classic (shortcode) Checkout and the block-based Checkout
* Native field rendering with zero custom JavaScript on the block-based Checkout, using WooCommerce's own Checkout Fields API
* Blocked weekdays (e.g. no Sunday/Monday delivery) and specific blackout dates (holidays, stock-take days)
* Configurable lead time (minimum days ahead) and how many days ahead to offer
* Delivery date and time slot are shown automatically in order emails and on the admin Edit Order screen, using WooCommerce's own order-field display
* Every submitted value is re-validated on the server against the live availability rules, so a tampered or stale value can never be saved
* Works for guests and logged-in shoppers alike

## Development

Standard WordPress plugin structure:

```
hdwebmobile-checkout-delivery-scheduler.php    Bootstrap
includes/class-hddts-activator.php
includes/class-hddts-admin.php
includes/class-hddts-availability.php
includes/class-hddts-checkout-blocks.php
includes/class-hddts-checkout-classic.php
includes/class-hddts-core.php
includes/class-hddts-hub.php
```

Part of the [HDWebmobile](https://hdwebmobile.com/plugins/) suite of focused, single-purpose WooCommerce plugins.

## License

GPLv2 or later. See [LICENSE](LICENSE).

