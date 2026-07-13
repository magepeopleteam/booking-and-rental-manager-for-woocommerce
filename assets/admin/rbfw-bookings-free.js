/**
 * FREE teaser "Bookings" page script. Self-contained: jQuery only.
 * Handles the delete confirmation modal, the delete AJAX, a minimal toast, and the
 * "PRO" locked-action hint. Never references any Pro asset.
 */
( function ( $ ) {
	'use strict';

	if ( typeof rbfwBookingsFree === 'undefined' ) {
		return;
	}

	/* ----- Minimal toast (no Pro dependency) ----- */
	function toast( message, type ) {
		var $c = $( '#rbfwfb-toast-container' );
		if ( ! $c.length ) {
			$c = $( '<div id="rbfwfb-toast-container"></div>' ).appendTo( 'body' );
		}
		var icon = type === 'error' ? 'dashicons-warning' : 'dashicons-yes-alt';
		var $t = $(
			'<div class="rbfwfb-toast rbfwfb-toast-' + ( type || 'success' ) + '">' +
				'<span class="dashicons ' + icon + '"></span>' +
				'<span class="rbfwfb-toast-msg"></span>' +
			'</div>'
		);
		$t.find( '.rbfwfb-toast-msg' ).text( message || '' );
		$c.append( $t );
		window.requestAnimationFrame( function () { $t.addClass( 'is-visible' ); } );
		setTimeout( function () {
			$t.removeClass( 'is-visible' );
			setTimeout( function () { $t.remove(); }, 250 );
		}, 2600 );
	}

	function closeModal() {
		$( '#rbfwfb-delete-modal' ).fadeOut( 150 );
	}

	$( function () {

		/* ----- Locked (PRO) actions ----- */
		$( document ).on( 'click', '.rbfwfb-locked-action', function () {
			toast( rbfwBookingsFree.i18n.proOnly, 'error' );
		} );

		/* ----- Open delete modal ----- */
		$( document ).on( 'click', '.rbfwfb-del-btn', function () {
			var $btn   = $( this );
			var source = $btn.data( 'source' );
			$( '#rbfwfb-delete-id' ).val( $btn.data( 'id' ) );
			$( '#rbfwfb-delete-ref' ).text( $btn.data( 'ref' ) || ( '#' + $btn.data( 'id' ) ) );
			$( '#rbfwfb-delete-note' ).toggle( source === 'woocommerce' );
			$( '#rbfwfb-delete-modal' ).css( 'display', 'flex' ).hide().fadeIn( 150 );
		} );

		/* ----- Close modal (button, overlay) ----- */
		$( document ).on( 'click', '.rbfwfb-modal-close', closeModal );
		$( document ).on( 'click', '.rbfwfb-modal', function ( e ) {
			if ( e.target === this ) { closeModal(); }
		} );

		/* ----- Confirm delete ----- */
		$( document ).on( 'click', '#rbfwfb-delete-confirm', function () {
			var $btn = $( this ).prop( 'disabled', true );
			var id   = $( '#rbfwfb-delete-id' ).val();
			$.post( rbfwBookingsFree.ajaxUrl, {
				action     : 'rbfw_free_booking_delete',
				nonce      : rbfwBookingsFree.nonce,
				booking_id : id
			}, function ( res ) {
				if ( res && res.success ) {
					closeModal();
					$( 'tr[data-row-id="' + id + '"]' ).fadeOut( 200, function () { $( this ).remove(); } );
					toast( ( res.data && res.data.message ) || rbfwBookingsFree.i18n.deleted, 'success' );
				} else {
					toast( ( res && res.data && res.data.message ) || rbfwBookingsFree.i18n.deleteError, 'error' );
				}
			} ).fail( function () {
				toast( rbfwBookingsFree.i18n.deleteError, 'error' );
			} ).always( function () {
				$btn.prop( 'disabled', false );
			} );
		} );
	} );

} )( jQuery );
