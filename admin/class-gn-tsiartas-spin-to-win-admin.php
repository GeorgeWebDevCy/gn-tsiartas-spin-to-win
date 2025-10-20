<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://www.georgenicolaou.me/
 * @since      1.0.0
 *
 * @package    Gn_Tsiartas_Spin_To_Win
 * @subpackage Gn_Tsiartas_Spin_To_Win/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Gn_Tsiartas_Spin_To_Win
 * @subpackage Gn_Tsiartas_Spin_To_Win/admin
 * @author     George Nicolaou <orionas.elite@gmail.com>
 */
class Gn_Tsiartas_Spin_To_Win_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

        /**
         * Cached copy of the saved settings.
         *
         * @since    1.3.3
         * @access   private
         * @var      array
         */
        private $settings = null;

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @param      string    $plugin_name       The name of this plugin.
         * @param      string    $version    The version of this plugin.
         */
        public function __construct( $plugin_name, $version ) {

                $this->plugin_name = $plugin_name;
                $this->version     = $version;

        }

        /**
         * Retrieve the default plugin settings.
         *
         * @since    1.3.3
         *
         * @return   array
         */
        public static function get_default_settings() {
                return array(
                        'spin_duration'      => 4600,
                        'active_day'         => 'friday',
                        'active_start_time'  => '07:00',
                        'active_end_time'    => '20:00',
                        'cashier_notice'     => __( 'Please spin the wheel in front of the cashier.', 'gn-tsiartas-spin-to-win' ),
                        'friday_quotas'      => array(
                                '5'   => 0,
                                '10'  => 0,
                                '50'  => 1,
                                '100' => 1,
                        ),
                );
        }

        /**
         * Register the plugin settings page in the WordPress admin.
         *
         * @since    1.3.3
         * @return   void
         */
        public function register_admin_menu() {
                add_menu_page(
                        __( 'Spin & Win Settings', 'gn-tsiartas-spin-to-win' ),
                        __( 'Spin & Win', 'gn-tsiartas-spin-to-win' ),
                        'manage_options',
                        'gn-tsiartas-spin-to-win',
                        array( $this, 'render_settings_page' ),
                        'dashicons-controls-repeat',
                        65
                );
        }

        /**
         * Render the plugin settings page.
         *
         * @since    1.3.3
         * @return   void
         */
        public function render_settings_page() {
                if ( ! current_user_can( 'manage_options' ) ) {
                        return;
                }

                $settings    = $this->get_settings();
                $option_name = $this->get_option_name();
                $timestamp   = current_time( 'timestamp', true );

                $server_day      = wp_date( 'l', $timestamp );
                $server_datetime = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
                $wheel_is_visible = $this->is_within_active_window( $settings );

                include plugin_dir_path( __FILE__ ) . 'partials/gn-tsiartas-spin-to-win-admin-display.php';
        }

        /**
         * Register settings, sections, and fields.
         *
         * @since    1.3.3
         * @return   void
         */
        public function register_settings() {
                register_setting( 'gn_tsiartas_spin_to_win', $this->get_option_name(), array( $this, 'sanitize_settings' ) );

                add_settings_section(
                        'gn_tsiartas_spin_to_win_general',
                        __( 'General Settings', 'gn-tsiartas-spin-to-win' ),
                        array( $this, 'render_general_settings_section' ),
                        'gn_tsiartas_spin_to_win'
                );

                add_settings_field(
                        'gn_tsiartas_spin_to_win_spin_duration',
                        __( 'Spin duration (milliseconds)', 'gn-tsiartas-spin-to-win' ),
                        array( $this, 'render_spin_duration_field' ),
                        'gn_tsiartas_spin_to_win',
                        'gn_tsiartas_spin_to_win_general'
                );

                add_settings_field(
                        'gn_tsiartas_spin_to_win_active_window',
                        __( 'Active window', 'gn-tsiartas-spin-to-win' ),
                        array( $this, 'render_active_window_field' ),
                        'gn_tsiartas_spin_to_win',
                        'gn_tsiartas_spin_to_win_general'
                );

                add_settings_field(
                        'gn_tsiartas_spin_to_win_cashier_notice',
                        __( 'Cashier notification', 'gn-tsiartas-spin-to-win' ),
                        array( $this, 'render_cashier_notice_field' ),
                        'gn_tsiartas_spin_to_win',
                        'gn_tsiartas_spin_to_win_general'
                );

                add_settings_field(
                        'gn_tsiartas_spin_to_win_friday_quotas',
                        __( 'Friday voucher quotas', 'gn-tsiartas-spin-to-win' ),
                        array( $this, 'render_friday_quotas_field' ),
                        'gn_tsiartas_spin_to_win',
                        'gn_tsiartas_spin_to_win_general'
                );
        }

        /**
         * Render a description for the general settings section.
         *
         * @since    1.3.3
         * @return   void
         */
        public function render_general_settings_section() {
                echo '<p>' . esc_html__( 'Control how the Spin & Win experience behaves on the front-end.', 'gn-tsiartas-spin-to-win' ) . '</p>';
        }

        /**
         * Render the spin duration field.
         *
         * @since    1.3.3
         * @return   void
         */
        public function render_spin_duration_field() {
                $settings    = $this->get_settings();
                $option_name = $this->get_option_name();
                ?>
                <input
                        type="number"
                        class="small-text"
                        id="gn-tsiartas-spin-duration"
                        name="<?php echo esc_attr( $option_name ); ?>[spin_duration]"
                        value="<?php echo esc_attr( $settings['spin_duration'] ); ?>"
                        min="1000"
                        step="100"
                />
                <p class="description">
                        <?php esc_html_e( 'Set how long the wheel spins in milliseconds (1 second = 1000ms).', 'gn-tsiartas-spin-to-win' ); ?>
                </p>
                <?php
        }

        /**
         * Render the active window field.
         *
         * @since    1.3.3
         * @return   void
         */
        public function render_active_window_field() {
                $settings     = $this->get_settings();
                $option_name  = $this->get_option_name();
                $day_options  = $this->get_weekday_options();
                ?>
                <label for="gn-tsiartas-spin-day" class="screen-reader-text"><?php esc_html_e( 'Active day', 'gn-tsiartas-spin-to-win' ); ?></label>
                <select id="gn-tsiartas-spin-day" name="<?php echo esc_attr( $option_name ); ?>[active_day]">
                        <?php foreach ( $day_options as $value => $label ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['active_day'], $value ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select>
                <label for="gn-tsiartas-spin-start" class="screen-reader-text"><?php esc_html_e( 'Start time', 'gn-tsiartas-spin-to-win' ); ?></label>
                <input
                        type="time"
                        id="gn-tsiartas-spin-start"
                        name="<?php echo esc_attr( $option_name ); ?>[active_start_time]"
                        value="<?php echo esc_attr( $settings['active_start_time'] ); ?>"
                />
                <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                <label for="gn-tsiartas-spin-end" class="screen-reader-text"><?php esc_html_e( 'End time', 'gn-tsiartas-spin-to-win' ); ?></label>
                <input
                        type="time"
                        id="gn-tsiartas-spin-end"
                        name="<?php echo esc_attr( $option_name ); ?>[active_end_time]"
                        value="<?php echo esc_attr( $settings['active_end_time'] ); ?>"
                />
                <p class="description">
                        <?php esc_html_e( 'Choose the day and local time window when the wheel should appear. Outside of this range the shortcode will remain hidden.', 'gn-tsiartas-spin-to-win' ); ?>
                </p>
                <?php
        }

        /**
         * Render the cashier notice field.
         *
         * @since    1.3.3
         * @return   void
         */
        public function render_cashier_notice_field() {
                $settings    = $this->get_settings();
                $option_name = $this->get_option_name();
                ?>
                <textarea
                        id="gn-tsiartas-spin-cashier-notice"
                        name="<?php echo esc_attr( $option_name ); ?>[cashier_notice]"
                        rows="3"
                        cols="50"
                        class="large-text"
                ><?php echo esc_textarea( $settings['cashier_notice'] ); ?></textarea>
                <p class="description">
                        <?php esc_html_e( 'Displayed alongside the wheel so shoppers remember to spin it in front of the cashier.', 'gn-tsiartas-spin-to-win' ); ?>
                </p>
                <?php
        }

        /**
         * Render the Friday voucher quotas field.
         *
         * @since    2.2.0
         * @return   void
         */
        public function render_friday_quotas_field() {
                $settings    = $this->get_settings();
                $option_name = $this->get_option_name();
                $quotas      = isset( $settings['friday_quotas'] ) && is_array( $settings['friday_quotas'] ) ? $settings['friday_quotas'] : array();
                $defaults    = self::get_default_settings();
                $quotas      = array_replace( $defaults['friday_quotas'], $quotas );

                $denominations = array(
                        '5'   => __( '€5 vouchers', 'gn-tsiartas-spin-to-win' ),
                        '10'  => __( '€10 vouchers', 'gn-tsiartas-spin-to-win' ),
                        '50'  => __( '€50 vouchers', 'gn-tsiartas-spin-to-win' ),
                        '100' => __( '€100 vouchers', 'gn-tsiartas-spin-to-win' ),
                );
                ?>
                <fieldset class="gn-tsiartas-spin-to-win__friday-quotas">
                        <legend class="screen-reader-text"><?php esc_html_e( 'Friday voucher quotas', 'gn-tsiartas-spin-to-win' ); ?></legend>
                        <p class="description">
                                <?php esc_html_e( 'Set how many vouchers of each value can be awarded each Friday. Higher value vouchers default to one guaranteed win.', 'gn-tsiartas-spin-to-win' ); ?>
                        </p>
                        <table class="form-table">
                                <tbody>
                                        <?php foreach ( $denominations as $value => $label ) :
                                                $field_id = 'gn-tsiartas-spin-friday-quota-' . $value;
                                                ?>
                                                <tr>
                                                        <th scope="row">
                                                                <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
                                                        </th>
                                                        <td>
                                                                <input
                                                                        type="number"
                                                                        class="small-text"
                                                                        id="<?php echo esc_attr( $field_id ); ?>"
                                                                        name="<?php echo esc_attr( $option_name ); ?>[friday_quotas][<?php echo esc_attr( $value ); ?>]"
                                                                        value="<?php echo esc_attr( isset( $quotas[ $value ] ) ? (int) $quotas[ $value ] : 0 ); ?>"
                                                                        min="<?php echo in_array( $value, array( '50', '100' ), true ) ? '1' : '0'; ?>"
                                                                />
                                                        </td>
                                                </tr>
                                        <?php endforeach; ?>
                                </tbody>
                        </table>
                </fieldset>
                <?php
        }

        /**
         * Sanitize and validate the plugin settings.
         *
         * @since    1.3.3
         *
         * @param    array $settings Submitted settings.
         *
         * @return   array
         */
        public function sanitize_settings( $settings ) {
                $defaults    = self::get_default_settings();
                $option_name = $this->get_option_name();

                if ( ! is_array( $settings ) ) {
                        $settings = array();
                }

                $sanitized = array();

                $spin_duration = isset( $settings['spin_duration'] ) ? absint( $settings['spin_duration'] ) : 0;
                if ( $spin_duration < 1000 ) {
                        $sanitized['spin_duration'] = $defaults['spin_duration'];
                        add_settings_error(
                                $option_name,
                                $option_name . '_spin_duration',
                                __( 'Please enter a spin duration of at least 1000 milliseconds.', 'gn-tsiartas-spin-to-win' )
                        );
                } else {
                        $sanitized['spin_duration'] = $spin_duration;
                }

                $allowed_days = array_keys( $this->get_weekday_options() );
                $active_day   = isset( $settings['active_day'] ) ? strtolower( sanitize_text_field( $settings['active_day'] ) ) : '';
                if ( ! in_array( $active_day, $allowed_days, true ) ) {
                        $active_day = $defaults['active_day'];
                        add_settings_error(
                                $option_name,
                                $option_name . '_active_day',
                                __( 'Please choose a valid day of the week.', 'gn-tsiartas-spin-to-win' )
                        );
                }
                $sanitized['active_day'] = $active_day;

                $start_time = isset( $settings['active_start_time'] ) ? $this->sanitize_time_field( $settings['active_start_time'] ) : false;
                if ( false === $start_time ) {
                        $start_time = $defaults['active_start_time'];
                        add_settings_error(
                                $option_name,
                                $option_name . '_start_time',
                                __( 'Please provide a valid start time.', 'gn-tsiartas-spin-to-win' )
                        );
                }
                $sanitized['active_start_time'] = $start_time;

                $end_time = isset( $settings['active_end_time'] ) ? $this->sanitize_time_field( $settings['active_end_time'] ) : false;
                if ( false === $end_time ) {
                        $end_time = $defaults['active_end_time'];
                        add_settings_error(
                                $option_name,
                                $option_name . '_end_time',
                                __( 'Please provide a valid end time.', 'gn-tsiartas-spin-to-win' )
                        );
                }
                $sanitized['active_end_time'] = $end_time;

                $notice = isset( $settings['cashier_notice'] ) ? sanitize_textarea_field( $settings['cashier_notice'] ) : '';
                if ( '' === $notice ) {
                        $notice = $defaults['cashier_notice'];
                }
                $sanitized['cashier_notice'] = $notice;

                $sanitized['friday_quotas'] = $this->sanitize_friday_quotas( isset( $settings['friday_quotas'] ) ? $settings['friday_quotas'] : array(), $option_name );

                $this->settings = null;

                return $sanitized;
        }

        /**
         * Sanitize the configured Friday voucher quotas.
         *
         * @since    2.2.0
         *
         * @param    array  $input       Submitted quota values.
         * @param    string $option_name Option name used for settings errors.
         *
         * @return   array
         */
        private function sanitize_friday_quotas( $input, $option_name ) {
                $defaults = self::get_default_settings();
                $defaults = isset( $defaults['friday_quotas'] ) ? $defaults['friday_quotas'] : array();

                if ( ! is_array( $input ) ) {
                        $input = array();
                }

                $sanitized = array();
                foreach ( $defaults as $value => $default_quota ) {
                        $raw = isset( $input[ $value ] ) ? $input[ $value ] : $default_quota;
                        $quota = max( 0, absint( $raw ) );

                        if ( in_array( $value, array( '50', '100' ), true ) && $quota < 1 ) {
                                $quota = 1;
                                add_settings_error(
                                        $option_name,
                                        $option_name . '_friday_quota_' . $value,
                                        sprintf(
                                                /* translators: %s: voucher value */
                                                __( 'At least one %s voucher must be available each Friday to honour the guaranteed spins.', 'gn-tsiartas-spin-to-win' ),
                                                '€' . $value
                                        )
                                );
                        }

                        $sanitized[ $value ] = $quota;
                }

                return $sanitized;
        }

        /**
         * Retrieve the saved settings merged with defaults.
         *
         * @since    1.3.3
         *
         * @return   array
         */
        private function get_settings() {
                if ( null !== $this->settings ) {
                        return $this->settings;
                }

                $saved = get_option( $this->get_option_name(), array() );
                if ( ! is_array( $saved ) ) {
                        $saved = array();
                }

                $this->settings = wp_parse_args( $saved, self::get_default_settings() );

                return $this->settings;
        }

        /**
         * Convert a submitted time string into a normalised value.
         *
         * @since    1.3.3
         *
         * @param    string $value Raw time input.
         *
         * @return   string|false  Time in H:i format or false on failure.
         */
        private function sanitize_time_field( $value ) {
                $value = trim( (string) $value );
                if ( '' === $value ) {
                        return false;
                }

                $time = date_create_from_format( 'H:i', $value );
                if ( ! $time ) {
                        $time = date_create_from_format( 'G:i', $value );
                }

                if ( ! $time ) {
                        return false;
                }

                return $time->format( 'H:i' );
        }

        /**
         * Return an array of weekday options.
         *
         * @since    1.3.3
         *
         * @return   array
         */
        private function get_weekday_options() {
                return array(
                        'monday'    => __( 'Monday', 'gn-tsiartas-spin-to-win' ),
                        'tuesday'   => __( 'Tuesday', 'gn-tsiartas-spin-to-win' ),
                        'wednesday' => __( 'Wednesday', 'gn-tsiartas-spin-to-win' ),
                        'thursday'  => __( 'Thursday', 'gn-tsiartas-spin-to-win' ),
                        'friday'    => __( 'Friday', 'gn-tsiartas-spin-to-win' ),
                        'saturday'  => __( 'Saturday', 'gn-tsiartas-spin-to-win' ),
                        'sunday'    => __( 'Sunday', 'gn-tsiartas-spin-to-win' ),
                );
        }

        /**
         * Retrieve the name of the option used to store settings.
         *
         * @since    1.3.3
         *
         * @return   string
         */
        private function get_option_name() {
                return defined( 'GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME' ) ? GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME : 'gn_tsiartas_spin_to_win_settings';
        }

        /**
         * Determine if the current request falls within the configured active window.
         *
         * @since    1.3.3
         *
         * @param    array $settings Plugin settings.
         *
         * @return   bool
         */
        private function is_within_active_window( $settings ) {
                $configured_day = isset( $settings['active_day'] ) ? strtolower( $settings['active_day'] ) : '';
                if ( '' === $configured_day ) {
                        return true;
                }

                $timestamp   = current_time( 'timestamp', true );
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
         * Convert a time string (H:i) into total minutes.
         *
         * @since    1.3.3
         *
         * @param    string $time Time string.
         *
         * @return   int|null
         */
        private function convert_time_to_minutes( $time ) {
                if ( empty( $time ) ) {
                        return null;
                }

                $parsed = date_create_from_format( 'H:i', $time );
                if ( ! $parsed ) {
                        return null;
                }

                return ( (int) $parsed->format( 'H' ) * 60 ) + (int) $parsed->format( 'i' );
        }

        /**
         * Add plugin action links for the settings page.
         *
         * @since    2.2.5
         *
         * @param    array $links Existing action links.
         *
         * @return   array
         */
        public function add_plugin_action_links( $links ) {
                $settings_link = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( admin_url( 'admin.php?page=gn-tsiartas-spin-to-win' ) ),
                        esc_html__( 'Settings', 'gn-tsiartas-spin-to-win' )
                );

                array_unshift( $links, $settings_link );

                return $links;
        }

        /**
         * Register the stylesheets for the admin area.
         *
         * @since    1.0.0
         */
        public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gn_Tsiartas_Spin_To_Win_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gn_Tsiartas_Spin_To_Win_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gn-tsiartas-spin-to-win-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gn_Tsiartas_Spin_To_Win_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gn_Tsiartas_Spin_To_Win_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gn-tsiartas-spin-to-win-admin.js', array( 'jquery' ), $this->version, false );

	}

}
