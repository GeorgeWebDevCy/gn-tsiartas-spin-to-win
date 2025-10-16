(function( $ ) {
        'use strict';

        /**
         * All of the code for your admin-facing JavaScript source
         * should reside in this file.
         */

        $( function() {
                var $settingsForm = $( '.wrap form[action="options.php"]' );

                // eslint-disable-next-line no-console
                console.log( '[SpinToWin][Admin] Admin script initialised', {
                        hasSettingsForm: $settingsForm.length > 0,
                } );

                if ( ! $settingsForm.length ) {
                        return;
                }

                $settingsForm.on( 'change', 'input, select, textarea', function( event ) {
                        var $field = $( event.currentTarget );

                        // eslint-disable-next-line no-console
                        console.log( '[SpinToWin][Admin] Field changed', {
                                fieldName: $field.attr( 'name' ),
                                fieldType: $field.attr( 'type' ) || event.currentTarget.tagName.toLowerCase(),
                                fieldValue: $field.val(),
                        } );
                } );

                $settingsForm.on( 'submit', function( event ) {
                        // eslint-disable-next-line no-console
                        console.log( '[SpinToWin][Admin] Settings form submitted', {
                                serializedData: $settingsForm.serialize(),
                        } );
                } );
        } );
})( jQuery );
