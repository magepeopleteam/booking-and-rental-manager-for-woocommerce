( function ( $ ) {
	'use strict';

	var config = window.rbfwIntegrationInstaller || {};
	var staleAfterSetupTab = false;

	function currentCard() {
		return $( '.rbfw-integration-card' ).first();
	}

	/**
	 * Re-render the card in place. Runs as its own request so plugins the setup
	 * step just activated are fully loaded when their status is read.
	 */
	function refreshCard( after ) {
		return $.ajax( {
			url: config.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'rbfw_integration_card',
				nonce: config.nonce,
				after: after || ''
			}
		} ).then( function ( response ) {
			if ( ! response || ! response.success || ! response.data || ! response.data.html ) {
				return $.Deferred().reject().promise();
			}
			currentCard().replaceWith( response.data.html );
			return currentCard();
		} );
	}

	$( document ).on( 'click', '.rbfw-integration-action', function () {
		var $button = $( this );
		var $card = $button.closest( '.rbfw-integration-card' );
		var $buttons = $card.find( '.rbfw-integration-action' );
		var $status = $card.find( '.rbfw-integration-action-status' );
		var originalLabel = $button.text();
		var confirmText = $button.data( 'confirm' );

		if ( $card.data( 'busy' ) ) {
			return;
		}
		if ( confirmText && ! window.confirm( confirmText ) ) {
			return;
		}

		$card.data( 'busy', true );
		$buttons.prop( 'disabled', true ).addClass( 'disabled' );
		$button.addClass( 'is-busy' ).text( $button.data( 'progress-label' ) || config.i18n.working );
		$status.removeClass( 'is-error is-success' ).text( $button.text() );

		function showFailure( message ) {
			$status.addClass( 'is-error' ).text( message || config.i18n.failed );
			$button.removeClass( 'is-busy' ).text( originalLabel );
			$buttons.prop( 'disabled', false ).removeClass( 'disabled' );
			$card.data( 'busy', false );
		}

		$.ajax( {
			url: config.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: $button.data( 'action' ),
				nonce: config.nonce,
				plugin: $button.data( 'plugin' ) || ''
			}
		} ).done( function ( response ) {
			if ( ! response || ! response.success ) {
				showFailure( response && response.data && response.data.message );
				return;
			}

			var message = response.data.message;
			refreshCard( $button.data( 'plugin' ) ).done( function ( $fresh ) {
				$fresh.find( '.rbfw-integration-action-status' ).addClass( 'is-success' ).text( message );
			} ).fail( function () {
				// The step succeeded; only the status could not be redrawn.
				window.location.reload();
			} );
		} ).fail( function ( xhr ) {
			var response = xhr.responseJSON;
			showFailure( response && response.data && response.data.message );
		} );
	} );

	// Stripe account setup happens on other screens, opened in a new tab. Pick up
	// the new status when the admin comes back.
	$( document ).on( 'click', '.rbfw-integration-card [data-refresh-on-return]', function () {
		staleAfterSetupTab = true;
	} );

	$( window ).on( 'focus', function () {
		if ( ! staleAfterSetupTab || currentCard().data( 'busy' ) ) {
			return;
		}
		staleAfterSetupTab = false;
		refreshCard();
	} );
}( jQuery ) );
