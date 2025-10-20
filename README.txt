=== GN Tsiartas Spin to Win ===
Contributors: orionaselite
Donate link: https://www.georgenicolaou.me/
Tags: spin wheel, gamification, loyalty, giveaways
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.3.16
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

\= 2.3.16 =
* Hard-code the wheel logo to load directly from tsiartassupermarket.com to ensure the latest branding is always displayed.
* Bump the plugin metadata and documentation references to version 2.3.16 for release packaging.

\= 2.3.15 =
* Copy the branded wheel logo into the WordPress uploads directory on demand so it always loads from `wp-content` for visitors.
* Bump the plugin metadata and documentation references to version 2.3.15 for release packaging.

\= 2.3.14 =
* Mirror the public-facing image assets into the WordPress uploads directory so the wheel logo remains visible for logged-out visitors.
* Update the plugin version metadata to 2.3.14 for this asset distribution change.

\= 2.3.13 =
* Confirm compatibility with current WordPress maintenance releases while keeping the proven 2.3.10 wheel configuration intact.
* Bump the plugin header, version constant, and documentation references to 2.3.13 for release packaging.

= 2.3.12 =
* Revert the celebration overlay and styling changes shipped in 2.3.11 so the experience matches the proven 2.3.10 baseline.
* Bump the plugin header, version constant, and documentation references to 2.3.12 for release packaging.

= 2.3.10 =
* Realign the wheel pointer so it remains centred above the winning slice after each spin.
* Bump the plugin header, version constant, and documentation references to 2.3.10 for release packaging.

= 2.3.9 =
* Add console logging that reports the resolved prize, slice index, and computed rotation for easier browser debugging.
* Bump the plugin header, version constant, and documentation references to 2.3.9 for release packaging.

= 2.3.8 =
* Bump the plugin header, version constant, and documentation references to 2.3.8 for release packaging.
* Confirm the GitHub-powered updater continues targeting the `main` branch for distributing the 2.3.8 build.

= 2.3.4 =
* Align the wheel header and Friday schedule logic with the WordPress timezone setting so shoppers see the correct local time.
* Bump plugin metadata and documentation references to version 2.3.4 for release.

= 2.3.3 =
* Revert the plugin to the proven 2.2.5 codebase so the wheel and configuration behave exactly as the earlier release.
* Bump plugin metadata and readme documentation to version 2.3.3 for distribution.

= 2.2.5 =
* Refreshed the wheel header date/time so the public experience always reflects the current store schedule.
* Added a direct **Settings** link in the Plugins list for quicker access to spin-to-win configuration.
* Tweaked wheel colours to better align with the latest Tsiartas branding accents.

= 2.2.4 =
* Fix Friday voucher quota persistence so guaranteed spins stay accurate after restarts.

= 2.2.3 =
* Bump plugin metadata and documentation to version 2.2.3.

= 2.2.2 =
* Bring the Tsiartas logo into the wheel hub so the branding remains visible as slices update.

= 2.2.1 =
* Display the current store date above the wheel and inside the modal so shoppers know which day's promotion they are joining.
* Localise the formatted date through the shortcode payload and AJAX responses so each instance can refresh the timestamp automatically.
* Polished the wheel header and modal footer styling to highlight the date without overpowering the existing branding.

= 2.2.0 =
* Added Friday voucher quota controls with guaranteed €50 and €100 allocations and exposed the configuration to the public script.
* Introduced server-side spin assignment with weekly tracking, quota pacing across the 07:00–20:00 window, and AJAX-powered prize responses.
* Documented manual QA steps that cover pacing behaviour, quota exhaustion messaging, and the forced €50/€100 spins.

= 2.1.2 =
* Removed the sidebar heading and prize list markup so the wheel interface focuses solely on the spin interaction.
* Bumped plugin metadata and documentation to version 2.1.2.

= 2.1.1 =
* Updated the plugin metadata and translation template for the 2.1.1 maintenance release.

= 2.1 =
* Removed the verbose admin and public console logging and updated the plugin metadata to version 2.1.

= 2.0 =
* Reverted the plugin to the proven 1.4.1 codebase while preparing the next major release cycle under the 2.0 version number.

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

= 2.1 =
Update to remove the diagnostic console logging from the admin and public scripts while receiving the 2.1 metadata bump.

= 2.0 =
Install this major release to stay aligned with the stable 1.4.1 codebase while receiving the refreshed 2.0 metadata.

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

== Manual QA ==

The following checklist covers the pacing, quota, and forced-spin safeguards added in version 2.2.0:

1. **Reset state and configure quotas**
   - Run `wp option delete gn_tsiartas_spin_to_win_friday_tracking` to clear previous logs.
   - In **Spin & Win → Settings**, set Friday quotas (for example, €5=3, €10=3, €50=1, €100=1) and save.
2. **Validate pacing and quota exhaustion**
   - Load the public wheel on a Friday between 07:00 and 20:00.
   - Use the browser console to trigger spins with `fetch( gnTsiartasSpinToWinConfig.settings.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: new URLSearchParams( { action: 'gn_tsiartas_spin_to_win_spin', nonce: gnTsiartasSpinToWinConfig.settings.nonce } ) } )`.
   - Confirm the JSON response reports decreasing `remainingQuotas` for €5/€10 and, once they reach zero, includes `depleted: true` while the UI disables the spin button.
3. **Verify guaranteed €50/€100 spins**
   - After generating tracking data, run `wp option patch update gn_tsiartas_spin_to_win_friday_tracking total_spins 49` and `wp option patch update gn_tsiartas_spin_to_win_friday_tracking totals.50 0`.
   - Trigger another spin and confirm the response shows `awardedDenomination: "50"`.
   - Repeat by setting `total_spins` to `99` and `totals.100` to `0`; the next spin must report the €100 voucher.
4. **Regression: mismatched prize identifiers**
   - In the browser console, run:

     ```js
     const instance = window.gnTsiartasSpinToWin.instances[0];
     const original = instance.processSpinSuccess;
     instance.processSpinSuccess = function(payload) {
         var mutated = window.jQuery.extend(true, {}, payload);
         mutated.prizeId = 'fake-id-' + Date.now();
         return original.call(instance, mutated);
     };
     ```

   - Click the spin button once and confirm the wheel lands on the same prize described in the modal (no fallback to an incorrect wedge or generic error message).
   - Restore the original handler with `instance.processSpinSuccess = original;` after completing the check.
