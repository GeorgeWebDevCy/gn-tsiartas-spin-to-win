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

## License
Released under the GPLv2 or later. See `LICENSE.txt` for details.
