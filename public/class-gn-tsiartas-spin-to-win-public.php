<?php
/**
 * GN Tsiartas Spin To Win – public class (local stub)
 *
 * This stub class is included for demonstration purposes. It shows how
 * to define the default prize configuration without a €15 prize and
 * interleave try‑again slices after each voucher. The real plugin
 * contains additional logic for AJAX handlers and state tracking.
 */

class Gn_Tsiartas_Spin_To_Win_Public {
    /**
     * Return the default prizes for the spin wheel. We define four
     * voucher prizes (5, 10, 50 and 100) each followed by a try‑again
     * slice. Removing the legacy €15 prize keeps the wheel at eight
     * slices, matching the design shown in the reference image.
     *
     * @return array
     */
    public function get_default_prizes() {
        return [
            // Voucher €5
            [
                'id'          => 'voucher-5',
                'label'       => '€5',
                'description' => 'Win a €5 voucher',
                'colour'      => '#009688',
                'icon'        => 'gift',
                'value'       => 5,
                'type'        => 'voucher',
            ],
            // Try again after €5
            [
                'id'          => 'try-again-a',
                'label'       => 'Try Again',
                'description' => 'Better luck next time!',
                'colour'      => '#4CAF50',
                'icon'        => 'redo',
                'type'        => 'try-again',
                'is_try_again' => true,
            ],
            // Voucher €10
            [
                'id'          => 'voucher-10',
                'label'       => '€10',
                'description' => 'Win a €10 voucher',
                'colour'      => '#FFC107',
                'icon'        => 'gift',
                'value'       => 10,
                'type'        => 'voucher',
            ],
            // Try again after €10
            [
                'id'          => 'try-again-b',
                'label'       => 'Try Again',
                'description' => 'Better luck next time!',
                'colour'      => '#F44336',
                'icon'        => 'redo',
                'type'        => 'try-again',
                'is_try_again' => true,
            ],
            // Voucher €50
            [
                'id'          => 'voucher-50',
                'label'       => '€50',
                'description' => 'Win a €50 voucher',
                'colour'      => '#3F51B5',
                'icon'        => 'gift',
                'value'       => 50,
                'type'        => 'voucher',
            ],
            // Try again after €50
            [
                'id'          => 'try-again-c',
                'label'       => 'Try Again',
                'description' => 'Better luck next time!',
                'colour'      => '#FF5722',
                'icon'        => 'redo',
                'type'        => 'try-again',
                'is_try_again' => true,
            ],
            // Voucher €100
            [
                'id'          => 'voucher-100',
                'label'       => '€100',
                'description' => 'Win a €100 voucher',
                'colour'      => '#00BCD4',
                'icon'        => 'gift',
                'value'       => 100,
                'type'        => 'voucher',
            ],
            // Final try again slice
            [
                'id'          => 'try-again-d',
                'label'       => 'Try Again',
                'description' => 'Better luck next time!',
                'colour'      => '#8BC34A',
                'icon'        => 'redo',
                'type'        => 'try-again',
                'is_try_again' => true,
            ],
        ];
    }
}