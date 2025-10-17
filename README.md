# GN Tsiartas Spin to Win

GN Tsiartas Spin to Win delivers an interactive promotional wheel for Tsiartas Supermarket. The plugin bundles the
front-end assets and WordPress hooks required to display the experience on any page while keeping the admin area clean.

## What's included
- Responsive, branded spin-to-win experience out of the box.
- Admin and public asset loading isolated via the plugin loader.
- Translation-ready strings through the included i18n class.
- Automatic GitHub-powered updates starting from version 1.1.0.

## Automatic updates
The plugin ships with the [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) library.
WordPress will detect new releases from https://github.com/GeorgeWebDevCy/gn-tsiartas-spin-to-win/ whenever you publish
an updated tag or push to the `main` branch. Release assets are supported, so you can attach packaged ZIP files to
GitHub releases for stable distributions.

## Development
1. Clone this repository into `wp-content/plugins/`.
2. Run `composer install` only if you add additional dependencies; the plugin-update-checker library is already included.
3. Activate **GN Tsiartas Spin to Win** from the WordPress admin.
4. Adjust assets in the `public/` and `admin/` directories to match current campaign requirements.

## Release notes

### 2.2.5
- Refreshed the wheel header date and time display so the experience always communicates the current store schedule.
- Added a Plugins screen **Settings** shortcut that opens the configuration page directly.
- Softened and adjusted the wheel colour palette to match the latest Tsiartas branding accents.

### 2.2.4
- Fixed Friday voucher quota persistence so guaranteed spins remain accurate after restarts.

### 2.2.3
- Bumped the plugin metadata and documentation to version 2.2.3.

### 2.2.2
- Embedded the Tsiartas logo within the wheel hub so branding stays visible across wheel redraws.

### 2.2.1
- Surfaced the current store date above the wheel header and inside the modal footer so players know which day's draw is active.
- Localised the formatted date through the shortcode payload and AJAX responses for accurate, per-instance updates.
- Tweaked the wheel header and modal footer styling to present the date clearly without distracting from the wheel.

### 2.2.0
- Added Friday voucher quota controls (with guaranteed €50/€100 allocations) and surfaced the configuration alongside the public settings payload.
- Replaced front-end prize selection with a server-side AJAX endpoint that paces vouchers across the 07:00–20:00 Friday window and records weekly spin history.
- Documented manual QA steps that verify pacing behaviour, quota exhaustion handling, and the forced €50/€100 spin overrides.

### 2.1.2
- Removed the sidebar prize heading and list markup so the public experience highlights the interactive wheel.
- Updated plugin metadata and documentation to version 2.1.2.

### 2.1.1
- Bumped the plugin metadata and translation template to package the 2.1.1 maintenance update.

### 2.1
- Removed the verbose admin and front-end console logging while bumping the plugin metadata to version 2.1.

### 2.0
- Reissued the stable 1.4.1 codebase under the 2.0 version banner to prepare for the next major update cycle.

### 1.4.1
- Trimmed the default prize labels so the wheel only displays the euro value for each reward.
- Synced the translation template and plugin metadata with the 1.4.1 release.

### 1.4.0
- Introduced a desktop-only overlay that explains how to access the promotion by scanning the in-store QR code on mobile.
- Simplified the prize list markup by removing the unused wrapper container while retaining styling.
- Updated plugin metadata to version 1.4.0 for this mobile-experience release.

### 1.3.9
- Refined the wheel pointer positioning and surrounding button spacing so the winning slice is emphasised more clearly.
- Updated plugin metadata to version 1.3.9 to document these wheel tweaks.

### 1.3.8
- Centered the spin pointer directly above the button so the wheel indicator lines up with the winning segment.
- Bumped plugin metadata to version 1.3.8 for this visual refinement.

### 1.3.7
- Made the wheel layout responsive by dynamically sizing slice offsets and typography as the container changes.
- Restyled prize labels so they sit directly on each slice with wedge-shaped backgrounds that follow the spin.
- Bumped plugin metadata to version 1.3.7 for this responsive styling release.

### 1.3.6
- Expanded admin and public JavaScript logging to trace configuration changes and front-end lifecycle events for debugging.
- Bumped plugin metadata to version 1.3.6 for this diagnostics update.

### 1.3.5
- Added detailed console logging to the spin workflow so debugging stuck wheels is easier.
- Updated plugin metadata to version 1.3.5 for this diagnostic release.

### 1.3.4
- Simplified the sidebar prize list by removing description rows so only the headline rewards display.
- Synced the wheel animation duration with the configured spin timing so each play visibly rotates the wheel.
- Locked the spin and win cues to the bundled `spin.mp3` and `win.mp3` audio files for consistent playback.

### 1.3.3
- Refreshed the default wheel prizes to include the updated voucher spread and "Δοκιμάστε Ξανά" segment, keeping special odds for €50 and €100 rewards.

### 1.3.2
- Rolled the core, admin, and front-end code back to the 1.2.0 implementation so spin duration and layout helpers return to their original behaviour.
- Removed the PHP-based audio proxy and default empty spin/win settings to the bundled MP3 files for direct playback.

### 1.3.1
- Default the spinning audio cue to `spin.mp3` and the winning celebration cue to `win.mp3` so each moment plays the correct sound automatically.
- Bumped the plugin metadata to version 1.3.1 to capture the updated audio defaults.

### 1.3.0
- Refreshed the plugin header metadata and documentation to match the 1.3.0 distribution.
- Clarified release packaging guidance so GitHub-tagged builds stay in sync with WordPress.org expectations.

### 1.2.0
- Documented the `[tsiartas_spin_to_win]` shortcode for embedding the promotional wheel markup and localized messages.
- Highlighted the bundled front-end assets that render the interactive spin-to-win wheel experience on the page.

### Manual QA (2.2.0)
Follow these steps to validate the new pacing, quota, and guaranteed-spin logic without adding automated tests:

1. **Reset state and configure quotas**
   - Run `wp option delete gn_tsiartas_spin_to_win_friday_tracking`.
   - In the admin settings, set Friday quotas (for example, €5=3, €10=3, €15=3, €50=1, €100=1).
2. **Confirm pacing and quota exhaustion**
   - Load the public wheel on Friday between 07:00 and 20:00.
   - Trigger spins from the browser console using `fetch( gnTsiartasSpinToWinConfig.settings.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: new URLSearchParams( { action: 'gn_tsiartas_spin_to_win_spin', nonce: gnTsiartasSpinToWinConfig.settings.nonce } ) } )`.
   - Observe the `remainingQuotas` values in each JSON response and ensure that, once a voucher reaches zero, the response includes `depleted: true` and the UI disables further spins.
3. **Validate the guaranteed €50/€100 spins**
   - After creating tracking data, run `wp option patch update gn_tsiartas_spin_to_win_friday_tracking total_spins 49` and `wp option patch update gn_tsiartas_spin_to_win_friday_tracking totals.50 0`.
   - Trigger one more spin and verify the response reports `awardedDenomination: "50"`.
   - Repeat with `total_spins` set to `99` and `totals.100` set to `0` to confirm the €100 voucher fires on the hundredth spin.

## License
Released under the GPLv2 or later. See `LICENSE.txt` for details.
