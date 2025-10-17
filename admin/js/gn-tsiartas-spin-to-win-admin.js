(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 */

	$( function() {
		var $settingsForm = $( '.wrap form[action="options.php"]' );

		if ( ! $settingsForm.length ) {
			return;
		}
	} );
})( jQuery );
