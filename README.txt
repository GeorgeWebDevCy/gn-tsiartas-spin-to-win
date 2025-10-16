=== GN Tsiartas Spin to Win ===
Contributors: orionaselite
Donate link: https://www.georgenicolaou.me/
Tags: spin wheel, gamification, loyalty, giveaways
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a branded spin-to-win promotion for Tsiartas Supermarket visitors and reward customer engagement.

== Description ==

GN Tsiartas Spin to Win delivers a customised promotional wheel for Tsiartas Supermarket. It provides an engaging
chance-to-win mechanic that encourages repeat visits and supports marketing campaigns.

The plugin bundles the full front-end experience and the necessary assets so it can be activated and used immediately.
Administrators can enqueue the branded styles and scripts on the relevant landing pages via the included public hooks.

**Highlights**

* Responsive spin wheel tailored for Tsiartas Supermarket branding.
* Localisation hooks for multi-language deployments.
* Asset enqueuing restricted to the front-end to keep the admin clean.
* Automatic updates directly from the public GitHub repository starting with version 1.1.0.

== Installation ==

1. Upload the entire `gn-tsiartas-spin-to-win` folder to the `/wp-content/plugins/` directory or install via the WordPress admin.
1. Activate the plugin through the **Plugins** screen in WordPress.
1. Use the provided shortcodes, blocks, or template hooks to display the spin-to-win experience wherever it is needed.

== Frequently Asked Questions ==

= How do automatic updates work? =

From version 1.1.0, the plugin uses the Plugin Update Checker library to detect releases from
https://github.com/GeorgeWebDevCy/gn-tsiartas-spin-to-win/. Publish a tagged release (or update the `main` branch) and
WordPress will offer the update automatically.

= Do I need GitHub authentication? =

No. Public access is sufficient. If you later make the repository private, you can configure authentication hooks
provided by the Plugin Update Checker library.

== Screenshots ==

1. Spin to Win wheel in action on the front-end.
2. Custom styling applied to the modal interface.

== Changelog ==

= 1.3.1 =
* Defaulted the spinning and winning audio cues to the bundled `spin.mp3` and `win.mp3` files so each state plays the correct sound.
* Updated plugin version metadata to 1.3.1 across the codebase and documentation.

= 1.3.0 =
* Synchronized plugin headers, documentation, and distribution notes for the 1.3.0 release package.

= 1.2.0 =
* Documented the `[tsiartas_spin_to_win]` shortcode flow and the interactive wheel experience for this release.

= 1.1.0 =
* Added automatic update support through the Plugin Update Checker library.
* Bundled the library inside the plugin for easier distribution.

= 1.0.0 =
* Initial release of the GN Tsiartas Spin to Win experience.

== Upgrade Notice ==

= 1.3.1 =
Update to ensure the correct MP3 cues play while spinning and upon winning, and to stay current with the 1.3.1 metadata bump.

= 1.3.0 =
Install this version to ensure the plugin metadata and readme references match the 1.3.0 release.

= 1.2.0 =
Review the refreshed documentation on the `[tsiartas_spin_to_win]` shortcode to quickly embed the promotional wheel.

= 1.1.0 =
Enable this update to receive future releases automatically from GitHub without manual uploads.
