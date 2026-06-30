( function () {
	'use strict';

	function getSessionToken( formId ) {
		var key = 'gfat_session_' + formId;
		var token = sessionStorage.getItem( key );

		if ( ! token ) {
			token = 'gfat_' + Date.now() + '_' + Math.random().toString( 36 ).slice( 2 );
			sessionStorage.setItem( key, token );
		}

		return token;
	}

	function fieldIdFromInput( input ) {
		var match = input.id.match( /input_(\d+)_(.+)/ );
		return match ? match[ 2 ] : input.name || input.id;
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var forms = document.querySelectorAll( 'form[id^="gform_"]' );

		forms.forEach( function ( form ) {
			var formIdMatch = form.id.match( /gform_(\d+)/ );
			if ( ! formIdMatch ) {
				return;
			}

			var formId = formIdMatch[ 1 ];
			var hasInteracted = false;
			var submitted = false;
			var lastFieldId = '';
			var sessionToken = getSessionToken( formId );

			// Inject the session token so the server can clear it on successful submission.
			var hiddenInput = document.createElement( 'input' );
			hiddenInput.type = 'hidden';
			hiddenInput.name = 'gfat_session_token';
			hiddenInput.value = sessionToken;
			form.appendChild( hiddenInput );

			form.addEventListener(
				'focusin',
				function ( e ) {
					if ( ! e.target.id ) {
						return;
					}
					hasInteracted = true;
					lastFieldId = fieldIdFromInput( e.target );
				},
				true
			);

			form.addEventListener( 'submit', function () {
				submitted = true;
			} );

			window.addEventListener( 'beforeunload', function () {
				if ( submitted || ! hasInteracted || ! window.gfatTracker ) {
					return;
				}

				var data = new FormData();
				data.append( 'action', 'gfat_log_abandonment' );
				data.append( 'nonce', gfatTracker.nonce );
				data.append( 'form_id', formId );
				data.append( 'last_field_id', lastFieldId );
				data.append( 'page_url', window.location.href );
				data.append( 'session_token', sessionToken );

				navigator.sendBeacon( gfatTracker.ajaxUrl, data );
			} );
		} );
	} );
} )();
