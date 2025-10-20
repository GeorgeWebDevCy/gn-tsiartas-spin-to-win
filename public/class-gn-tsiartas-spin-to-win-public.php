<?php
/**
 * GN Tsiartas Spin To Win – public class
 *
 * This implementation wires the front-end hooks used by the shortcode
 * and AJAX endpoint. The original production plugin ships with a much
 * richer codebase; the goal here is to provide a fully functional
 * reference implementation that showcases how the moving pieces fit
 * together.
 */

class Gn_Tsiartas_Spin_To_Win_Public {
	/**
	 * Unique plugin identifier used as the script/style handle.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Current plugin version. Used for cache-busting assets.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Cached copy of the merged front-end settings.
	 *
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * Initialise the public-facing class with identifiers and version data.
	 *
	 * @param string $plugin_name Plugin identifier used for handles.
	 * @param string $version     Version string used for cache busting.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Enqueue the public stylesheet.
	 *
	 * The handle mirrors the plugin slug so third parties can reliably
	 * deregister or override it as needed.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/gn-tsiartas-spin-to-win-public.css',
			array(),
			$this->version
		);
	}

	/**
	 * Enqueue the front-end script and expose configuration values.
	 *
	 * WordPress localisation helpers are used to surface the settings
	 * payload as a global object so vanilla JavaScript can interact with
	 * it without requiring build tools.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/gn-tsiartas-spin-to-win-public.js',
			array(),
			$this->version,
			true
		);

		$settings = $this->get_settings();
		$config   = $this->build_front_end_config( $settings );

		wp_localize_script(
			$this->plugin_name,
			'gnTsiartasSpinToWinConfig',
			$config
		);
	}

	/**
	 * Register both shortcodes used historically by the campaign.
	 */
	public function register_shortcodes() {
		// Original shortcode documented in the project README.
		add_shortcode( 'tsiartas_spin_to_win', array( $this, 'render_shortcode' ) );

		// Backwards-compatible alias that mirrors the plugin slug.
		add_shortcode( 'gn_tsiartas_spin_to_win', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render the shortcode output.
	 *
	 * @param array  $atts    Shortcode attributes provided in content.
	 * @param string $content Enclosed content (unused).
	 * @param string $tag     Shortcode tag that triggered the callback.
	 * @return string
	 */
	public function render_shortcode( $atts = array(), $content = '', $tag = '' ) {
		$atts = shortcode_atts(
			array(
				'show_notice' => 'true',
			),
			$atts,
			$tag
		);

		$settings          = $this->get_settings();
		$wheel_is_visible  = $this->is_within_active_window( $settings );
		$config            = $this->build_front_end_config( $settings );
		$config['settings']['wheelVisible'] = $wheel_is_visible;
		$cashier_notice    = isset( $settings['cashier_notice'] ) ? $settings['cashier_notice'] : '';
		$inactive_message  = __( 'The Spin & Win wheel is currently unavailable. Please visit during the configured hours.', 'gn-tsiartas-spin-to-win' );
		$show_cashier      = filter_var( $atts['show_notice'], FILTER_VALIDATE_BOOLEAN );

		$template = plugin_dir_path( __FILE__ ) . 'partials/gn-tsiartas-spin-to-win-public-display.php';
		if ( ! file_exists( $template ) ) {
			return '';
		}

		ob_start();

		$gn_tsiartas_spin_to_win_data = array(
			'config'              => $config,
			'wheel_is_visible'    => $wheel_is_visible,
			'cashier_notice'      => $cashier_notice,
			'inactive_message'    => $inactive_message,
			'show_cashier_notice' => $show_cashier,
		);

		include $template;

		return ob_get_clean();
	}

	/**
	 * Handle AJAX spin submissions triggered from the front-end.
	 */
	public function handle_spin_request() {
		if ( ! check_ajax_referer( $this->get_ajax_action(), 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security validation failed. Refresh the page and try again.', 'gn-tsiartas-spin-to-win' ),
				),
				403
			);
		}

		if ( 'POST' !== strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Spin requests must be sent using POST.', 'gn-tsiartas-spin-to-win' ),
				),
				405
			);
		}

