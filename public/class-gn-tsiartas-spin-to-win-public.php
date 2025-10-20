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
         * Absolute path to the directory inside uploads where public assets are stored.
         *
         * @since    2.3.14
         * @var      string
         */
        private $assets_upload_dir = '';

        /**
         * Base URL to the directory inside uploads where public assets are stored.
         *
         * @since    2.3.14
         * @var      string
         */
        private $assets_upload_url = '';

        /**
         * Option name tracking the version of the copied public assets.
         *
         * @since    2.3.14
         * @var      string
         */
        private $assets_version_option = 'gn_tsiartas_spin_to_win_assets_version';

        /**
         * Cached copy of plugin-level settings.
         *
         * @since    1.3.3
         * @access   private
         * @var      array|null
         */
        private $plugin_settings = null;

        /**
         * Option name storing tracking data for Friday spins.
         *
         * @since    2.2.0
         * @var      string
         */
        private $tracking_option_name = 'gn_tsiartas_spin_to_win_friday_tracking';

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
         * Ensure the public-facing image assets are available from the uploads directory.
         *
         * When users are not logged in some environments restrict direct access to plugin
         * files. Mirroring the images to the uploads directory allows them to remain
         * accessible regardless of authentication requirements.
         *
         * @since    2.3.14
         * @return   void
         */
        public function ensure_public_assets() {

		$this->assets_upload_dir = '';
		$this->assets_upload_url = '';

		if ( ! function_exists( 'wp_upload_dir' ) ) {
			return;
		}

		$uploads = wp_upload_dir();
		if ( ! is_array( $uploads ) || ! empty( $uploads['error'] ) ) {
			return;
		}

		$upload_dir = trailingslashit( $uploads['basedir'] );
		$upload_url = trailingslashit( $uploads['baseurl'] );

		$assets_dir = trailingslashit( $upload_dir . $this->plugin_name );
		$assets_url = trailingslashit( $upload_url . $this->plugin_name );

		if ( ! is_dir( $assets_dir ) && ! wp_mkdir_p( $assets_dir ) ) {
			return;
		}

		$this->assets_upload_dir = $assets_dir;
		$this->assets_upload_url = $assets_url;

		$source_dir = trailingslashit( plugin_dir_path( __FILE__ ) . 'images' );
		if ( ! is_dir( $source_dir ) ) {
			return;
		}

		$stored_version = get_option( $this->assets_version_option );
		$should_sync    = (string) $stored_version !== (string) $this->version;
		$entries        = scandir( $source_dir );
		if ( ! is_array( $entries ) ) {
			return;
		}

		$copied     = false;
		$has_images = false;
		foreach ( $entries as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$source_file = $source_dir . $entry;
			if ( ! is_file( $source_file ) || ! $this->is_image_file( $source_file ) ) {
				continue;
			}

			$has_images = true;

			$destination = $assets_dir . $entry;

			if ( ! $should_sync && file_exists( $destination ) ) {
				continue;
			}

			$contents = file_get_contents( $source_file );
			if ( false === $contents ) {
				continue;
			}

			$bytes_written = file_put_contents( $destination, $contents );
			if ( false === $bytes_written ) {
				continue;
			}

			$copied = true;
		}

		if ( $copied || ( $should_sync && ! $has_images ) ) {
			update_option( $this->assets_version_option, $this->version );
		}
	}


	/**
	 * Retrieve the URL for an uploaded asset if it exists.
	 *
	 * @since    2.3.14
	 *
	 * @param    string $filename File name within the uploads mirror directory.
	 * @return   string
	 */
        private function get_uploaded_asset_url( $filename ) {
                if ( empty( $filename ) ) {
                        return '';
                }

                if ( empty( $this->assets_upload_dir ) || empty( $this->assets_upload_url ) ) {
                        $this->ensure_public_assets();
                }

                if ( empty( $this->assets_upload_dir ) || empty( $this->assets_upload_url ) ) {
                        return '';
                }

                $destination = $this->assets_upload_dir . $filename;

                if ( file_exists( $destination ) ) {
                        return $this->assets_upload_url . $filename;
                }

                $source_dir  = trailingslashit( plugin_dir_path( __FILE__ ) . 'images' );
                $source_file = $source_dir . $filename;

                if ( ! file_exists( $source_file ) || ! $this->is_image_file( $source_file ) ) {
                        return '';
                }

                if ( ! is_dir( $this->assets_upload_dir ) ) {
                        if ( ! function_exists( 'wp_mkdir_p' ) || ! wp_mkdir_p( $this->assets_upload_dir ) ) {
                                return '';
                        }
                }

                if ( copy( $source_file, $destination ) ) {
                        return $this->assets_upload_url . $filename;
                }

                return '';
        }

	/**
	 * Determine whether the provided path points to a supported image file.
	 *
	 * @since    2.3.14
	 *
	 * @param    string $path Absolute file path.
	 * @return   bool
	 */
        private function is_image_file( $path ) {
		if ( empty( $path ) ) {
			return false;
		}

		$extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		if ( '' === $extension ) {
			return false;
		}

		$allowed_extensions = array( 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp' );

		return in_array( $extension, $allowed_extensions, true );
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

                $global_settings                  = $this->get_global_settings( $settings );
                $this->localized_data['settings'] = $global_settings;
                $formatted_date                  = isset( $global_settings['formattedDate'] ) ? $global_settings['formattedDate'] : $this->get_formatted_store_date();

                // Ensure public assets are present and data is localized for the script.
                wp_enqueue_style( $this->plugin_name );
                wp_enqueue_script( $this->plugin_name );
                wp_localize_script( $this->plugin_name, 'gnTsiartasSpinToWinConfig', $this->localized_data );

                $show_cta = filter_var( $atts['show_cta'], FILTER_VALIDATE_BOOLEAN );
                $prizes   = isset( $configuration['prizes'] ) ? $configuration['prizes'] : array();
                $messages = isset( $configuration['messages'] ) ? $configuration['messages'] : array();
		$wheel_logo_url = $this->get_uploaded_asset_url( 'TSIARTAS-logo-transparent.png' );
		if ( '' === $wheel_logo_url ) {
			$wheel_logo_url = plugins_url( 'public/images/TSIARTAS-logo-transparent.png', GN_TSIARTAS_SPIN_TO_WIN_PLUGIN_FILE );
		}

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
                                <p class="gn-tsiartas-spin-to-win__current-date" data-role="current-date">
                                        <?php echo esc_html( $formatted_date ); ?>
                                </p>
                                <div class="gn-tsiartas-spin-to-win__wheel" data-role="wheel" aria-live="polite">
                                        <img
                                                class="gn-tsiartas-spin-to-win__wheel-logo"
                                                src="<?php echo esc_url( $wheel_logo_url ); ?>"
                                                alt="<?php echo esc_attr__( 'Tsiartas Supermarket logo', 'gn-tsiartas-spin-to-win' ); ?>"
                                                data-role="wheel-hub"
                                        />
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
                                        <p class="gn-tsiartas-spin-to-win__modal-date" data-role="modal-date"></p>
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
                $prizes = $this->get_prize_pool();

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
                        'prizes'     => $prizes,
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
         * Retrieve the configured prize pool.
         *
         * @since    2.2.0
         *
         * @return   array
         */
        private function get_prize_pool() {
                $prizes_option = get_option( 'gn_tsiartas_spin_to_win_prizes', array() );
                $prizes        = $this->normalise_prizes( $prizes_option );

                if ( empty( $prizes ) ) {
                        return $this->get_default_prizes();
                }

                return $prizes;
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

                $spin_duration = isset( $settings['spin_duration'] ) ? (int) $settings['spin_duration'] : 4600;
                $quotas        = $this->get_friday_quotas( $settings );
                $store_hours   = $this->get_active_window_hours( $settings );

                return array(
                        'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                        'nonce'         => wp_create_nonce( 'gn-tsiartas-spin-to-win' ),
                        'pluginUrl'     => plugin_dir_url( __FILE__ ),
                        'spinDuration'  => $spin_duration,
                        'activeWindow'  => array(
                                'day'   => isset( $settings['active_day'] ) ? $settings['active_day'] : '',
                                'start' => isset( $settings['active_start_time'] ) ? $settings['active_start_time'] : '',
                                'end'   => isset( $settings['active_end_time'] ) ? $settings['active_end_time'] : '',
                        ),
                        'cashierNotice' => isset( $settings['cashier_notice'] ) ? $settings['cashier_notice'] : '',
                        'quotas'        => $quotas,
                        'storeHours'    => $store_hours,
                        'formattedDate' => $this->get_formatted_store_date(),
                );
        }

        /**
         * Retrieve the formatted date string for the current store day.
         *
         * @since    2.2.1
         *
         * @param    int|null $timestamp Optional timestamp override.
         *
         * @return   string
         */
        private function get_formatted_store_date( $timestamp = null ) {
                if ( null === $timestamp ) {
                        $timestamp = current_time( 'timestamp', true );
                }

                $date_format = get_option( 'date_format' );
                if ( empty( $date_format ) ) {
                        $date_format = 'l, F j, Y';
                }

                $time_format = get_option( 'time_format' );
                if ( empty( $time_format ) ) {
                        $time_format = 'H:i';
                }

                $format = trim( $date_format . ' ' . $time_format );
                if ( empty( $format ) ) {
                        $format = 'l, F j, Y H:i';
                }

                $formatted = wp_date( $format, $timestamp );

                if ( false === $formatted ) {
                        $formatted = wp_date( 'l, F j, Y H:i', $timestamp );
                }

                return $formatted;
        }

        /**
         * AJAX handler that assigns prizes server-side.
         *
         * @since    2.2.0
         * @return   void
         */
        public function handle_spin_request() {
                check_ajax_referer( 'gn-tsiartas-spin-to-win', 'nonce' );

                $settings = $this->get_plugin_settings();

		if ( ! $this->is_within_active_window( $settings ) ) {
			wp_send_json_error(
				array(
					'code'    => 'inactive_window',
					'message' => $this->get_inactive_window_message( $settings ),
				),
				403
			);
		}

                $timestamp = current_time( 'timestamp', true );
                $quotas    = $this->get_friday_quotas( $settings );

                if ( empty( array_filter( $quotas ) ) ) {
                        wp_send_json_error(
                                array(
                                        'code'    => 'quotas_not_configured',
                                        'message' => __( 'Voucher quotas have not been configured for this promotion.', 'gn-tsiartas-spin-to-win' ),
                                ),
                                500
                        );
                }

                $tracking = $this->get_tracking_state( $timestamp );
                $prizes   = $this->get_prize_pool();

                $spin_number = (int) $tracking['total_spins'] + 1;

                $prize = $this->determine_prize_for_spin( $spin_number, $quotas, $tracking, $timestamp, $prizes );

                if ( is_wp_error( $prize ) ) {
                        $error_data = $prize->get_error_data();
                        $status     = isset( $error_data['status'] ) ? (int) $error_data['status'] : 400;
                        $payload    = array(
                                'code'    => $prize->get_error_code(),
                                'message' => $prize->get_error_message(),
                        );

                        if ( isset( $error_data['data'] ) && is_array( $error_data['data'] ) ) {
                                $payload = array_merge( $payload, $error_data['data'] );
                        }

                        wp_send_json_error( $payload, $status );
                }

                $tracking['total_spins'] = $spin_number;
                $this->update_tracking_totals( $tracking, $prize );
                $this->append_tracking_log( $tracking, $prize, $timestamp, $spin_number );
                $this->save_tracking_state( $tracking );

                $remaining = $this->build_remaining_quota_summary( $quotas, $tracking['totals'] );

                wp_send_json_success(
                        array(
                                'spinNumber'       => $spin_number,
                                'prizeId'          => $prize['id'],
                                'label'            => isset( $prize['label'] ) ? $prize['label'] : '',
                                'description'      => isset( $prize['description'] ) ? $prize['description'] : '',
                                'value'            => isset( $prize['value'] ) ? $prize['value'] : null,
                                'isVoucher'        => ! empty( $prize['is_voucher'] ),
                                'timestamp'        => $timestamp,
                                'remainingQuotas'  => $remaining,
                                'awardedDenomination' => isset( $prize['denomination'] ) ? $prize['denomination'] : null,
                                'formattedDate'    => $this->get_formatted_store_date( $timestamp ),
                        )
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

                if ( class_exists( 'Gn_Tsiartas_Spin_To_Win_Admin' ) ) {
                        $defaults = Gn_Tsiartas_Spin_To_Win_Admin::get_default_settings();
                }

                $option_name = defined( 'GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME' ) ? GN_TSIARTAS_SPIN_TO_WIN_OPTION_NAME : 'gn_tsiartas_spin_to_win_settings';

                $saved = get_option( $option_name, array() );
                if ( ! is_array( $saved ) ) {
                        $saved = array();
                }

                $this->plugin_settings = wp_parse_args( $saved, $defaults );

                return $this->plugin_settings;
        }

        /**
         * Retrieve the configured Friday voucher quotas.
         *
         * @since    2.2.0
         *
         * @param    array|null $settings Optional settings array.
         *
         * @return   array
         */
        private function get_friday_quotas( $settings = null ) {
                if ( null === $settings ) {
                        $settings = $this->get_plugin_settings();
                }

                $defaults = array(
                        '5'   => 0,
                        '10'  => 0,
                        '50'  => 1,
                        '100' => 1,
                );

                $configured = isset( $settings['friday_quotas'] ) && is_array( $settings['friday_quotas'] ) ? $settings['friday_quotas'] : array();
                $quotas     = array();

                foreach ( $defaults as $denomination => $default ) {
                        $value = isset( $configured[ $denomination ] ) ? $configured[ $denomination ] : $default;
                        $value = max( 0, (int) $value );

                        if ( in_array( $denomination, array( '50', '100' ), true ) ) {
                                $value = max( 1, $value );
                        }

                        $quotas[ $denomination ] = $value;
                }

                return $quotas;
        }

        /**
         * Retrieve the store opening window for the promotion.
         *
         * @since    2.2.0
         *
         * @return   array
         */
        private function get_active_window_hours( $settings = null ) {
                if ( null === $settings ) {
                        $settings = $this->get_plugin_settings();
                }

                $start = $this->normalise_time_setting( isset( $settings['active_start_time'] ) ? $settings['active_start_time'] : '' );
                $end   = $this->normalise_time_setting( isset( $settings['active_end_time'] ) ? $settings['active_end_time'] : '' );

                if ( '' === $start || '' === $end ) {
                        return array(
                                'start' => '07:00',
                                'end'   => '20:00',
                        );
                }

                return array(
                        'start' => $start,
                        'end'   => $end,
                );
        }

        /**
         * Retrieve and normalise the weekly tracking state for Friday spins.
         *
         * @since    2.2.0
         *
         * @param    int $timestamp Current timestamp.
         *
         * @return   array
         */
        private function get_tracking_state( $timestamp ) {
                $state = get_option( $this->tracking_option_name, array() );
                if ( ! is_array( $state ) ) {
                        $state = array();
                }

                $current_week = $this->get_current_week_key( $timestamp );

                if ( ! isset( $state['week_key'] ) || $state['week_key'] !== $current_week ) {
                        $state = $this->get_default_tracking_state( $current_week, $timestamp );
                }

                if ( ! isset( $state['totals'] ) || ! is_array( $state['totals'] ) ) {
                        $state['totals'] = array();
                }

                $totals_defaults = array(
                        '5'         => 0,
                        '10'        => 0,
                        '50'        => 0,
                        '100'       => 0,
                        'try-again' => 0,
                );

                $state['totals'] = wp_parse_args( $state['totals'], $totals_defaults );

                if ( ! isset( $state['spins'] ) || ! is_array( $state['spins'] ) ) {
                        $state['spins'] = array();
                }

                if ( ! isset( $state['total_spins'] ) ) {
                        $state['total_spins'] = 0;
                }

                return $state;
        }

        /**
         * Persist the tracking state option.
         *
         * @since    2.2.0
         *
         * @param    array $state Tracking state to save.
         *
         * @return   void
         */
        private function save_tracking_state( $state ) {
                update_option( $this->tracking_option_name, $state, false );
        }

        /**
         * Provide the default tracking structure for a new week.
         *
         * @since    2.2.0
         *
         * @param    string $week_key  Week identifier.
         * @param    int    $timestamp Current timestamp.
         *
         * @return   array
         */
        private function get_default_tracking_state( $week_key, $timestamp ) {
                return array(
                        'week_key'    => $week_key,
                        'total_spins' => 0,
                        'totals'      => array(
                                '5'         => 0,
                                '10'        => 0,
                                '50'        => 0,
                                '100'       => 0,
                                'try-again' => 0,
                        ),
                        'spins'       => array(),
                        'last_reset'  => $timestamp,
                );
        }

        /**
         * Build a unique week identifier.
         *
         * @since    2.2.0
         *
         * @param    int $timestamp Current timestamp.
         *
         * @return   string
         */
        private function get_current_week_key( $timestamp ) {
                return wp_date( 'o-\WW', $timestamp );
        }

        /**
         * Update aggregated totals following a spin.
         *
         * @since    2.2.0
         *
         * @param    array $tracking Tracking state (passed by reference).
         * @param    array $prize    Awarded prize payload.
         *
         * @return   void
         */
        private function update_tracking_totals( array &$tracking, array $prize ) {
                $key = 'try-again';

                if ( ! empty( $prize['is_voucher'] ) && isset( $prize['denomination'] ) ) {
                        $key = (string) $prize['denomination'];
                }

                if ( ! isset( $tracking['totals'][ $key ] ) ) {
                        $tracking['totals'][ $key ] = 0;
                }

                $tracking['totals'][ $key ]++;
        }

        /**
         * Append a spin log entry to the tracking state.
         *
         * @since    2.2.0
         *
         * @param    array $tracking   Tracking state (passed by reference).
         * @param    array $prize      Awarded prize payload.
         * @param    int   $timestamp  Current timestamp.
         * @param    int   $spin_index Sequential spin index for the week.
         *
         * @return   void
         */
        private function append_tracking_log( array &$tracking, array $prize, $timestamp, $spin_index ) {
                $tracking['spins'][] = array(
                        'spin'         => $spin_index,
                        'timestamp'    => $timestamp,
                        'prize_id'     => isset( $prize['id'] ) ? $prize['id'] : '',
                        'denomination' => isset( $prize['denomination'] ) ? $prize['denomination'] : null,
                        'label'        => isset( $prize['label'] ) ? $prize['label'] : '',
                );

                // Keep the log from growing unbounded.
                if ( count( $tracking['spins'] ) > 250 ) {
                        $tracking['spins'] = array_slice( $tracking['spins'], -250 );
                }
        }

        /**
         * Calculate remaining quotas after a spin.
         *
         * @since    2.2.0
         *
         * @param    array $quotas Configured quotas.
         * @param    array $totals Awarded totals.
         *
         * @return   array
         */
        private function build_remaining_quota_summary( array $quotas, array $totals ) {
                $summary = array();

                foreach ( $quotas as $denomination => $quota ) {
                        $awarded = isset( $totals[ $denomination ] ) ? (int) $totals[ $denomination ] : 0;
                        $summary[ $denomination ] = max( 0, (int) $quota - $awarded );
                }

                return $summary;
        }

        /**
         * Determine the appropriate prize for the current spin.
         *
         * @since    2.2.0
         *
         * @param    int   $spin_number Sequential spin index.
         * @param    array $quotas      Configured quotas.
         * @param    array $tracking    Current tracking state.
         * @param    int   $timestamp   Current timestamp.
         * @param    array $prizes      Available prize configuration.
         *
         * @return   array|WP_Error
         */
        private function determine_prize_for_spin( $spin_number, array $quotas, array $tracking, $timestamp, array $prizes ) {
                $prize_map = $this->map_prizes_by_value( $prizes );
                $totals    = isset( $tracking['totals'] ) ? $tracking['totals'] : array();

                $forced_spins = array(
                        50  => '50',
                        100 => '100',
                );

                if ( isset( $forced_spins[ $spin_number ] ) ) {
                        $denomination = $forced_spins[ $spin_number ];
                        $remaining    = isset( $quotas[ $denomination ] ) ? (int) $quotas[ $denomination ] : 0;
                        $awarded      = isset( $totals[ $denomination ] ) ? (int) $totals[ $denomination ] : 0;

                        if ( $remaining <= $awarded ) {
                                return new WP_Error(
                                        'quota_exhausted',
                                        __( 'The guaranteed voucher for this spin has already been awarded.', 'gn-tsiartas-spin-to-win' ),
                                        array(
                                                'status' => 410,
                                                'data'   => array(
                                                        'depleted' => true,
                                                ),
                                        )
                                );
                        }

                        $prize = $this->pick_prize_by_value( $denomination, $prize_map );

                        if ( is_wp_error( $prize ) ) {
                                return $prize;
                        }

                        return $prize;
                }

                $remaining_quota_total = 0;
                foreach ( array( '5', '10' ) as $value ) {
                        $quota   = isset( $quotas[ $value ] ) ? (int) $quotas[ $value ] : 0;
                        $awarded = isset( $totals[ $value ] ) ? (int) $totals[ $value ] : 0;
                        $remaining_quota_total += max( 0, $quota - $awarded );
                }

                if ( $remaining_quota_total <= 0 ) {
                        return $this->handle_quota_depletion( $prize_map );
                }

                $ratio      = $this->calculate_elapsed_ratio( $timestamp );
                $candidates = array();

                foreach ( array( '5', '10' ) as $value ) {
                        $quota   = isset( $quotas[ $value ] ) ? (int) $quotas[ $value ] : 0;
                        $awarded = isset( $totals[ $value ] ) ? (int) $totals[ $value ] : 0;

                        if ( $quota <= 0 || $awarded >= $quota ) {
                                continue;
                        }

                        $allowed = $this->calculate_allowed_awards( $quota, $ratio );

                        if ( $awarded >= $allowed && $allowed < $quota ) {
                                continue;
                        }

                        $remaining = max( 0, $quota - $awarded );
                        $allowed_now = max( 1, $allowed - $awarded );
                        $weight = min( $remaining, $allowed_now );
                        $weight = max( 1, $weight );

                        $candidates[] = array(
                                'value'  => $value,
                                'weight' => $weight,
                        );
                }

                if ( empty( $candidates ) ) {
                        $try_again = $this->pick_try_again_prize( $prize_map );

                        if ( is_wp_error( $try_again ) ) {
                                return $try_again;
                        }

                        return $try_again;
                }

                $total_weight = array_sum( wp_list_pluck( $candidates, 'weight' ) );
                $random       = wp_rand( 1, (int) $total_weight );
                $accumulator  = 0;
                $selected     = null;

                foreach ( $candidates as $candidate ) {
                        $accumulator += (int) $candidate['weight'];
                        if ( $random <= $accumulator ) {
                                $selected = $candidate['value'];
                                break;
                        }
                }

                if ( null === $selected ) {
                        $last_candidate = end( $candidates );
                        $selected       = isset( $last_candidate['value'] ) ? $last_candidate['value'] : $candidates[0]['value'];
                }

                $prize = $this->pick_prize_by_value( $selected, $prize_map );

                if ( is_wp_error( $prize ) ) {
                        return $prize;
                }

                return $prize;
        }

        /**
         * Handle the outcome when quotas are fully exhausted.
         *
         * @since    2.2.0
         *
         * @param    array $prize_map Prize map grouped by value.
         *
         * @return   array|WP_Error
         */
        private function handle_quota_depletion( array $prize_map ) {
                $try_again = $this->pick_try_again_prize( $prize_map );

                if ( is_wp_error( $try_again ) ) {
                        return new WP_Error(
                                'quotas_depleted',
                                __( 'All voucher quotas have been claimed for today.', 'gn-tsiartas-spin-to-win' ),
                                array(
                                        'status' => 410,
                                        'data'   => array(
                                                'depleted' => true,
                                        ),
                                )
                        );
                }

                return $try_again;
        }

        /**
         * Calculate how many vouchers can be released based on elapsed time.
         *
         * @since    2.2.0
         *
         * @param    int   $quota Total quota for the denomination.
         * @param    float $ratio Elapsed ratio between 0 and 1.
         *
         * @return   int
         */
        private function calculate_allowed_awards( $quota, $ratio ) {
                $ratio = max( 0, min( 1, $ratio ) );
                $allowed = (int) floor( $quota * $ratio );

                if ( $quota > 0 ) {
                        $allowed = max( 1, $allowed );
                }

                return min( $quota, $allowed );
        }

        /**
         * Calculate the ratio of elapsed time within the promotion window.
         *
         * @since    2.2.0
         *
         * @param    int $timestamp Current timestamp.
         *
         * @return   float
         */
        private function calculate_elapsed_ratio( $timestamp ) {
                $hours      = $this->get_active_window_hours();
                $start_time = $this->get_window_boundary_timestamp( $timestamp, $hours['start'] );
                $end_time   = $this->get_window_boundary_timestamp( $timestamp, $hours['end'] );

                if ( $timestamp <= $start_time ) {
                        return 0.0;
                }

                if ( $timestamp >= $end_time ) {
                        return 1.0;
                }

                $duration = $end_time - $start_time;

                if ( $duration <= 0 ) {
                        return 1.0;
                }

                return ( $timestamp - $start_time ) / $duration;
        }

        /**
         * Group prizes by voucher denomination.
         *
         * @since    2.2.0
         *
         * @param    array $prizes Prize configuration array.
         *
         * @return   array
         */
        private function map_prizes_by_value( array $prizes ) {
                $map = array(
                        '5'         => array(),
                        '10'        => array(),
                        '50'        => array(),
                        '100'       => array(),
                        'try-again' => array(),
                );

                foreach ( $prizes as $prize ) {
                        $denomination = $this->extract_prize_denomination( $prize );

                        if ( null !== $denomination ) {
                                $key = (string) $denomination;

                                if ( ! isset( $map[ $key ] ) ) {
                                        $map[ $key ] = array();
                                }

                                $prize['denomination'] = $denomination;
                                $prize['value']        = (int) $denomination;
                                $prize['is_voucher']   = true;
                                $map[ $key ][]         = $prize;
                                continue;
                        }

                        $id = isset( $prize['id'] ) ? (string) $prize['id'] : '';

                        if ( '' !== $id && 0 === strpos( $id, 'try-again' ) ) {
                                $prize['is_voucher'] = false;
                                $map['try-again'][]   = $prize;
                        }
                }

                return $map;
        }

        /**
         * Extract a denomination value from a prize configuration.
         *
         * @since    2.2.0
         *
         * @param    array $prize Prize configuration.
         *
         * @return   string|null
         */
        private function extract_prize_denomination( array $prize ) {
                if ( isset( $prize['value'] ) && is_numeric( $prize['value'] ) ) {
                        return (string) absint( $prize['value'] );
                }

                $fields = array( 'label', 'description' );

                foreach ( $fields as $field ) {
                        if ( empty( $prize[ $field ] ) || ! is_string( $prize[ $field ] ) ) {
                                continue;
                        }

                        if ( preg_match( '/€\s*(\d+)/u', $prize[ $field ], $matches ) ) {
                                return (string) absint( $matches[1] );
                        }

                        if ( preg_match( '/(\d+)\s*€/u', $prize[ $field ], $matches ) ) {
                                return (string) absint( $matches[1] );
                        }
                }

                return null;
        }

        /**
         * Select a prize for a given denomination.
         *
         * @since    2.2.0
         *
         * @param    string $value     Denomination value.
         * @param    array  $prize_map Prize map grouped by value.
         *
         * @return   array|WP_Error
         */
        private function pick_prize_by_value( $value, array $prize_map ) {
                if ( empty( $prize_map[ $value ] ) ) {
                        return new WP_Error(
                                'prize_unavailable',
                                __( 'No matching prize configuration was found.', 'gn-tsiartas-spin-to-win' ),
                                array(
                                        'status' => 500,
                                )
                        );
                }

                $pool = $prize_map[ $value ];
                $index = array_rand( $pool );

                return $pool[ $index ];
        }

        /**
         * Select a "try again" style prize.
         *
         * @since    2.2.0
         *
         * @param    array $prize_map Prize map grouped by value.
         *
         * @return   array|WP_Error
         */
        private function pick_try_again_prize( array $prize_map ) {
                if ( empty( $prize_map['try-again'] ) ) {
                        return new WP_Error(
                                'try_again_unavailable',
                                __( 'No fallback outcome is available at the moment.', 'gn-tsiartas-spin-to-win' ),
                                array(
                                        'status' => 500,
                                )
                        );
                }

                $pool = $prize_map['try-again'];
                $index = array_rand( $pool );
                $prize = $pool[ $index ];
                $prize['denomination'] = null;
                $prize['value']        = null;
                $prize['is_voucher']   = false;

                return $prize;
        }

        /**
         * Convert a time string to the relevant timestamp for the current day.
         *
         * @since    2.2.0
         *
         * @param    int    $timestamp Reference timestamp.
         * @param    string $time      Time string (H:i).
         *
         * @return   int
         */
        private function get_window_boundary_timestamp( $timestamp, $time ) {
                try {
                        $timezone = wp_timezone();
                        $date     = new DateTimeImmutable( '@' . $timestamp );
                        $date     = $date->setTimezone( $timezone );

                        if ( false === strpos( $time, ':' ) ) {
                                $time .= ':00';
                        }

                        list( $hours, $minutes ) = array_pad( explode( ':', $time ), 2, '00' );
                        $boundary = $date->setTime( (int) $hours, (int) $minutes, 0 );

                        return $boundary->getTimestamp();
                } catch ( Exception $exception ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
                        // Fall back to the provided timestamp on failure.
                }

                return $timestamp;
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
         * Normalise a stored time setting into H:i format.
         *
         * @since    2.3.5
         *
         * @param    string $time Time string to normalise.
         *
         * @return   string
         */
        private function normalise_time_setting( $time ) {
                if ( empty( $time ) ) {
                        return '';
                }

                $time = trim( (string) $time );

                if ( ! preg_match( '/^(\\d{1,2}):(\\d{2})$/', $time, $matches ) ) {
                        return '';
                }

                $hours   = (int) $matches[1];
                $minutes = (int) $matches[2];

                if ( $hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59 ) {
                        return '';
                }

                return sprintf( '%02d:%02d', $hours, $minutes );
        }

        /**
         * Retrieve a human readable label for the configured active day.
         *
         * @since    2.3.5
         *
         * @param    array $settings Plugin settings.
         *
         * @return   string
         */
        private function get_configured_day_label( $settings ) {
                if ( empty( $settings['active_day'] ) ) {
                        return '';
                }

                $day = strtolower( $settings['active_day'] );

                $labels = array(
                        'monday'    => __( 'Monday', 'gn-tsiartas-spin-to-win' ),
                        'tuesday'   => __( 'Tuesday', 'gn-tsiartas-spin-to-win' ),
                        'wednesday' => __( 'Wednesday', 'gn-tsiartas-spin-to-win' ),
                        'thursday'  => __( 'Thursday', 'gn-tsiartas-spin-to-win' ),
                        'friday'    => __( 'Friday', 'gn-tsiartas-spin-to-win' ),
                        'saturday'  => __( 'Saturday', 'gn-tsiartas-spin-to-win' ),
                        'sunday'    => __( 'Sunday', 'gn-tsiartas-spin-to-win' ),
                );

                if ( isset( $labels[ $day ] ) ) {
                        return $labels[ $day ];
                }

                return ucfirst( $day );
        }

        /**
         * Format a time value using the site's preferred time format.
         *
         * @since    2.3.5
         *
         * @param    string $time Time string in H:i format.
         *
         * @return   string
         */
        private function format_time_for_display( $time ) {
                if ( empty( $time ) ) {
                        return '';
                }

                $timestamp   = current_time( 'timestamp', true );
                $boundary    = $this->get_window_boundary_timestamp( $timestamp, $time );
                $time_format = get_option( 'time_format' );

                if ( empty( $time_format ) ) {
                        $time_format = 'H:i';
                }

                $formatted = wp_date( $time_format, $boundary );

                if ( false === $formatted ) {
                        return $time;
                }

                return $formatted;
        }

        /**
         * Build the message shown when the promotion is inactive.
         *
         * @since    2.3.5
         *
         * @param    array $settings Plugin settings.
         *
         * @return   string
         */
        private function get_inactive_window_message( $settings ) {
                $day_label = $this->get_configured_day_label( $settings );
                $hours     = $this->get_active_window_hours( $settings );
                $start     = $this->format_time_for_display( $hours['start'] );
                $end       = $this->format_time_for_display( $hours['end'] );

                if ( $day_label && $start && $end ) {
                        return sprintf(
                                /* translators: 1: Day name, 2: start time, 3: end time. */
                                __( 'The promotion is only available on %1$s between %2$s and %3$s.', 'gn-tsiartas-spin-to-win' ),
                                $day_label,
                                $start,
                                $end
                        );
                }

                if ( $day_label ) {
                        return sprintf(
                                /* translators: %s: Day name. */
                                __( 'The promotion is only available on %s.', 'gn-tsiartas-spin-to-win' ),
                                $day_label
                        );
                }

                return __( 'The promotion is not currently active.', 'gn-tsiartas-spin-to-win' );
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
                                'id'          => 'try-again-a',
                                'label'       => __( 'Try Again', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability outcome.', 'gn-tsiartas-spin-to-win' ),
                                'icon'        => '✖',
                                'weight'      => 1,
                                'colour'      => '#f3722c',
                                'color'       => '#f3722c',
                        ),
                        array(
                                'id'          => 'voucher-10-a',
                                'label'       => __( '€10', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability reward.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#f8961e',
                                'color'       => '#f8961e',
                        ),
                        array(
                                'id'          => 'try-again-b',
                                'label'       => __( 'Try Again', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability outcome.', 'gn-tsiartas-spin-to-win' ),
                                'icon'        => '✖',
                                'weight'      => 1,
                                'colour'      => '#f9c74f',
                                'color'       => '#f9c74f',
                        ),
                        array(
                                'id'          => 'voucher-50',
                                'label'       => __( '€50', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Awarded approximately every 50 spins.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#90be6d',
                                'color'       => '#90be6d',
                        ),
                        array(
                                'id'          => 'try-again-c',
                                'label'       => __( 'Try Again', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability outcome.', 'gn-tsiartas-spin-to-win' ),
                                'icon'        => '✖',
                                'weight'      => 1,
                                'colour'      => '#43aa8b',
                                'color'       => '#43aa8b',
                        ),
                        array(
                                'id'          => 'voucher-100',
                                'label'       => __( '€100', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Awarded approximately every 100 spins.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                                'colour'      => '#577590',
                                'color'       => '#577590',
                        ),
                        array(
                                'id'          => 'try-again-d',
                                'label'       => __( 'Try Again', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Random probability outcome.', 'gn-tsiartas-spin-to-win' ),
                                'icon'        => '✖',
                                'weight'      => 1,
                                'colour'      => '#277da1',
                                'color'       => '#277da1',
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
                $settings  = $this->get_plugin_settings();
                $day_label = $this->get_configured_day_label( $settings );

                if ( '' === $day_label ) {
                        $day_label = __( 'Friday', 'gn-tsiartas-spin-to-win' );
                }

                return array(
                        'prompt' => __( 'Spin the wheel for a chance to win exclusive rewards!', 'gn-tsiartas-spin-to-win' ),
                        'win'    => __( 'Congratulations! You won %s.', 'gn-tsiartas-spin-to-win' ),
                        'lose'   => __( 'Thanks for playing! Try again soon.', 'gn-tsiartas-spin-to-win' ),
                        'alreadyPlayed' => sprintf(
                                __( 'You have already played this week. Please visit us again next %s!', 'gn-tsiartas-spin-to-win' ),
                                $day_label
                        ),
                        'error'         => __( 'The spin could not be completed. Please try again shortly.', 'gn-tsiartas-spin-to-win' ),
                        'errorTitle'    => __( 'Something went wrong', 'gn-tsiartas-spin-to-win' ),
                        'depleted'      => sprintf(
                                __( 'All vouchers have been claimed for today. Please come back next %s.', 'gn-tsiartas-spin-to-win' ),
                                $day_label
                        ),
                        'depletedTitle' => __( 'No vouchers remaining', 'gn-tsiartas-spin-to-win' ),
                );
        }

}
