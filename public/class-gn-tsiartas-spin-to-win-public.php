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
                $this->localized_data['settings']                 = $this->get_global_settings();

                // Ensure public assets are present and data is localized for the script.
                wp_enqueue_style( $this->plugin_name );
                wp_enqueue_script( $this->plugin_name );
                wp_localize_script( $this->plugin_name, 'gnTsiartasSpinToWinConfig', $this->localized_data );

                $show_cta = filter_var( $atts['show_cta'], FILTER_VALIDATE_BOOLEAN );
                $prizes   = isset( $configuration['prizes'] ) ? $configuration['prizes'] : array();
                $messages = isset( $configuration['messages'] ) ? $configuration['messages'] : array();

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
                                <div class="gn-tsiartas-spin-to-win__wheel" data-role="wheel" aria-live="polite"></div>
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
                                </div>
                                <div class="gn-tsiartas-spin-to-win__prize-list-wrapper">
                                        <h2 class="gn-tsiartas-spin-to-win__heading"><?php echo esc_html__( 'Available prizes', 'gn-tsiartas-spin-to-win' ); ?></h2>
                                        <ul class="gn-tsiartas-spin-to-win__prize-list" data-role="prize-list">
                                                <?php foreach ( $prizes as $prize ) : ?>
                                                        <li class="gn-tsiartas-spin-to-win__prize-item" data-prize-id="<?php echo esc_attr( $prize['id'] ); ?>">
                                                                <span class="gn-tsiartas-spin-to-win__prize-label"><?php echo esc_html( $prize['label'] ); ?></span>
                                                                <?php if ( ! empty( $prize['description'] ) ) : ?>
                                                                        <span class="gn-tsiartas-spin-to-win__prize-description"><?php echo esc_html( $prize['description'] ); ?></span>
                                                                <?php endif; ?>
                                                        </li>
                                                <?php endforeach; ?>
                                        </ul>
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
                                        <button type="button" class="gn-tsiartas-spin-to-win__modal-close" data-action="close-modal">
                                                <span class="gn-tsiartas-spin-to-win__modal-close-label"><?php echo esc_html__( 'Close', 'gn-tsiartas-spin-to-win' ); ?></span>
                                        </button>
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

                if ( empty( $audio_settings['spin'] ) ) {
                        $audio_settings['spin'] = plugins_url( 'public/audio/spin-loop.php', GN_TSIARTAS_SPIN_TO_WIN_PLUGIN_FILE );
                }

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
        private function get_global_settings() {
                $default_duration = 4600;
                $option_name      = class_exists( 'Gn_Tsiartas_Spin_To_Win_Admin' ) ? Gn_Tsiartas_Spin_To_Win_Admin::OPTION_SPIN_DURATION : 'gn_tsiartas_spin_to_win_spin_duration';
                $spin_duration    = get_option( $option_name, $default_duration );
                $spin_duration    = absint( $spin_duration );

                if ( $spin_duration < 600 || $spin_duration > 60000 ) {
                        $spin_duration = $default_duration;
                }

                return array(
                        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                        'nonce'     => wp_create_nonce( 'gn-tsiartas-spin-to-win' ),
                        'pluginUrl' => plugin_dir_url( __FILE__ ),
                        'spinDuration' => $spin_duration,
                );
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

                        $normalised[] = array(
                                'id'          => isset( $prize['id'] ) ? sanitize_title( $prize['id'] ) : sanitize_title( $label . '-' . $index ),
                                'label'       => $label,
                                'description' => isset( $prize['description'] ) ? sanitize_textarea_field( $prize['description'] ) : '',
                                'weight'      => isset( $prize['weight'] ) ? (float) $prize['weight'] : 1,
                                'value'       => isset( $prize['value'] ) ? sanitize_text_field( $prize['value'] ) : '',
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
                                'id'          => 'grand-prize',
                                'label'       => __( '€100 Gift Card', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Enjoy a €100 shopping spree at Tsiartas Supermarket.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 1,
                        ),
                        array(
                                'id'          => 'runner-up',
                                'label'       => __( 'Free Groceries', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Take home a basket of hand-picked essentials.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 2,
                        ),
                        array(
                                'id'          => 'discount',
                                'label'       => __( '10% Discount Voucher', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Apply the discount on your next visit.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 4,
                        ),
                        array(
                                'id'          => 'thank-you',
                                'label'       => __( 'Thank You', 'gn-tsiartas-spin-to-win' ),
                                'description' => __( 'Better luck next time – enjoy exclusive member offers.', 'gn-tsiartas-spin-to-win' ),
                                'weight'      => 3,
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
