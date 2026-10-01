=== Shipxio Connect ===
Contributors: shipxio
Tags: shipping, shipping rates, shipping calculator, logistics
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.10
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

Three shortcodes are available:

* `[shipxio_connect]` displays the shipping calculator and the shipping rates.
* `[shipxio_connect_calculator]` displays the shipping calculator only.
* `[shipxio_connect_rates]` displays the shipping rates only.

Each shortcode shows a built-in title and short description above its section. Add `show_intro="false"` to hide that title and description and show only the calculator or rate table:

* `[shipxio_connect show_intro="false"]`
* `[shipxio_connect_calculator show_intro="false"]`
* `[shipxio_connect_rates show_intro="false"]`

Leaving the option out keeps the built-in title and description, so nothing changes on pages you have already published.

The shortcodes work with normal WordPress content and can also be placed inside Elementor's standard Shortcode widget. Elementor is not required.

Shipxio Connect does not add a heading or branding of its own, so your page keeps its own title and introduction.

The settings screen also offers two appearance settings, a primary color and a border radius, so the calculator and rates can match your website. They apply to the plugin output only and never change the rest of your website.

== Installation ==

1. Install and activate Shipxio Connect.
2. In WordPress, open Settings > Shipxio Connect.
3. In Shipxio, open your Website Integration settings.
4. Copy the Shipxio base URL and paste it into the matching WordPress field.
5. Copy the Website Integration credential and paste it into the matching WordPress field.
6. Save the settings.
7. Add `[shipxio_connect]` to the WordPress page where you want the shipping calculator and rates to appear.

To display only one part of Shipxio Connect, use `[shipxio_connect_calculator]` or `[shipxio_connect_rates]` instead.

To supply your own heading instead of the built-in one, add `show_intro="false"` to any of the shortcodes.

Optionally, set the primary color and border radius under Appearance on the same settings screen.

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

Once it is saved, the settings screen shows a masked "Credential configured" indicator instead of the credential itself. The saved credential is never displayed again. Leave the field blank when saving to keep it, or paste a new credential to replace it.

= Is the Website Integration credential exposed to website visitors? =

No. The credential is used by the plugin's server-side PHP client and is not included in the public shortcode HTML or visitor-facing JavaScript.

= Can I show only the calculator or only the rates? =

Yes. Use `[shipxio_connect_calculator]` for the shipping calculator on its own, or `[shipxio_connect_rates]` for the shipping rates on its own. `[shipxio_connect]` shows both.

= Can I hide the built-in section titles? =

Yes. Add `show_intro="false"` to any of the shortcodes:

`[shipxio_connect show_intro="false"]`

`[shipxio_connect_calculator show_intro="false"]`

`[shipxio_connect_rates show_intro="false"]`

That hides the heading and the short description above the section, which is useful when your page already has its own heading. The supported values are `true` and `false`. Omitting the option, or using `show_intro="true"`, keeps the built-in title and description.

= Does Shipxio Connect work with Elementor? =

Yes. Add any of the shortcodes to Elementor's standard Shortcode widget.

Elementor is optional. The shortcodes also work in normal WordPress content.

= Does Shipxio Connect calculate shipping prices itself? =

No. Shipxio remains authoritative for shipping rates, estimates, HS Code suggestions, and related calculations. The plugin displays the results returned by Shipxio.

= Can I change the plugin's colors? =

Yes. Settings > Shipxio Connect has four appearance settings: a primary color, a border radius, a button padding, and a button text color. The primary color is used for the calculate button, focus rings, and highlights. The border radius sets the corner rounding for fields, buttons, cards, and tables, from 0 to 24 pixels. The button padding sets how much space sits inside the calculate button, from 6 to 24 pixels. The button text color sets the label on that button.

These settings apply only to the Shipxio Connect output. They do not change your theme, and the plugin does not accept custom CSS or HTML.

= Does the plugin use my website's font? =

Yes. Shipxio Connect inherits the host website's font family while retaining its own layout, sizing, spacing, and component styling.

== Changelog ==

= 1.0.10 =
* Maintenance and improvements.

= 1.0.9 =
* Larger, more comfortable calculate button.
* Added a button padding setting under Appearance.
* Added a button text color setting under Appearance.
* Refined the calculator fields, including clearer focus, placeholder, and disabled states.
* Tightened the shipping rates table so it is more compact and easier to scan.

= 1.0.8 =
* Added the Shipxio Connect icon to the WordPress updates and plugin details screens, replacing the generic plugin placeholder.

= 1.0.7 =
* Documented the show_intro shortcode option on the Shipxio Connect settings screen.

= 1.0.6 =
* Fixed update checks so newly published Shipxio Connect releases can be detected immediately.

= 1.0.5 =
* Added a `show_intro` shortcode option for showing or hiding the built-in section titles and descriptions.

= 1.0.4 =
* Added independent Shipxio Connect update support using GitHub Releases.
* Added automated release packaging and update metadata publishing.

= 1.0.3 =
* Refined the shortcode layout to use the full available width without extra container padding.
* Refined the admin settings interface.

= 1.0.2 =
* First WordPress.org release.
* Added the `[shipxio_connect_calculator]` and `[shipxio_connect_rates]` shortcodes alongside `[shipxio_connect]`, so a page can show the shipping calculator and the shipping rates separately.
* Added primary color and border radius appearance settings, applied to the plugin output only.
* Added a masked "Credential configured" indicator to the settings screen, so a saved Website Integration credential is visible as configured without ever being displayed.
* Removed the plugin's built-in heading and tagline, so the WordPress page keeps its own title and branding.

= 1.0.1 =
* Initial plugin build. Not published on WordPress.org.
* Added Shipxio Website Integration configuration.
* Added shipping rates.
* Added the shipping calculator.
* Added HS Code suggestions and selected classification estimates.
* Added the `[shipxio_connect]` shortcode and Elementor Shortcode widget compatibility.
