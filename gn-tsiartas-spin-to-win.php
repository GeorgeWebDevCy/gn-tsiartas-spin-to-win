<?php
/**
 * Plugin Name: GN Tsiartas Spin To Win
 * Description: Lucky spin wheel game with vouchers and try-again slices.
 * Version: 2.3.0
 */

/**
 * Abort execution if this file is loaded directly. WordPress defines the
 * ABSPATH constant when the environment is bootstrapped, so the check keeps
 * curious visitors from triggering the plugin PHP via direct URLs.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expose the current plugin version so it can be reused when enqueueing
 * assets, generating cache-busting strings, or populating admin notices.
 * The constant is defined only if it has not been set previously to avoid
 * clobbering values during testing or when the plugin is bundled in another
 * project.
 */
if ( ! defined( 'GN_TSIARTAS_SPIN_TO_WIN_VERSION' ) ) {
	define( 'GN_TSIARTAS_SPIN_TO_WIN_VERSION', '2.3.0' );
}

/**
 * Store the absolute path to this file. Several classes reference the plugin
 * file path when registering hooks (for example, to inject a Settings link on
 * the Plugins screen), so we expose it via a constant for easy reuse.
 */
if ( ! defined( 'GN_TSIARTAS_SPIN_TO_WIN_PLUGIN_FILE' ) ) {
	define( 'GN_TSIARTAS_SPIN_TO_WIN_PLUGIN_FILE', __FILE__ );
}

/**
 * Reuse the same option name across the admin and public layers. This keeps
 * the settings API, shortcode renderer, and AJAX handlers aligned on where the
 * configuration is stored without scattering magic strings throughout the
 * codebase.
 */
if ( ! defined( 'GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME' ) ) {
	define( 'GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME', 'gn_tsiartas_spin_to_win_settings' );
}

/**
 * Execute logic required during plugin activation.
 *
 * The activator is responsible for preparing default settings, creating
 * database structures, or any other one-time operations. Even though the
 * bundled activator is currently empty, we still include the hook to preserve
 * backwards compatibility and to make future migrations straightforward.
 */
function activate_gn_tsiartas_spin_to_win() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gn-tsiartas-spin-to-win-activator.php';
	Gn_Tsiartas_Spin_To_Win_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_gn_tsiartas_spin_to_win' );

/**
 * Execute shutdown logic during plugin deactivation.
 *
 * The deactivator is the symmetrical counterpart to the activator. It offers
 * a convenient location for tearing down scheduled events or cached data when
 * the plugin is disabled from the WordPress admin.
 */
function deactivate_gn_tsiartas_spin_to_win() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gn-tsiartas-spin-to-win-deactivator.php';
	Gn_Tsiartas_Spin_To_Win_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_gn_tsiartas_spin_to_win' );

/**
 * Load the Plugin Update Checker library so self-hosted releases continue to
 * function. The library inspects the configured GitHub repository and informs
 * WordPress when a new tag or branch update is available.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/load-v5p6.php';

/**
 * Register the update checker with the GitHub repository that stores the
 * production plugin builds. We use the fully qualified class name to avoid
 * namespace collisions if another plugin bundles a different version of the
 * library.
 */
// Hold a reference to the update checker so hooks remain active for the lifetime of the request.
$gn_tsiartas_spin_to_win_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/GeorgeWebDevCy/gn-tsiartas-spin-to-win/',
	__FILE__,
	'gn-tsiartas-spin-to-win'
);
$gn_tsiartas_spin_to_win_update_checker->setBranch( 'main' );

/**
 * Pull in the core plugin class. This class wires together the admin and
 * public layers, registers hooks, and exposes the run() method used to kick
 * everything off after the bootstrap finishes executing.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-gn-tsiartas-spin-to-win.php';

/**
 * Instantiate and execute the core plugin.
 *
 * Splitting the instantiation into a dedicated function keeps the global
 * namespace tidy while still allowing other plugins (or unit tests) to tap
 * into the process if they need to replace the implementation at runtime.
 */
function run_gn_tsiartas_spin_to_win() {
	$plugin = new Gn_Tsiartas_Spin_To_Win();
	$plugin->run();
}
run_gn_tsiartas_spin_to_win();
