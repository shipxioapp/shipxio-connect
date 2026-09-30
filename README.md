# Shipxio Connect

Shipxio Connect is the official WordPress plugin for connecting a website to Shipxio.

It allows website visitors to view shipping rates, calculate shipping estimates, and search for HS Code classifications using the Shipxio Website Integration API.

## Features

- Display shipping rates
- Calculate shipping estimates
- Search HS Code suggestions
- Calculate estimates using a selected HS Code
- Full, calculator-only, and rates-only shortcodes
- Primary color and border radius appearance settings
- Elementor Shortcode widget compatibility
- Server-side Shipxio authentication

## Requirements

- WordPress 6.3 or later
- PHP 8.1 or later
- A Shipxio account with Website Integration access

## Installation

1. Install and activate Shipxio Connect.
2. Open **Settings → Shipxio Connect**.
3. Copy your **Shipxio base URL** from Shipxio.
4. Copy your **Website Integration credential** from Shipxio.
5. Save the settings.
6. Add one of the shortcodes below to a page.

## Shortcodes

| Shortcode | Displays |
| --- | --- |
| `[shipxio_connect]` | The shipping calculator and the shipping rates |
| `[shipxio_connect_calculator]` | The shipping calculator only |
| `[shipxio_connect_rates]` | The shipping rates only |

All three render through the same client, REST routes, and assets. Several shortcodes can appear on one page.

The plugin renders no heading or branding of its own, so the page keeps its own title and introduction.

## Appearance

**Settings → Shipxio Connect → Appearance** has two settings:

- **Primary color** — a hex color used for the calculate button, focus rings, and highlights. Defaults to `#2A7FFF`.
- **Border radius** — the corner rounding of fields, buttons, cards, and tables, from `0` to `24` pixels. Defaults to `8`.

Both are applied as CSS custom properties scoped to `.shipxio-connect`, so they affect the plugin output only and never the host theme. Custom CSS, custom HTML, and font-family selection are deliberately not supported; the plugin keeps inheriting the host website font.

## Elementor

Shipxio Connect works with Elementor through Elementor's standard **Shortcode** widget.

Add any of the shortcodes to the widget and publish the page.

Elementor is optional.

## Security

The Website Integration credential is used only by the server-side WordPress plugin.

It is not exposed in visitor-facing HTML or JavaScript.

It is also never rendered back into the settings form. Once a credential is saved, the settings screen shows a masked **Credential configured** indicator. Saving with the field blank keeps the stored credential; entering a new one replaces it.

Shipxio remains the source of truth for shipping rates, estimates, HS Code suggestions, and Website Integration access.

## Links

- Website: https://shipxio.com
- Shipxio Connect: https://shipxio.com/shipxio-connect
- Terms of Use: https://shipxio.com/terms-of-use
- Privacy Policy: https://shipxio.com/privacy-policy
- Cookie Policy: https://shipxio.com/cookie-policy

## License

Shipxio Connect is licensed under GPLv2 or later.
