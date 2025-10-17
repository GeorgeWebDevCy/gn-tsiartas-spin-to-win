<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://www.georgenicolaou.me/
 * @since             1.0.0
 * @package           Gn_Tsiartas_Spin_To_Win
 *
 * @wordpress-plugin
 * Plugin Name:       GN Tsiartas Spin to WIN
 * Plugin URI:        https://https://www.georgenicolaou.me/plugins/gn-tsiartas-spin-to-win
 * Description:       A spin to win plugin built for Tsiartas Supermarket
 * Version:           1.4.10
 * Author:            George Nicolaou
 * Author URI:        https://https://www.georgenicolaou.me//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gn-tsiartas-spin-to-win
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GN_TSIARTAS_SPIN_TO_WIN_VERSION', '1.4.10' );

if ( ! defined( 'GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME' ) ) {
        define( 'GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME', 'gn_tsiartas_spin_to_win_settings' );
}

if ( ! defined( 'GN_TSIARTAS_SPIN_TO_WIN_PLUGIN_FILE' ) ) {
        define( 'GN_TSIARTAS_SPIN_TO_WIN_PLUGIN_FILE', __FILE__ );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gn-tsiartas-spin-to-win-activator.php
 */
function activate_gn_tsiartas_spin_to_win() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gn-tsiartas-spin-to-win-activator.php';
	Gn_Tsiartas_Spin_To_Win_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gn-tsiartas-spin-to-win-deactivator.php
 */
function deactivate_gn_tsiartas_spin_to_win() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gn-tsiartas-spin-to-win-deactivator.php';
	Gn_Tsiartas_Spin_To_Win_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gn_tsiartas_spin_to_win' );
register_deactivation_hook( __FILE__, 'deactivate_gn_tsiartas_spin_to_win' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gn-tsiartas-spin-to-win.php';

/**
 * Register automatic updates for the plugin using the GitHub repository.
 *
 * @see https://github.com/YahnisElsts/plugin-update-checker
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/plugin-update-checker.php';

// Provide backwards compatibility for legacy PUC factory class names.
if ( ! class_exists( 'Puc_v5_Factory' ) && class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
	class Puc_v5_Factory extends \YahnisElsts\PluginUpdateChecker\v5\PucFactory {}
}

$gn_tsiartas_spin_to_win_updater = Puc_v5_Factory::buildUpdateChecker(
	'https://github.com/GeorgeWebDevCy/gn-tsiartas-spin-to-win/',
	__FILE__,
	'gn-tsiartas-spin-to-win'
);

$gn_tsiartas_spin_to_win_updater->setBranch( 'main' );

$gn_tsiartas_spin_to_win_vcs_api = $gn_tsiartas_spin_to_win_updater->getVcsApi();

if ( $gn_tsiartas_spin_to_win_vcs_api && method_exists( $gn_tsiartas_spin_to_win_vcs_api, 'enableReleaseAssets' ) ) {
	$gn_tsiartas_spin_to_win_vcs_api->enableReleaseAssets();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gn_tsiartas_spin_to_win() {

	$plugin = new Gn_Tsiartas_Spin_To_Win();
	$plugin->run();

}
run_gn_tsiartas_spin_to_win();
