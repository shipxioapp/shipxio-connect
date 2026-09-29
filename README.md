# Shipxio Connect

Shipxio Connect is the official WordPress plugin for connecting a website to Shipxio.

It allows website visitors to view shipping rates, calculate shipping estimates, and search for HS Code classifications using the Shipxio Website Integration API.

## Features

- Display shipping rates
- Calculate shipping estimates
- Search HS Code suggestions
- Calculate estimates using a selected HS Code
- WordPress shortcode support
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
6. Add the following shortcode to a page:

```text
[shipxio_connect]
```

## Elementor

Shipxio Connect works with Elementor through Elementor's standard **Shortcode** widget.

Add:

```text
[shipxio_connect]
```

to the widget and publish the page.

Elementor is optional.

## Security

The Website Integration credential is used only by the server-side WordPress plugin.

It is not exposed in visitor-facing HTML or JavaScript.

Shipxio remains the source of truth for shipping rates, estimates, HS Code suggestions, and Website Integration access.

## Links

- Website: https://shipxio.com
- Shipxio Connect: https://shipxio.com/shipxio-connect
- Terms of Use: https://shipxio.com/terms-of-use
- Privacy Policy: https://shipxio.com/privacy-policy
- Cookie Policy: https://shipxio.com/cookie-policy

## License

Shipxio Connect is licensed under GPLv2 or later.
