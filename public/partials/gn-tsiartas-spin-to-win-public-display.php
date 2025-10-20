<?php
/**
 * Provide a public-facing view for the plugin.
 *
 * The shortcode renderer passes an associative array named
 * `$gn_tsiartas_spin_to_win_data` containing the configuration payload and
 * presentation flags. This template focuses solely on markup so the PHP class
 * stays lean and maintainable.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'config'              => array(),
	'wheel_is_visible'    => false,
	'cashier_notice'      => '',
	'inactive_message'    => '',
	'show_cashier_notice' => true,
);

$payload = wp_parse_args( isset( $gn_tsiartas_spin_to_win_data ) ? $gn_tsiartas_spin_to_win_data : array(), $defaults );

$config_json = wp_json_encode( $payload['config'] );
if ( false === $config_json ) {
	$config_json = '{}';
}

$container_classes = 'gn-tsiartas-spin-to-win';
if ( empty( $payload['wheel_is_visible'] ) ) {
	$container_classes .= ' gn-tsiartas-spin-to-win--inactive';
}
?>
<?php if ( ! empty( $payload['wheel_is_visible'] ) ) : ?>
<div class="gn-tsiartas-spin-to-win__desktop-modal" role="dialog" aria-labelledby="gn-tsiartas-spin-to-win-desktop-modal-title" aria-describedby="gn-tsiartas-spin-to-win-desktop-modal-description" aria-hidden="false" tabindex="-1" data-gn-tsiartas-spin-to-win-desktop-modal>
        <div class="gn-tsiartas-spin-to-win__desktop-modal__content" role="document">
                <h2 class="gn-tsiartas-spin-to-win__desktop-modal__title" id="gn-tsiartas-spin-to-win-desktop-modal-title">
                        <?php esc_html_e( 'Play Spin to Win on your phone', 'gn-tsiartas-spin-to-win' ); ?>
                </h2>
                <p class="gn-tsiartas-spin-to-win__desktop-modal__description" id="gn-tsiartas-spin-to-win-desktop-modal-description">
                        <?php esc_html_e( 'Scan the in-store QR code with your mobile camera to launch the wheel and claim today’s rewards.', 'gn-tsiartas-spin-to-win' ); ?>
                </p>
                <button type="button" class="gn-tsiartas-spin-to-win__desktop-modal__close" data-action="dismiss-desktop-modal">
                        <?php esc_html_e( 'Got it, I will play on mobile', 'gn-tsiartas-spin-to-win' ); ?>
                </button>
        </div>
</div>
<?php endif; ?>
<div class="<?php echo esc_attr( $container_classes ); ?>" data-gn-tsiartas-spin-to-win="<?php echo esc_attr( $config_json ); ?>">
        <?php if ( ! empty( $payload['wheel_is_visible'] ) ) : ?>
                <div class="gn-tsiartas-spin-to-win__canvas" role="presentation" aria-hidden="true"></div>
                <?php if ( ! empty( $payload['show_cashier_notice'] ) && '' !== $payload['cashier_notice'] ) : ?>
			<p class="gn-tsiartas-spin-to-win__cashier-notice"><?php echo esc_html( $payload['cashier_notice'] ); ?></p>
		<?php endif; ?>
		<button type="button" class="gn-tsiartas-spin-to-win__spin-button" data-action="spin">
			<?php esc_html_e( 'Spin the wheel', 'gn-tsiartas-spin-to-win' ); ?>
		</button>
	<?php else : ?>
		<p class="gn-tsiartas-spin-to-win__inactive-message">
			<?php echo esc_html( $payload['inactive_message'] ); ?>
		</p>
	<?php endif; ?>
</div>
