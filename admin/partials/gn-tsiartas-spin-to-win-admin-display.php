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

        <form method="post" action="options.php">
                <?php
                settings_fields( 'gn_tsiartas_spin_to_win' );
                do_settings_sections( 'gn_tsiartas_spin_to_win' );
                submit_button();
                ?>
        </form>
</div>
