=== Shipxio Connect ===
Contributors: shipxio
Tags: shipping, shipping rates, shipping calculator, logistics
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your WordPress website to Shipxio to display shipping rates and provide shipping estimates to your visitors.

== Description ==

Shipxio Connect connects a WordPress website to a Company's Shipxio Website Integration.

The plugin lets website visitors:

* View the Company's current shipping rates.
* Calculate shipping estimates.
* Search for item classifications using HS Code suggestions.
* Calculate estimates using a selected item classification.

Shipxio remains the source of truth for shipping rates, calculations, item classifications, Company access, and Website Integration capabilities.

A Shipxio account with Website Integration access is required.

The plugin uses a server-side connection to Shipxio. Your Website Integration credential is stored in WordPress and is not exposed to visitor-facing browser JavaScript.

Shipxio Connect can be added to a page with:

[shipxio_connect]

The shortcode works with normal WordPress content and can also be placed inside Elementor's standard Shortcode widget. Elementor is not required.

== Installation ==

1. Install and activate Shipxio Connect.
2. In WordPress, open Settings > Shipxio Connect.
3. In Shipxio, open your Website Integration settings.
4. Copy the Shipxio base URL and paste it into the matching WordPress field.
5. Copy the Website Integration credential and paste it into the matching WordPress field.
6. Save the settings.
7. Add `[shipxio_connect]` to the WordPress page where you want the shipping calculator and rates to appear.

The Shipxio base URL is supplied directly by Shipxio. You do not need to add or edit API endpoint paths.

== External Service ==

Shipxio Connect relies on the Shipxio service to provide its shipping functionality.

The plugin sends server-side requests from the WordPress website to Shipxio when it needs to:

* Verify the Website Integration connection.
* Retrieve the connected Company's shipping rates.
* Request a shipping estimate.
* Retrieve HS Code suggestions for the shipping calculator.

Requests are authenticated using the Website Integration credential configured by the WordPress administrator.

The credential remains on the WordPress server and is not sent to visitors' browsers.

Information entered into the public shipping calculator, including package weight, declared item value, search terms, and a selected HS Code when applicable, may be sent to Shipxio so that the requested shipping information can be returned.

Shipxio Connect does not calculate shipping prices, customs charges, or classifications independently. Those results are provided by Shipxio.

Service information:
https://shipxio.com/

Terms of Use:
https://shipxio.com/terms-of-use

Privacy Policy:
https://shipxio.com/privacy-policy

Cookie Policy:
https://shipxio.com/cookie-policy

== Frequently Asked Questions ==

= Do I need a Shipxio account? =

Yes. Shipxio Connect requires a Shipxio Website Integration with the appropriate capabilities enabled.

= Where do I get the Shipxio base URL? =

Copy the Shipxio base URL directly from your Website Integration settings in Shipxio.

= Where do I get the Website Integration credential? =

Shipxio displays the credential when a Website Integration is created or its credential is rotated. Copy it when it is shown and save it in the Shipxio Connect settings.

= Is the Website Integration credential exposed to website visitors? =

No. The credential is used by the plugin's server-side PHP client and is not included in the public shortcode HTML or visitor-facing JavaScript.

= Does Shipxio Connect work with Elementor? =

Yes. Add `[shipxio_connect]` to Elementor's standard Shortcode widget.

Elementor is optional. The shortcode also works in normal WordPress content.

= Does Shipxio Connect calculate shipping prices itself? =

No. Shipxio remains authoritative for shipping rates, estimates, HS Code suggestions, and related calculations. The plugin displays the results returned by Shipxio.

= Does the plugin use my website's font? =

Yes. Shipxio Connect inherits the host website's font family while retaining its own layout, sizing, spacing, and component styling.

== Changelog ==

= 1.0.1 =
* Initial WordPress.org release.
* Added Shipxio Website Integration configuration.
* Added shipping rates.
* Added the shipping calculator.
* Added HS Code suggestions and selected classification estimates.
* Added shortcode and Elementor Shortcode widget compatibility.