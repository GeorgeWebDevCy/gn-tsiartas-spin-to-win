<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://https://www.georgenicolaou.me/
 * @since      1.0.0
 *
 * @package    Gn_Tsiartas_Spin_To_Win
 * @subpackage Gn_Tsiartas_Spin_To_Win/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

?>

<div class="wrap">
        <h1><?php esc_html_e( 'Spin & Win Settings', 'gn-tsiartas-spin-to-win' ); ?></h1>

        <div class="notice notice-info">
                <p>
                        <strong><?php esc_html_e( 'Server day:', 'gn-tsiartas-spin-to-win' ); ?></strong>
                        <?php echo esc_html( isset( $server_day ) ? $server_day : __( 'Unavailable', 'gn-tsiartas-spin-to-win' ) ); ?>
                </p>
                <p>
                        <strong><?php esc_html_e( 'Server date & time:', 'gn-tsiartas-spin-to-win' ); ?></strong>
                        <?php echo esc_html( isset( $server_datetime ) ? $server_datetime : __( 'Unavailable', 'gn-tsiartas-spin-to-win' ) ); ?>
                </p>
                <p>
                        <strong><?php esc_html_e( 'Wheel visibility:', 'gn-tsiartas-spin-to-win' ); ?></strong>
                        <?php
                        if ( isset( $wheel_is_visible ) ) {
                                echo $wheel_is_visible ? esc_html__( 'The wheel is currently visible.', 'gn-tsiartas-spin-to-win' ) : esc_html__( 'The wheel is currently hidden.', 'gn-tsiartas-spin-to-win' );
                        } else {
                                esc_html_e( 'Visibility status unavailable.', 'gn-tsiartas-spin-to-win' );
                        }
                        ?>
                </p>
        </div>

        <form method="post" action="options.php">
                <?php
                settings_fields( 'gn_tsiartas_spin_to_win' );
                do_settings_sections( 'gn_tsiartas_spin_to_win' );
                submit_button();
                ?>
        </form>
</div>
