=== GN Tsiartas Spin to Win ===
Contributors: orionaselite
Donate link: https://www.georgenicolaou.me/
Tags: spin wheel, gamification, loyalty, giveaways
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.4.3
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

= 1.4.3 =
* Removed the "Try Again" outcome from the default prize configuration so every spin pays out a voucher by default.
* Updated plugin version metadata to 1.4.3 for this prize lineup refresh.

= 1.4.2 =
* Updated the in-app campaign date so the wheel and modal highlight the refreshed schedule for the latest giveaway window.
* Swapped the modal and wheel logo assets to the new supermarket branding for the 2025 promotion.
* Raised the default voucher quotas to mirror the latest daily allocation shared by the marketing team.

= 1.4.1 =
* Trimmed the default prize labels so the wheel shows only the euro value for each reward both on the wheel and in the sidebar.
* Updated the translation template and documentation for the 1.4.1 metadata bump.

= 1.4.0 =
* Added a desktop-only overlay that guides shoppers to scan the in-store QR code on their phone before playing.
* Removed the unused prize list wrapper container while preserving the sidebar styling.
* Updated plugin metadata to 1.4.0 for the mobile-exclusive experience release.

= 1.3.9 =
* Smoothed the wheel pointer alignment and button spacing so the indicator clearly highlights the winning slice.
* Updated plugin metadata to 1.3.9 to package the latest wheel tweaks.

= 1.3.8 =
* Repositioned the spin pointer so it sits centred above the button and clearly indicates the winning slice.
* Updated plugin metadata to 1.3.8 for the pointer alignment fix.

= 1.3.7 =
* Tuned the wheel layout so slice offsets, label widths, and typography respond gracefully to any viewport or container size.
* Reworked the slice labels with wedge-shaped overlays that rest on the wheel segments instead of floating above them.
* Updated plugin metadata to 1.3.7 for the responsive styling refresh.

= 1.3.6 =
* Added verbose console logging across the admin and public scripts so every lifecycle step is visible while debugging.
* Updated plugin metadata to 1.3.6 to package the enhanced diagnostics release.

= 1.3.5 =
* Added console logging around the spin flow to help diagnose why the wheel may fail to rotate.
* Bumped the plugin version metadata to 1.3.5 for the new diagnostics release.

= 1.3.4 =
* Removed the prize description copy from the sidebar list so only the main reward labels appear.
* Matched the wheel animation duration with the configured spin setting to guarantee a visible rotation on each play.
* Locked the spin and win audio cues to the bundled `spin.mp3` and `win.mp3` assets for consistent playback.

= 1.3.3 =
* Updated the default wheel prizes to reflect the latest voucher lineup and "Δοκιμάστε Ξανά" outcome, including special odds for the €50 and €100 rewards.

= 1.3.2 =
* Restored the 1.2.0 core, admin, and front-end implementations to remove dynamic spin duration and layout calculations.
* Removed the PHP audio proxy so empty spin/win settings fall back to the bundled MP3 assets directly.

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

= 1.4.2 =
Install this update to refresh the campaign date copy, roll out the new supermarket logo artwork, and load the latest voucher quotas.

= 1.4.1 =
Install this release to present only the euro amounts on each prize and keep translations aligned with the 1.4.1 metadata bump.

= 1.4.0 =
Install this release to guide desktop visitors toward the in-store QR code, streamline the prize list markup, and stay current with the 1.4.0 metadata bump.

= 1.3.7 =
Install this release to get responsive wheel sizing and on-slice labels that follow each segment while staying current with the 1.3.7 metadata bump.

= 1.3.6 =
Install this update to surface detailed console output across the admin and front-end experiences, making it easier to locate spin bugs while staying current with the 1.3.6 release metadata.

= 1.3.5 =
Install this update to capture additional console debugging that explains why the wheel cannot spin and to stay current with the 1.3.5 release metadata.

= 1.3.4 =
Update to keep the wheel animation aligned with the configured spin timing, simplify the prize list layout, and rely on the bundled MP3 cues for spin and win events.

= 1.3.3 =
Install this update to load the refreshed voucher wheel defaults, including the €50 and €100 prize cadence.

= 1.3.2 =
Update to revert to the stable 1.2.0 spin behaviour while keeping direct MP3 defaults for the spin and win audio cues.

= 1.3.1 =
Update to ensure the correct MP3 cues play while spinning and upon winning, and to stay current with the 1.3.1 metadata bump.

= 1.3.0 =
Install this version to ensure the plugin metadata and readme references match the 1.3.0 release.

= 1.2.0 =
Review the refreshed documentation on the `[tsiartas_spin_to_win]` shortcode to quickly embed the promotional wheel.

= 1.1.0 =
Enable this update to receive future releases automatically from GitHub without manual uploads.
