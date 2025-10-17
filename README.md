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

## Manual QA checklist
Follow the steps below when validating campaign changes that touch the voucher logic.

1. **Reset weekly tracking** – delete the `gn_tsiartas_spin_to_win_tracking` option (via WP-CLI or the database) so the new week starts with zero recorded spins.
2. **Configure quotas** – in the admin settings screen set explicit Friday counts (for example €5 → 6, €10 → 4, €15 → 2, €50/€100 → 1) and confirm they save without validation errors.
3. **Pacing guardrails** – during the first hour of availability trigger several spins and confirm the AJAX response limits low-value voucher usage (the `quota_usage` payload should only increment up to the allowable share for the elapsed window while returning “try again” outcomes afterwards).
4. **Forced jackpot spins** – prime the tracker to spin 50 and 100 by updating the option so `spin_count` is one less than the target, then run the next spin and confirm the server awards the €50 or €100 voucher and marks the `special_spin` flag accordingly.
5. **Quota exhaustion fallback** – continue spinning until the response shows zero `remaining` vouchers for every tier and verify the subsequent request returns an error message telling the shopper all vouchers have been claimed for the day.

## License
Released under the GPLv2 or later. See `LICENSE.txt` for details.
