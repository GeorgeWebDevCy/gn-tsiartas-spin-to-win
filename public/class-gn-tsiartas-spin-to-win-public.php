<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://www.georgenicolaou.me/
 * @since      1.0.0
 *
 * @package    Gn_Tsiartas_Spin_To_Win
 * @subpackage Gn_Tsiartas_Spin_To_Win/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Gn_Tsiartas_Spin_To_Win
 * @subpackage Gn_Tsiartas_Spin_To_Win/public
 * @author     George Nicolaou <orionas.elite@gmail.com>
 */
class Gn_Tsiartas_Spin_To_Win_Public {

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
         * Tracks the number of shortcode instances rendered on the current request.
         *
         * @since    1.0.0
         * @access   private
         * @var      int
         */
        private $instance_counter = 0;

        /**
         * Aggregated data that will be passed to the public script via localization.
         *
         * @since    1.0.0
         * @access   private
         * @var      array
         */
        private $localized_data = array(
                'instances' => array(),
        );

        /**
         * Whether the front-end data has already been localised during this request.
         *
         * @since    1.4.13
         * @access   private
         * @var      bool
         */
        private $has_localized_data = false;

        /**
         * Cached copy of plugin-level settings.
         *
         * @since    1.3.3
         * @access   private
         * @var      array|null
         */
        private $plugin_settings = null;

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @param      string    $plugin_name       The name of the plugin.
         * @param      string    $version    The version of this plugin.
         */
        public function __construct( $plugin_name, $version ) {

                $this->plugin_name = $plugin_name;
                $this->version     = $version;

        }

        /**
         * Register the stylesheets for the public-facing side of the site.
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

                wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gn-tsiartas-spin-to-win-public.css', array(), $this->version, 'all' );

        }

        /**
         * Register the JavaScript for the public-facing side of the site.
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

                wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gn-tsiartas-spin-to-win-public.js', array( 'jquery' ), $this->version, true );

        }

        /**
         * Register the public shortcodes.
         *
         * @since    1.0.0
         * @return   void
         */
        public function register_shortcodes() {
                add_shortcode( 'tsiartas_spin_to_win', array( $this, 'render_spin_to_win_shortcode' ) );
        }

