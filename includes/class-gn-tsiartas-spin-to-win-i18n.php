<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://www.georgenicolaou.me/
 * @since      1.0.0
 *
 * @package    Gn_Tsiartas_Spin_To_Win
 * @subpackage Gn_Tsiartas_Spin_To_Win/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Gn_Tsiartas_Spin_To_Win
 * @subpackage Gn_Tsiartas_Spin_To_Win/includes
 * @author     George Nicolaou <orionas.elite@gmail.com>
 */
class Gn_Tsiartas_Spin_To_Win_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'gn-tsiartas-spin-to-win',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
