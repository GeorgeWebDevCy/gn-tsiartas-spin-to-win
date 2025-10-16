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

        const OPTION_SPIN_DURATION = 'gn_tsiartas_spin_to_win_spin_duration';

        const OPTION_GROUP = 'gn_tsiartas_spin_to_win_settings';

        const SETTINGS_PAGE = 'gn-tsiartas-spin-to-win-settings';

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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

        /**
         * Register the Spin & Win settings page in the WordPress admin menu.
         *
         * @since    1.0.0
         * @return   void
         */
        public function register_admin_menu() {
                add_menu_page(
                        __( 'Spin & Win Settings', 'gn-tsiartas-spin-to-win' ),
                        __( 'Spin & Win', 'gn-tsiartas-spin-to-win' ),
                        'manage_options',
                        self::SETTINGS_PAGE,
                        array( $this, 'render_settings_page' ),
                        'dashicons-controls-repeat'
                );
        }

        /**
         * Register plugin settings and fields using the Settings API.
         *
         * @since    1.0.0
         * @return   void
         */
        public function register_settings() {
                register_setting(
                        self::OPTION_GROUP,
                        self::OPTION_SPIN_DURATION,
                        array(
                                'type'              => 'integer',
                                'sanitize_callback' => array( $this, 'sanitize_spin_duration' ),
                                'default'           => 4600,
                        )
                );

                add_settings_section(
                        'gn_tsiartas_spin_to_win_general',
                        __( 'General', 'gn-tsiartas-spin-to-win' ),
                        array( $this, 'render_general_section_intro' ),
                        self::SETTINGS_PAGE
                );

                add_settings_field(
                        self::OPTION_SPIN_DURATION,
                        __( 'Spin duration (ms)', 'gn-tsiartas-spin-to-win' ),
                        array( $this, 'render_spin_duration_field' ),
                        self::SETTINGS_PAGE,
                        'gn_tsiartas_spin_to_win_general'
                );
        }

        /**
         * Sanitize and validate the spin duration option.
         *
         * @since    1.0.0
         *
         * @param mixed $value Raw value provided by the administrator.
         *
         * @return int Sanitised duration in milliseconds.
         */
        public function sanitize_spin_duration( $value ) {
                $value = is_numeric( $value ) ? (int) $value : 0;

                if ( $value < 600 ) {
                        $value = 600;
                }

                if ( $value > 60000 ) {
                        $value = 60000;
                }

                return $value;
        }

        /**
         * Render the general settings section introduction.
         *
         * @since    1.0.0
         * @return   void
         */
        public function render_general_section_intro() {
                echo '<p>' . esc_html__( 'Configure the behaviour of the Spin & Win experience.', 'gn-tsiartas-spin-to-win' ) . '</p>';
        }

        /**
         * Output the spin duration field control.
         *
         * @since    1.0.0
         * @return   void
         */
        public function render_spin_duration_field() {
                $value = get_option( self::OPTION_SPIN_DURATION, 4600 );
                ?>
                <input
                        type="number"
                        name="<?php echo esc_attr( self::OPTION_SPIN_DURATION ); ?>"
                        id="<?php echo esc_attr( self::OPTION_SPIN_DURATION ); ?>"
                        value="<?php echo esc_attr( absint( $value ) ); ?>"
                        min="600"
                        max="60000"
                        step="100"
                        class="regular-text"
                />
                <p class="description">
                        <?php esc_html_e( 'Specify how long the wheel should spin in milliseconds. Recommended range: 0.6s (600) to 60s (60000).', 'gn-tsiartas-spin-to-win' ); ?>
                </p>
                <?php
        }

        /**
         * Render the settings page content using the admin partial.
         *
         * @since    1.0.0
         * @return   void
         */
        public function render_settings_page() {
                if ( ! current_user_can( 'manage_options' ) ) {
                        return;
                }

                $settings = array(
                        'spin_duration' => get_option( self::OPTION_SPIN_DURATION, 4600 ),
                );

                include plugin_dir_path( __FILE__ ) . 'partials/gn-tsiartas-spin-to-win-admin-display.php';
        }

}
