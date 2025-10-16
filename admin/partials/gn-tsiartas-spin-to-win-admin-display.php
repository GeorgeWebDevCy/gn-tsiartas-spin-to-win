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

<div class="wrap gn-tsiartas-spin-to-win-settings">
        <h1><?php esc_html_e( 'Spin & Win Settings', 'gn-tsiartas-spin-to-win' ); ?></h1>

        <?php settings_errors(); ?>

        <form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
                <?php
                settings_fields( Gn_Tsiartas_Spin_To_Win_Admin::OPTION_GROUP );
                do_settings_sections( Gn_Tsiartas_Spin_To_Win_Admin::SETTINGS_PAGE );
                submit_button( __( 'Save Changes', 'gn-tsiartas-spin-to-win' ) );
                ?>
        </form>

        <p class="description">
                <?php
                printf(
                        /* translators: %s: currently saved spin duration */
                        esc_html__( 'Current spin duration: %s milliseconds.', 'gn-tsiartas-spin-to-win' ),
                        esc_html( absint( isset( $settings['spin_duration'] ) ? $settings['spin_duration'] : 4600 ) )
                );
                ?>
        </p>
</div>
