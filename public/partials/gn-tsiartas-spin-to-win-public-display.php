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