		$prizes = $this->get_default_prizes();

		if ( empty( $prizes ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No prizes are configured at the moment. Please try again later.', 'gn-tsiartas-spin-to-win' ),
				)
		);
		}

		$prize = $this->choose_random_prize( $prizes );

		wp_send_json_success(
			array(
				'prizeId'             => isset( $prize['id'] ) ? $prize['id'] : '',
				'normalizedType'      => isset( $prize['type'] ) ? $prize['type'] : '',
				'awardedDenomination' => isset( $prize['value'] ) ? $prize['value'] : null,
				'label'               => isset( $prize['label'] ) ? $prize['label'] : '',
				'isTryAgain'          => ! empty( $prize['is_try_again'] ),
				'timestamp'           => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Build the configuration payload consumed by the public script.
	 *
	 * @param array $settings Normalised plugin settings.
	 * @return array
	 */
	private function build_front_end_config( $settings ) {
		$settings = is_array( $settings ) ? $settings : array();

		$config = array(
			'settings' => array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( $this->get_ajax_action() ),
				'spinDuration'  => isset( $settings['spin_duration'] ) ? (int) $settings['spin_duration'] : 0,
				'cashierNotice' => isset( $settings['cashier_notice'] ) ? $settings['cashier_notice'] : '',
				'activeWindow'  => array(
					'day'   => isset( $settings['active_day'] ) ? $settings['active_day'] : '',
					'start' => isset( $settings['active_start_time'] ) ? $settings['active_start_time'] : '',
					'end'   => isset( $settings['active_end_time'] ) ? $settings['active_end_time'] : '',
				),
				'generatedAt'   => current_time( 'mysql' ),
			),
			'prizes'   => $this->get_default_prizes(),
			'strings'  => array(
				'inactive' => __( 'The wheel is sleeping right now. Please come back during the promotion hours.', 'gn-tsiartas-spin-to-win' ),
				'spin'     => __( 'Spin the wheel', 'gn-tsiartas-spin-to-win' ),
			),
		);

		/**
		 * Allow developers to filter the front-end configuration array before
		 * it is handed to the script localisation helper.
		 */
		return apply_filters( 'gn_tsiartas_spin_to_win_front_end_config', $config, $settings );
	}

	/**
	 * Retrieve merged settings with defaults sourced from the admin class.
	 *
	 * @return array
	 */
	private function get_settings() {
		if ( null !== $this->settings ) {
			return $this->settings;
		}

		$defaults = array();
		if ( class_exists( 'Gn_Tsiartas_Spin_To_Win_Admin' ) && method_exists( 'Gn_Tsiartas_Spin_To_Win_Admin', 'get_default_settings' ) ) {
			$defaults = Gn_Tsiartas_Spin_To_Win_Admin::get_default_settings();
		}

		$option = get_option( $this->get_option_name(), array() );
		if ( ! is_array( $option ) ) {
			$option = array();
		}

		$this->settings = wp_parse_args( $option, $defaults );

		return $this->settings;
	}

	/**
	 * Return the database option name that stores plugin settings.
	 *
	 * @return string
	 */
	private function get_option_name() {
		return defined( 'GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME' ) ? GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME : 'gn_tsiartas_spin_to_win_settings';
	}

	/**
	 * Determine whether the current request happens inside the active window.
	 *
	 * @param array $settings Plugin settings.
	 * @return bool
	 */
	private function is_within_active_window( $settings ) {
		$settings = is_array( $settings ) ? $settings : array();

		$configured_day = isset( $settings['active_day'] ) ? strtolower( $settings['active_day'] ) : '';
		if ( '' === $configured_day ) {
			return true;
		}

		$timestamp   = current_time( 'timestamp' );
		$current_day = strtolower( wp_date( 'l', $timestamp ) );
		if ( $configured_day !== $current_day ) {
			return false;
		}

		$current_minutes = $this->convert_time_to_minutes( wp_date( 'H:i', $timestamp ) );
		$start_minutes   = $this->convert_time_to_minutes( isset( $settings['active_start_time'] ) ? $settings['active_start_time'] : '' );
		$end_minutes     = $this->convert_time_to_minutes( isset( $settings['active_end_time'] ) ? $settings['active_end_time'] : '' );

		if ( null === $current_minutes || null === $start_minutes || null === $end_minutes ) {
			return true;
		}

		if ( $start_minutes <= $end_minutes ) {
			return ( $current_minutes >= $start_minutes && $current_minutes <= $end_minutes );
		}

		return ( $current_minutes >= $start_minutes || $current_minutes <= $end_minutes );
	}

	/**
	 * Convert a HH:MM time string into total minutes.
	 *
	 * @param string $time Time string.
	 * @return int|null
	 */
	private function convert_time_to_minutes( $time ) {
		$time = trim( (string) $time );
		if ( '' === $time ) {
			return null;
		}

		$parsed = date_create_from_format( 'H:i', $time );
		if ( ! $parsed ) {
			return null;
		}

		return ( (int) $parsed->format( 'H' ) * 60 ) + (int) $parsed->format( 'i' );
	}

	/**
	 * Return the action identifier shared by AJAX requests and nonce creation.
	 *
	 * @return string
	 */
	private function get_ajax_action() {
		return 'gn_tsiartas_spin_to_win_spin';
	}

	/**
	 * Pick a random prize from the configured list.
	 *
	 * @param array $prizes Prize configuration array.
	 * @return array
	 */
	private function choose_random_prize( $prizes ) {
		$prizes = array_values( (array) $prizes );
		if ( empty( $prizes ) ) {
			return array();
		}

		$index = array_rand( $prizes );

		return isset( $prizes[ $index ] ) ? $prizes[ $index ] : array();
	}

	/**
	 * Return the default spin wheel prizes used when no custom data exists.
	 *
	 * @return array
	 */
	public function get_default_prizes() {
		return array(
			array(
				'id'          => 'voucher-5',
				'label'       => '€5',
				'description' => 'Win a €5 voucher',
				'colour'      => '#009688',
				'icon'        => 'gift',
				'value'       => 5,
				'type'        => 'voucher',
			),
			array(
				'id'           => 'try-again-a',
				'label'        => 'Try Again',
				'description'  => 'Better luck next time!',
				'colour'       => '#4CAF50',
				'icon'         => 'redo',
				'type'         => 'try-again',
				'is_try_again' => true,
			),
			array(
				'id'          => 'voucher-10',
				'label'       => '€10',
				'description' => 'Win a €10 voucher',
				'colour'      => '#FFC107',
				'icon'        => 'gift',
				'value'       => 10,
				'type'        => 'voucher',
			),
			array(
				'id'           => 'try-again-b',
				'label'        => 'Try Again',
				'description'  => 'Better luck next time!',
				'colour'       => '#F44336',
				'icon'         => 'redo',
				'type'         => 'try-again',
				'is_try_again' => true,
			),
			array(
				'id'          => 'voucher-50',
				'label'       => '€50',
				'description' => 'Win a €50 voucher',
				'colour'      => '#3F51B5',
				'icon'        => 'gift',
				'value'       => 50,
				'type'        => 'voucher',
			),
			array(
				'id'           => 'try-again-c',
				'label'        => 'Try Again',
				'description'  => 'Better luck next time!',
				'colour'       => '#FF5722',
				'icon'         => 'redo',
				'type'         => 'try-again',
				'is_try_again' => true,
			),
			array(
				'id'          => 'voucher-100',
				'label'       => '€100',
				'description' => 'Win a €100 voucher',
				'colour'      => '#00BCD4',
				'icon'        => 'gift',
				'value'       => 100,
				'type'        => 'voucher',
			),
			array(
				'id'           => 'try-again-d',
				'label'        => 'Try Again',
				'description'  => 'Better luck next time!',
				'colour'       => '#8BC34A',
				'icon'         => 'redo',
				'type'         => 'try-again',
				'is_try_again' => true,
			),
		);
	}

}