        /**
         * Renders the Spin to Win experience markup.
         *
         * @since    1.0.0
         *
         * @param    array       $atts    Shortcode attributes provided by the author.
         * @param    string|null $content Optional content enclosed by the shortcode.
         *
         * @return   string               HTML output for the front-end experience.
         */
        public function render_spin_to_win_shortcode( $atts, $content = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
                $settings = $this->get_plugin_settings();

                if ( ! $this->is_within_active_window( $settings ) ) {
                        return '';
                }

                $this->instance_counter++;
                $instance_id = 'gn-tsiartas-spin-to-win-' . $this->instance_counter;

                $atts = shortcode_atts(
                        array(
                                'show_cta' => 'true',
                        ),
                        $atts,
                        'tsiartas_spin_to_win'
                );

                $configuration = $this->prepare_frontend_configuration( $instance_id, $atts );
                $this->localized_data['instances'][ $instance_id ] = $configuration;
                $this->localized_data['settings']                 = $this->get_global_settings( $settings );

                // Ensure public assets are present and data is localized for the script.
                wp_enqueue_style( $this->plugin_name );
                wp_enqueue_script( $this->plugin_name );

                $show_cta = filter_var( $atts['show_cta'], FILTER_VALIDATE_BOOLEAN );
                $prizes   = isset( $configuration['prizes'] ) ? $configuration['prizes'] : array();
                $messages = isset( $configuration['messages'] ) ? $configuration['messages'] : array();

                $logo_url = plugins_url(
                        'public/images/TSIARTAS-logo-transparent.png',
                        dirname( __DIR__ ) . '/gn-tsiartas-spin-to-win.php'
                );

                ob_start();
                ?>
                <section
                        id="<?php echo esc_attr( $instance_id ); ?>"
                        class="gn-tsiartas-spin-to-win"
                        role="region"
                        aria-label="<?php echo esc_attr__( 'Spin to Win promotion', 'gn-tsiartas-spin-to-win' ); ?>"
                        data-gn-tsiartas-spin-instance="<?php echo esc_attr( $instance_id ); ?>"
                >
                        <div class="gn-tsiartas-spin-to-win__wheel-area">
                                <p class="gn-tsiartas-spin-to-win__date" data-role="date-line">
                                        <span class="gn-tsiartas-spin-to-win__date-label"><?php echo esc_html__( 'Current date:', 'gn-tsiartas-spin-to-win' ); ?></span>
                                        <span class="gn-tsiartas-spin-to-win__date-value" data-role="date-value" aria-live="polite"></span>
                                </p>
                                <div class="gn-tsiartas-spin-to-win__wheel" data-role="wheel" aria-live="polite">
                                        <div class="gn-tsiartas-spin-to-win__logo">
                                                <img
                                                        class="gn-tsiartas-spin-to-win__logo-image"
                                                        src="<?php echo esc_url( $logo_url ); ?>"
                                                        alt="<?php echo esc_attr__( 'Tsiartas Supermarkets logo', 'gn-tsiartas-spin-to-win' ); ?>"
                                                />
                                        </div>
                                </div>
                                <button type="button" class="gn-tsiartas-spin-to-win__spin-button" data-action="spin">
                                        <span class="gn-tsiartas-spin-to-win__spin-pointer" aria-hidden="true"></span>
                                        <span class="gn-tsiartas-spin-to-win__spin-label"><?php echo esc_html__( 'Spin the wheel', 'gn-tsiartas-spin-to-win' ); ?></span>
                                </button>
                        </div>
                        <div class="gn-tsiartas-spin-to-win__sidebar">
                                <div class="gn-tsiartas-spin-to-win__message" data-role="message">
                                        <p class="gn-tsiartas-spin-to-win__message-text" data-role="message-text">
                                                <?php echo isset( $messages['prompt'] ) ? esc_html( $messages['prompt'] ) : esc_html__( 'Try your luck and spin the wheel!', 'gn-tsiartas-spin-to-win' ); ?>
                                        </p>
                                        <?php if ( ! empty( $settings['cashier_notice'] ) ) : ?>
                                                <p class="gn-tsiartas-spin-to-win__cashier-notice">
                                                        <?php echo esc_html( $settings['cashier_notice'] ); ?>
                                                </p>
                                        <?php endif; ?>
                                </div>
                                <?php if ( $show_cta ) : ?>
                                        <div class="gn-tsiartas-spin-to-win__ctas" data-role="cta-container">
                                                <?php echo $this->render_cta_buttons( $configuration ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        </div>
                                <?php endif; ?>
                        </div>
                        <div class="gn-tsiartas-spin-to-win__modal" role="dialog" aria-modal="true" aria-hidden="true" data-role="result-modal">
                                <div class="gn-tsiartas-spin-to-win__modal-content" role="document">
                                        <h2 class="gn-tsiartas-spin-to-win__modal-title" data-role="modal-title"></h2>
                                        <p class="gn-tsiartas-spin-to-win__modal-message" data-role="modal-message"></p>
                                        <p class="gn-tsiartas-spin-to-win__modal-date">
                                                <span class="gn-tsiartas-spin-to-win__modal-date-label"><?php echo esc_html__( 'Current date:', 'gn-tsiartas-spin-to-win' ); ?></span>
                                                <span class="gn-tsiartas-spin-to-win__modal-date-value" data-role="modal-date" aria-live="polite"></span>
                                        </p>
                                        <button type="button" class="gn-tsiartas-spin-to-win__modal-close" data-action="close-modal">
                                                <span class="gn-tsiartas-spin-to-win__modal-close-label"><?php echo esc_html__( 'Close', 'gn-tsiartas-spin-to-win' ); ?></span>
                                        </button>
                                </div>
                        </div>
                        <div class="gn-tsiartas-spin-to-win__desktop-notice" data-role="desktop-notice" aria-hidden="true">
                                <div class="gn-tsiartas-spin-to-win__desktop-notice-card">
                                        <span class="gn-tsiartas-spin-to-win__desktop-notice-icon" aria-hidden="true">📱</span>
                                        <h2 class="gn-tsiartas-spin-to-win__desktop-notice-title"><?php echo esc_html__( 'Mobile exclusive experience', 'gn-tsiartas-spin-to-win' ); ?></h2>
                                        <p class="gn-tsiartas-spin-to-win__desktop-notice-text"><?php echo esc_html__( 'This promotion is only available when you scan the in-store QR code on your phone.', 'gn-tsiartas-spin-to-win' ); ?></p>
                                        <p class="gn-tsiartas-spin-to-win__desktop-notice-subtext"><?php echo esc_html__( 'Grab your smartphone, visit Tsiartas Supermarket, and scan the QR code at the entrance to start spinning!', 'gn-tsiartas-spin-to-win' ); ?></p>
                                </div>
                        </div>
                </section>
                <?php
                return (string) ob_get_clean();
        }

        /**
         * Localize the aggregated front-end data for the public script.
         *
         * @since    1.4.13
         *
         * @return   void
         */
        public function localize_script_data() {
                if ( $this->has_localized_data ) {
                        return;
                }

                if ( ! wp_script_is( $this->plugin_name, 'enqueued' ) ) {
                        return;
                }

                if ( empty( $this->localized_data['instances'] ) ) {
                        return;
                }

                if ( empty( $this->localized_data['settings'] ) ) {
                        $this->localized_data['settings'] = $this->get_global_settings();
                }

                wp_localize_script(
                        $this->plugin_name,
                        'gnTsiartasSpinToWinConfig',
                        array(
                                'instances' => $this->localized_data['instances'],
                                'settings'  => $this->localized_data['settings'],
                        )
                );

                $this->has_localized_data = true;
        }

        /**
         * Render CTA buttons markup based on the instance configuration.
         *
         * @since    1.0.0
         *
         * @param    array $configuration Instance configuration array.
         *
         * @return   string
         */
        private function render_cta_buttons( $configuration ) {
                if ( empty( $configuration['ctas'] ) || ! is_array( $configuration['ctas'] ) ) {
                        return '';
                }

                $buttons = array();
                foreach ( $configuration['ctas'] as $cta ) {
                        if ( empty( $cta['label'] ) || empty( $cta['url'] ) ) {
                                continue;
                        }

                        $buttons[] = sprintf(
                                '<a class="gn-tsiartas-spin-to-win__cta-button" href="%1$s" data-role="cta" target="%2$s" rel="%3$s">%4$s</a>',
                                esc_url( $cta['url'] ),
                                ! empty( $cta['target'] ) ? esc_attr( $cta['target'] ) : '_self',
                                ! empty( $cta['rel'] ) ? esc_attr( $cta['rel'] ) : 'noopener',
                                esc_html( $cta['label'] )
                        );
                }

                return implode( '', $buttons );
        }

        /**
         * Prepare the configuration array that will be exposed to the public script.
         *
         * @since    1.0.0
         *
         * @param    string $instance_id Unique identifier of the shortcode instance.
         * @param    array  $atts        Shortcode attributes.
         *
         * @return   array
         */
        private function prepare_frontend_configuration( $instance_id, $atts ) {
                $prizes_option = get_option( 'gn_tsiartas_spin_to_win_prizes', array() );
                $prizes        = $this->normalise_prizes( $prizes_option );

                $messages = get_option( 'gn_tsiartas_spin_to_win_messages', array() );
                if ( ! is_array( $messages ) ) {
                        $messages = array();
                }

                $defaults = $this->get_default_messages();
                $messages = wp_parse_args( $messages, $defaults );

                $cta_option = get_option( 'gn_tsiartas_spin_to_win_ctas', array() );
                $ctas       = $this->normalise_ctas( $cta_option );

                $audio_settings = get_option( 'gn_tsiartas_spin_to_win_audio', array() );
                if ( ! is_array( $audio_settings ) ) {
                        $audio_settings = array();
                }

                $audio_settings = wp_parse_args(
                        $audio_settings,
                        array(
                                'spin' => '',
                                'win'  => '',
                                'lose' => '',
                        )
                );

                $audio_settings['spin'] = plugins_url( 'public/audio/spin.mp3', GN_TSIARTAS_SPIN_TO_WIN_PLUGIN_FILE );
                $audio_settings['win']  = plugins_url( 'public/audio/win.mp3', GN_TSIARTAS_SPIN_TO_WIN_PLUGIN_FILE );

                return array(
                        'id'         => $instance_id,
                        'prizes'     => ! empty( $prizes ) ? $prizes : $this->get_default_prizes(),
                        'messages'   => $messages,
                        'ctas'       => $ctas,
                        'audio'      => array(
                                'spin' => esc_url_raw( $audio_settings['spin'] ),
                                'win'  => esc_url_raw( $audio_settings['win'] ),
                                'lose' => esc_url_raw( $audio_settings['lose'] ),
                        ),
                        'attributes' => $atts,
                );
        }

        /**
         * Retrieve global settings shared across all shortcode instances.
         *
         * @since    1.0.0
         *
         * @return   array
         */
        private function get_global_settings( $settings = null ) {
                if ( null === $settings ) {
                        $settings = $this->get_plugin_settings();
                }

                $spin_duration     = isset( $settings['spin_duration'] ) ? (int) $settings['spin_duration'] : 4600;
                $current_timestamp = current_time( 'timestamp' );
                $date_format       = get_option( 'date_format' );
                if ( empty( $date_format ) ) {
                        $date_format = 'F j, Y';
                }

                $time_format = get_option( 'time_format' );
                if ( empty( $time_format ) ) {
                        $time_format = 'g:i a';
                }

                $localized_date = wp_date( $date_format, $current_timestamp );
                $localized_time = wp_date( $time_format, $current_timestamp );
                $iso_date       = wp_date( DATE_ATOM, $current_timestamp );

                if ( false === $localized_date ) {
                        $localized_date = '';
                }

                if ( false === $localized_time ) {
                        $localized_time = '';
                }

                if ( false === $iso_date ) {
                        $iso_date = '';
                }

                $localized_datetime = $localized_date;

                if ( '' !== $localized_time ) {
                        $localized_datetime = sprintf(
                                /* translators: 1: localized current date, 2: localized current time */
                                _x( '%1$s at %2$s', 'current date/time format', 'gn-tsiartas-spin-to-win' ),
                                $localized_date,
                                $localized_time
                        );
                }

                $active_day = isset( $settings['active_day'] ) ? $settings['active_day'] : '';
                if ( 'any' === $active_day ) {
                        $active_day = '';
                }

                return array(
                        'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                        'nonce'         => wp_create_nonce( 'gn-tsiartas-spin-to-win' ),
                        'pluginUrl'     => plugin_dir_url( __FILE__ ),
                        'spinDuration'  => $spin_duration,
                        'ajaxAction'    => 'gn_tsiartas_spin_to_win_spin',
                        'activeWindow'  => array(
                                'day'   => $active_day,
                                'start' => isset( $settings['active_start_time'] ) ? $settings['active_start_time'] : '',
                                'end'   => isset( $settings['active_end_time'] ) ? $settings['active_end_time'] : '',
                        ),
                        'storeHours'    => array(
                                'start' => isset( $settings['active_start_time'] ) ? $settings['active_start_time'] : '07:00',
                                'end'   => isset( $settings['active_end_time'] ) ? $settings['active_end_time'] : '20:00',
                        ),
                        'voucherQuotas' => $this->prepare_voucher_quotas( isset( $settings['voucher_quotas'] ) ? $settings['voucher_quotas'] : array() ),
                        'cashierNotice' => isset( $settings['cashier_notice'] ) ? $settings['cashier_notice'] : '',
                        'currentDate'   => $localized_datetime,
                        'currentDateIso' => $iso_date,
                );
        }

        /**
         * Retrieve the saved plugin settings.
         *
         * @since    1.3.3
         *
         * @return   array
         */
        private function get_plugin_settings() {
                if ( null !== $this->plugin_settings ) {
                        return $this->plugin_settings;
                }

                $defaults = array(
                        'spin_duration'      => 4600,
                        'active_day'         => 'any',
                        'active_start_time'  => '00:00',
                        'active_end_time'    => '23:59',
                        'cashier_notice'     => __( 'Please spin the wheel in front of the cashier.', 'gn-tsiartas-spin-to-win' ),
                        'voucher_quotas'     => array(),
                );

                if ( class_exists( 'Gn_Tsiartas_Spin_To_Win_Admin' ) ) {
                        $defaults = Gn_Tsiartas_Spin_To_Win_Admin::get_default_settings();
                }

                $option_name = defined( 'GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME' ) ? GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME : 'gn_tsiartas_spin_to_win_settings';

                $saved = get_option( $option_name, array() );
                if ( ! is_array( $saved ) ) {
                        $saved = array();
                }

                $this->plugin_settings = wp_parse_args( $saved, $defaults );
                $this->plugin_settings['voucher_quotas'] = $this->prepare_voucher_quotas( isset( $this->plugin_settings['voucher_quotas'] ) ? $this->plugin_settings['voucher_quotas'] : array() );

                return $this->plugin_settings;
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
                $configured_day       = isset( $settings['active_day'] ) ? $settings['active_day'] : '';
                $configured_day_index = $this->get_weekday_index( $configured_day );

                $timestamp = current_time( 'timestamp' );

                if ( null !== $configured_day_index ) {
                        $current_day_value = wp_date( 'w', $timestamp );

                        if ( false === $current_day_value ) {
                                return true;
                        }

                        $current_day_index = (int) $current_day_value;

                        if ( $configured_day_index !== $current_day_index ) {
                                return false;
                        }
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
         * Convert a configured weekday value into an ISO-8601 numeric index.
         *
         * This normalises translated day names saved in the settings so we can reliably
         * compare them with the numeric value returned by wp_date( 'w' ).
         *
         * @since    1.4.8
         *
         * @param    string|int $day Configured weekday value.
         *
         * @return   int|null
         */
        private function get_weekday_index( $day ) {
                if ( 'any' === $day ) {
                        return null;
                }

                if ( is_numeric( $day ) ) {
                        $index = (int) $day;

                        if ( $index >= 0 && $index <= 6 ) {
                                return $index;
                        }
                }

                if ( ! is_string( $day ) || '' === trim( $day ) ) {
                        return null;
                }

                $map = array(
                        'sunday'    => 0,
                        'monday'    => 1,
                        'tuesday'   => 2,
                        'wednesday' => 3,
                        'thursday'  => 4,
                        'friday'    => 5,
                        'saturday'  => 6,
                );

                $normalized = strtolower( trim( $day ) );

                if ( isset( $map[ $normalized ] ) ) {
                        return $map[ $normalized ];
                }

                return null;
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
         * Normalise the prize configuration retrieved from the database.
         *
         * @since    1.0.0
         *
         * @param    mixed $prizes Raw value retrieved from the database.
         *
         * @return   array
         */
        private function normalise_prizes( $prizes ) {
                if ( empty( $prizes ) || ! is_array( $prizes ) ) {
                        return array();
                }

                $normalised = array();
                foreach ( $prizes as $index => $prize ) {
                        if ( ! is_array( $prize ) ) {
                                continue;
                        }

                        $label = isset( $prize['label'] ) ? sanitize_text_field( $prize['label'] ) : '';
                        if ( '' === $label ) {
                                continue;
                        }

                        $colour = '';
                        if ( isset( $prize['colour'] ) ) {
                                $colour = sanitize_text_field( $prize['colour'] );
                        } elseif ( isset( $prize['color'] ) ) {
                                $colour = sanitize_text_field( $prize['color'] );
                        }

                        $normalised[] = array(
                                'id'          => isset( $prize['id'] ) ? sanitize_title( $prize['id'] ) : sanitize_title( $label . '-' . $index ),
                                'label'       => $label,
                                'description' => isset( $prize['description'] ) ? sanitize_textarea_field( $prize['description'] ) : '',
                                'weight'      => isset( $prize['weight'] ) ? (float) $prize['weight'] : 1,
                                'value'       => isset( $prize['value'] ) ? sanitize_text_field( $prize['value'] ) : '',
                                'colour'      => $colour,
                                'color'       => $colour,
                        );
                }

                return $normalised;
        }

        /**
         * Normalise CTA configuration retrieved from the database.
         *
         * @since    1.0.0
         *
         * @param    mixed $ctas Raw value retrieved from the database.
         *
         * @return   array
         */
        private function normalise_ctas( $ctas ) {
                if ( empty( $ctas ) || ! is_array( $ctas ) ) {
                        return $this->get_default_ctas();
                }

                $normalised = array();
                foreach ( $ctas as $cta ) {
                        if ( ! is_array( $cta ) ) {
                                continue;
                        }

                        $label = isset( $cta['label'] ) ? sanitize_text_field( $cta['label'] ) : '';
                        $url   = isset( $cta['url'] ) ? esc_url_raw( $cta['url'] ) : '';

                        if ( '' === $label || '' === $url ) {
                                continue;
                        }

                        $normalised[] = array(
                                'label'  => $label,
                                'url'    => $url,
                                'target' => isset( $cta['target'] ) ? sanitize_text_field( $cta['target'] ) : '_self',
                                'rel'    => isset( $cta['rel'] ) ? sanitize_text_field( $cta['rel'] ) : 'noopener',
                        );
                }

                if ( empty( $normalised ) ) {
                        return $this->get_default_ctas();
                }

                return $normalised;
        }

        /**
         * Handle spin requests sent via AJAX.
         *
         * @since    1.4.0
         *
         * @return   void
         */
        public function handle_spin_request() {
                if ( ! check_ajax_referer( 'gn-tsiartas-spin-to-win', 'nonce', false ) ) {
                        wp_send_json_error(
                                array(
                                        'message'         => __( 'Invalid request. Please refresh the page and try again.', 'gn-tsiartas-spin-to-win' ),
                                        'can_spin_again'  => true,
                                        'code'            => 'invalid_nonce',
                                ),
                                400
                        );
                }

                $settings = $this->get_plugin_settings();

                if ( ! $this->is_within_active_window( $settings ) ) {
                        wp_send_json_error(
                                array(
                                        'message'         => __( 'The Spin & Win promotion is not currently active. Please visit between 07:00 and 20:00 on Friday.', 'gn-tsiartas-spin-to-win' ),
                                        'can_spin_again'  => true,
                                        'code'            => 'inactive_window',
                                ),
                                400
                        );
                }

                $prizes = $this->get_available_prizes();
                if ( empty( $prizes ) ) {
                        wp_send_json_error(
                                array(
                                        'message'         => __( 'No prizes are configured at the moment. Please try again later.', 'gn-tsiartas-spin-to-win' ),
                                        'can_spin_again'  => true,
                                        'code'            => 'no_prizes',
                                ),
                                400
                        );
                }

                $timestamp   = current_time( 'timestamp' );
                $tracking    = $this->get_tracking_data( $timestamp );
                $spin_number = (int) $tracking['spin_count'] + 1;
                $window      = $this->calculate_window_progress( $timestamp, $settings );

                $selection = $this->determine_prize_selection( $spin_number, $prizes, $settings, $tracking, $window );
                if ( is_wp_error( $selection ) ) {
                        $code    = $selection->get_error_code();
                        $message = $selection->get_error_message();
                        wp_send_json_error(
                                array(
                                        'message'         => $message,
                                        'can_spin_again'  => ( 'quotas_depleted' !== $code ),
                                        'code'            => $code,
                                ),
                                400
                        );
                }

                $prize        = $selection['prize'];
                $prize_value  = $selection['value'];
                $selection_type = $selection['type'];

                $tracking['spin_count'] = $spin_number;
                $tracking['spins'][]    = array(
                        'spin'       => $spin_number,
                        'timestamp'  => $timestamp,
                        'prize_id'   => $prize['id'],
                        'prize_label'=> isset( $prize['label'] ) ? $prize['label'] : '',
                        'prize_value'=> $prize_value,
                        'type'       => $selection_type,
                        'special'    => isset( $selection['special'] ) ? $selection['special'] : '',
                );

                if ( 'voucher' === $selection_type && null !== $prize_value ) {
                        $usage_key = (string) $prize_value;
                        if ( ! isset( $tracking['usage'][ $usage_key ] ) ) {
                                $tracking['usage'][ $usage_key ] = 0;
                        }
                        $tracking['usage'][ $usage_key ]++;
                }

                $rotation_key = (string) $selection['rotation_key'];
                if ( ! isset( $tracking['rotation'][ $rotation_key ] ) ) {
                        $tracking['rotation'][ $rotation_key ] = 0;
                }
                $tracking['rotation'][ $rotation_key ]++;

                $this->save_tracking_data( $tracking );

                $response = array(
                        'prize_id'     => $prize['id'],
                        'prize_label'  => isset( $prize['label'] ) ? $prize['label'] : '',
                        'prize_value'  => $prize_value,
                        'spin_number'  => $spin_number,
                        'timestamp'    => wp_date( DATE_ATOM, $timestamp ),
                        'type'         => $selection_type,
                        'special_spin' => isset( $selection['special'] ) ? $selection['special'] : '',
                        'quota_usage'  => $this->format_quota_usage_response( $tracking['usage'], $settings['voucher_quotas'] ),
                );

                wp_send_json_success( $response );
        }

        /**
         * Retrieve the prizes available for selection.
         *
         * @since    1.4.0
         *
         * @return   array
         */
        private function get_available_prizes() {
                $prizes_option = get_option( 'gn_tsiartas_spin_to_win_prizes', array() );
                $prizes        = $this->normalise_prizes( $prizes_option );

                if ( empty( $prizes ) ) {
                        $prizes = $this->get_default_prizes();
                }

                return $prizes;
        }

        /**
         * Normalise voucher quota configuration.
         *
         * @since    1.4.0
         *
         * @param    mixed $value Raw quota values.
         *
         * @return   array
         */
        private function prepare_voucher_quotas( $value ) {
                if ( class_exists( 'Gn_Tsiartas_Spin_To_Win_Admin' ) ) {
                        $defaults = Gn_Tsiartas_Spin_To_Win_Admin::get_default_voucher_quotas();
                } else {
                        $defaults = array(
                                '5'   => 0,
                                '10'  => 0,
                                '15'  => 0,
                                '50'  => 1,
                                '100' => 1,
                        );
                }

                if ( ! is_array( $value ) ) {
                        $value = array();
                }

                foreach ( $defaults as $key => $default ) {
                        $value[ $key ] = isset( $value[ $key ] ) ? max( 0, (int) $value[ $key ] ) : $default;
                }

                return $value;
        }

        /**
         * Retrieve the name of the option used to persist spin tracking data.
         *
         * @since    1.4.0
         *
         * @return   string
         */
        private function get_tracking_option_name() {
                return 'gn_tsiartas_spin_to_win_tracking';
        }

        /**
         * Retrieve the tracking data for the current promotional week.
         *
         * @since    1.4.0
         *
         * @param    int $timestamp Current timestamp.
         *
         * @return   array
         */
        private function get_tracking_data( $timestamp ) {
                $option_name = $this->get_tracking_option_name();
                $data        = get_option( $option_name, array() );

                if ( ! is_array( $data ) ) {
                        $data = array();
                }

                $week_key = $this->get_current_week_key( $timestamp );

                if ( ! isset( $data['week_key'] ) || $data['week_key'] !== $week_key ) {
                        $data = $this->get_empty_tracking_template( $week_key );
                        update_option( $option_name, $data );

                        return $data;
                }

                $data = wp_parse_args(
                        $data,
                        array(
                                'week_key'   => $week_key,
                                'spins'      => array(),
                                'usage'      => array(),
                                'rotation'   => array(),
                                'spin_count' => 0,
                        )
                );

                $data['usage']    = $this->prepare_voucher_quotas( isset( $data['usage'] ) ? $data['usage'] : array() );
                $data['rotation'] = isset( $data['rotation'] ) && is_array( $data['rotation'] ) ? $data['rotation'] : array();
                $data['spin_count'] = max( (int) $data['spin_count'], count( $data['spins'] ) );

                return $data;
        }

        /**
         * Persist tracking data to the options table.
         *
         * @since    1.4.0
         *
         * @param    array $data Tracking data to save.
         *
         * @return   void
         */
        private function save_tracking_data( $data ) {
                update_option( $this->get_tracking_option_name(), $data );
        }

        /**
         * Generate a unique key for the current promotional week.
         *
         * @since    1.4.0
         *
         * @param    int|null $timestamp Reference timestamp.
         *
         * @return   string
         */
        private function get_current_week_key( $timestamp = null ) {
                if ( null === $timestamp ) {
                        $timestamp = current_time( 'timestamp' );
                }

                return wp_date( 'oW', $timestamp );
        }

        /**
         * Provide an empty tracking structure for a new promotional week.
         *
         * @since    1.4.0
         *
         * @param    string $week_key Week identifier.
         *
         * @return   array
         */
        private function get_empty_tracking_template( $week_key ) {
                return array(
                        'week_key'   => $week_key,
                        'spins'      => array(),
                        'usage'      => $this->prepare_voucher_quotas( array() ),
                        'rotation'   => array(),
                        'spin_count' => 0,
                );
        }

        /**
         * Calculate progress through the configured active window.
         *
         * @since    1.4.0
         *
         * @param    int   $timestamp Current timestamp.
         * @param    array $settings  Plugin settings.
         *
         * @return   array
         */
        private function calculate_window_progress( $timestamp, $settings ) {
                $timezone   = wp_timezone();
                $start_time = isset( $settings['active_start_time'] ) ? $settings['active_start_time'] : '07:00';
                $end_time   = isset( $settings['active_end_time'] ) ? $settings['active_end_time'] : '20:00';

                $date      = wp_date( 'Y-m-d', $timestamp, $timezone );
                $start_dt  = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $start_time, $timezone );
                $end_dt    = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $end_time, $timezone );

                if ( ! $start_dt ) {
                        $start_dt = new DateTimeImmutable( '@' . $timestamp );
                        $start_dt = $start_dt->setTimezone( $timezone );
                }

                if ( ! $end_dt ) {
                        $end_dt = $start_dt->modify( '+13 hours' );
                }

                if ( $end_dt <= $start_dt ) {
                        $end_dt = $end_dt->modify( '+1 day' );
                }

                $start_ts = $start_dt->getTimestamp();
                $end_ts   = $end_dt->getTimestamp();
                $total    = max( 1, $end_ts - $start_ts );

                if ( $timestamp <= $start_ts ) {
                        $elapsed = 0;
                } elseif ( $timestamp >= $end_ts ) {
                        $elapsed = $total;
                } else {
                        $elapsed = $timestamp - $start_ts;
                }

                $ratio = $total > 0 ? min( 1, max( 0, $elapsed / $total ) ) : 1;

                return array(
                        'start'         => $start_ts,
                        'end'           => $end_ts,
                        'total'         => $total,
                        'elapsed'       => $elapsed,
                        'elapsed_ratio' => $ratio,
                );
        }

        /**
         * Determine which prize should be awarded for the current spin.
         *
         * @since    1.4.0
         *
         * @param    int   $spin_number Spin sequence number.
         * @param    array $prizes      Available prizes.
         * @param    array $settings    Plugin settings.
         * @param    array $tracking    Current tracking information.
         * @param    array $window      Active window progress information.
         *
         * @return   array|WP_Error
         */
        private function determine_prize_selection( $spin_number, $prizes, $settings, $tracking, $window ) {
                $quotas   = $this->prepare_voucher_quotas( isset( $settings['voucher_quotas'] ) ? $settings['voucher_quotas'] : array() );
                $usage    = isset( $tracking['usage'] ) ? $this->prepare_voucher_quotas( $tracking['usage'] ) : $this->prepare_voucher_quotas( array() );
                $rotation = isset( $tracking['rotation'] ) && is_array( $tracking['rotation'] ) ? $tracking['rotation'] : array();

                $categories = $this->categorize_prizes( $prizes );
                $by_value   = isset( $categories['by_value'] ) ? $categories['by_value'] : array();
                $try_again  = isset( $categories['try_again'] ) ? $categories['try_again'] : array();

                if ( 50 === $spin_number ) {
                        $special = $this->select_special_prize( '50', 'spin-50', $by_value, $quotas, $usage, $rotation );
                        if ( $special ) {
                                return $special;
                        }
                }

                if ( 100 === $spin_number ) {
                        $special = $this->select_special_prize( '100', 'spin-100', $by_value, $quotas, $usage, $rotation );
                        if ( $special ) {
                                return $special;
                        }
                }

                $ratio    = isset( $window['elapsed_ratio'] ) ? (float) $window['elapsed_ratio'] : 1.0;
                $eligible = array();

                foreach ( array( '15', '10', '5' ) as $value_key ) {
                        $quota = isset( $quotas[ $value_key ] ) ? (int) $quotas[ $value_key ] : 0;
                        $used  = isset( $usage[ $value_key ] ) ? (int) $usage[ $value_key ] : 0;

                        if ( $quota <= 0 || $used >= $quota ) {
                                continue;
                        }

                        if ( empty( $by_value[ $value_key ] ) ) {
                                continue;
                        }

                        if ( ! $this->can_award_value_now( $quota, $used, $ratio ) ) {
                                continue;
                        }

                        $eligible[ $value_key ] = max( 1, $quota - $used );
                }

                if ( ! empty( $eligible ) ) {
                        $selected = (string) $this->pick_weighted_value( $eligible );
                        if ( isset( $by_value[ $selected ] ) ) {
                                $prize = $this->select_prize_from_group( $selected, $by_value[ $selected ], $rotation );
                                if ( $prize ) {
                                        return array(
                                                'prize'        => $prize,
                                                'value'        => (int) $selected,
                                                'type'         => 'voucher',
                                                'rotation_key' => $selected,
                                        );
                                }
                        }
                }

                if ( ! empty( $try_again ) ) {
                        $prize = $this->select_prize_from_group( 'try_again', $try_again, $rotation );
                        if ( $prize ) {
                                return array(
                                        'prize'        => $prize,
                                        'value'        => null,
                                        'type'         => 'try_again',
                                        'rotation_key' => 'try_again',
                                );
                        }
                }

                return new WP_Error( 'quotas_depleted', __( 'All vouchers have been awarded for today. Please visit us next Friday!', 'gn-tsiartas-spin-to-win' ) );
        }

        /**
         * Select a special prize if a quota is still available.
         *
         * @since    1.4.0
         *
         * @param    string $value_key   Prize value key.
         * @param    string $label       Special selection label.
         * @param    array  $by_value    Categorised prizes by value.
         * @param    array  $quotas      Configured quotas.
         * @param    array  $usage       Current usage counts.
         * @param    array  $rotation    Rotation counters.
         *
         * @return   array|null
         */
        private function select_special_prize( $value_key, $label, $by_value, $quotas, $usage, $rotation ) {
                $quota = isset( $quotas[ $value_key ] ) ? (int) $quotas[ $value_key ] : 0;
                $used  = isset( $usage[ $value_key ] ) ? (int) $usage[ $value_key ] : 0;

                if ( $quota <= 0 || $used >= $quota ) {
                        return null;
                }

                if ( empty( $by_value[ $value_key ] ) ) {
                        return null;
                }

                $prize = $this->select_prize_from_group( $value_key, $by_value[ $value_key ], $rotation );
                if ( ! $prize ) {
                        return null;
                }

                return array(
                        'prize'        => $prize,
                        'value'        => (int) $value_key,
                        'type'         => 'voucher',
                        'rotation_key' => $value_key,
                        'special'      => $label,
                );
        }

        /**
         * Group prizes by their monetary value and detect try-again options.
         *
         * @since    1.4.0
         *
         * @param    array $prizes Prizes to categorise.
         *
         * @return   array
         */
        private function categorize_prizes( $prizes ) {
                $categories = array(
                        'by_value'  => array(),
                        'try_again' => array(),
                );

                foreach ( $prizes as $prize ) {
                        $value = $this->extract_prize_value( $prize );

                        if ( null !== $value ) {
                                $key = (string) $value;
                                if ( ! isset( $categories['by_value'][ $key ] ) ) {
                                        $categories['by_value'][ $key ] = array();
                                }
                                $categories['by_value'][ $key ][] = $prize;
                                continue;
                        }

                        if ( $this->is_try_again_prize( $prize ) ) {
                                $categories['try_again'][] = $prize;
                                continue;
                        }
                }

                return $categories;
        }

        /**
         * Attempt to extract a numeric voucher value from a prize definition.
         *
         * @since    1.4.0
         *
         * @param    array $prize Prize definition.
         *
         * @return   int|null
         */
        private function extract_prize_value( $prize ) {
                $fields = array();

                foreach ( array( 'value', 'label', 'description', 'id' ) as $key ) {
                        if ( isset( $prize[ $key ] ) ) {
                                $fields[] = $prize[ $key ];
                        }
                }

                foreach ( $fields as $field ) {
                        if ( ! is_string( $field ) ) {
                                continue;
                        }

                        if ( preg_match( '/€\s*(\d+)/u', $field, $matches ) || preg_match( '/(\d+)\s*€/u', $field, $matches ) ) {
                                return (int) $matches[1];
                        }
                }

                return null;
        }

        /**
         * Determine whether a prize represents a try-again outcome.
         *
         * @since    1.4.0
         *
         * @param    array $prize Prize definition.
         *
         * @return   bool
         */
        private function is_try_again_prize( $prize ) {
                $haystack = strtolower( implode( ' ', array(
                        isset( $prize['id'] ) ? $prize['id'] : '',
                        isset( $prize['label'] ) ? $prize['label'] : '',
                        isset( $prize['description'] ) ? $prize['description'] : '',
                ) ) );

                $keywords = array( 'try again', 'try-again', 'better luck', 'no prize', 'δοκιμάστε', 'ξανά' );

                foreach ( $keywords as $keyword ) {
                        if ( false !== strpos( $haystack, $keyword ) ) {
                                return true;
                        }
                }

                return false;
        }

        /**
         * Determine if a voucher can be awarded based on pacing restrictions.
         *
         * @since    1.4.0
         *
         * @param    int   $quota Total vouchers available.
         * @param    int   $used  Vouchers already awarded.
         * @param    float $ratio Portion of the active window elapsed.
         *
         * @return   bool
         */
        private function can_award_value_now( $quota, $used, $ratio ) {
                if ( $quota <= 0 || $used >= $quota ) {
                        return false;
                }

                $ratio   = min( 1, max( 0, (float) $ratio ) );
                $allowed = min( $quota, max( 1, (int) floor( $quota * $ratio ) + 1 ) );

                return $used < $allowed;
        }

        /**
         * Select a value using weighted randomness.
         *
         * @since    1.4.0
         *
         * @param    array $weights Associative array of weights keyed by value.
         *
         * @return   int
         */
        private function pick_weighted_value( $weights ) {
                $total = 0;

                foreach ( $weights as $weight ) {
                        $total += max( 0, (int) $weight );
                }

                if ( $total <= 0 ) {
                        $keys = array_keys( $weights );
                        return (int) array_shift( $keys );
                }

                $target = random_int( 1, $total );
                $running = 0;

                foreach ( $weights as $value => $weight ) {
                        $running += max( 0, (int) $weight );
                        if ( $target <= $running ) {
                                return (int) $value;
                        }
                }

                $keys = array_keys( $weights );
                return (int) array_pop( $keys );
        }

        /**
         * Select a prize from a group using round-robin rotation.
         *
         * @since    1.4.0
         *
         * @param    string $rotation_key Rotation identifier.
         * @param    array  $group        Prizes in the group.
         * @param    array  $rotation     Rotation counters.
         *
         * @return   array|null
         */
        private function select_prize_from_group( $rotation_key, $group, $rotation ) {
                if ( empty( $group ) || ! is_array( $group ) ) {
                        return null;
                }

                $index = isset( $rotation[ $rotation_key ] ) ? (int) $rotation[ $rotation_key ] : 0;
                $count = count( $group );

                return $group[ $index % $count ];
        }

        /**
         * Prepare quota usage data for API responses.
         *
         * @since    1.4.0
         *
         * @param    array $usage  Recorded usage counts.
         * @param    array $quotas Configured quotas.
         *
         * @return   array
         */
        private function format_quota_usage_response( $usage, $quotas ) {
                $quotas = $this->prepare_voucher_quotas( $quotas );
                $usage  = $this->prepare_voucher_quotas( is_array( $usage ) ? $usage : array() );

                $response = array();

                foreach ( $quotas as $key => $total ) {
                        $used = isset( $usage[ $key ] ) ? (int) $usage[ $key ] : 0;
                        $response[ $key ] = array(
                                'total'     => (int) $total,
                                'used'      => min( (int) $total, $used ),
                                'remaining' => max( 0, (int) $total - $used ),
                        );
                }

                return $response;
        }

        /**
         * Provide default CTA buttons when none are configured.
         *
         * @since    1.0.0
         *
         * @return   array
         */
        private function get_default_ctas() {
                return array(
                        array(
                                'label'  => __( 'Visit the supermarket', 'gn-tsiartas-spin-to-win' ),
                                'url'    => home_url( '/' ),
                                'target' => '_self',
                                'rel'    => 'noopener',
                        ),
                );
        }

        /**
         * Provide default prize configuration.
         *
         * @since    1.0.0
         *
         * @return   array
         */
        private function get_default_prizes() {
                return array(
                        array(
                                'id'          => 'voucher-5-a',
                                'label'       => __( '€5', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability reward.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#f94144',
                                'color'       => '#f94144',
                        ),
                        array(
                                'id'          => 'voucher-10-a',
                                'label'       => __( '€10', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability reward.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#f3722c',
                                'color'       => '#f3722c',
                        ),
                        array(
                                'id'          => 'voucher-15-a',
                                'label'       => __( '€15', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability reward.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#f8961e',
                                'color'       => '#f8961e',
                        ),
                        array(
                                'id'          => 'voucher-50',
                                'label'       => __( '€50', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Awarded approximately every 50 spins.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#f9c74f',
                                'color'       => '#f9c74f',
                        ),
                        array(
                                'id'          => 'voucher-100',
                                'label'       => __( '€100', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Awarded approximately every 100 spins.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#90be6d',
                                'color'       => '#90be6d',
                        ),
                        array(
                                'id'          => 'voucher-5-b',
                                'label'       => __( '€5', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability reward.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#577590',
                                'color'       => '#577590',
                        ),
                        array(
                                'id'          => 'voucher-10-b',
                                'label'       => __( '€10', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability reward.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#277da1',
                                'color'       => '#277da1',
                        ),
                        array(
                                'id'          => 'voucher-15-b',
                                'label'       => __( '€15', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability reward.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#9b5de5',
                                'color'       => '#9b5de5',
                        ),
                );
        }

        /**
         * Default user-facing messages.
         *
         * @since    1.0.0
         *
         * @return   array
         */
        private function get_default_messages() {
                return array(
                        'prompt' => __( 'Spin the wheel for a chance to win exclusive rewards!', 'gn-tsiartas-spin-to-win' ),
                        'win'    => __( 'Congratulations! You won %s.', 'gn-tsiartas-spin-to-win' ),
                        'lose'   => __( 'Thanks for playing! Try again soon.', 'gn-tsiartas-spin-to-win' ),
                );
        }

}
