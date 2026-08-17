/**
 * Wires a Gravity form to the rental booking form that follows it.
 *
 * The two forms are siblings, never nested — see class-rbfw-gf-frontend.php for
 * why. This script is the only thing connecting them: it carries the entry id and
 * its claim token from the Gravity confirmation into a pair of hidden inputs on
 * the booking form, and in "required" mode keeps the booking form hidden until
 * that has happened.
 *
 * Works for any number of forms and any number of rentals on a page; nothing here
 * knows a form id.
 */
( function ( $ ) {
	'use strict';

	var CONFIG = window.rbfwGf || {};

	/** The booking form belonging to a given wrapper, i.e. the next one in the DOM. */
	function bookingFormFor( $wrap ) {
		var $container = $wrap.closest( '.rbfw-single-container' );
		var $form = $container.length
			? $container.find( 'form.mp_rbfw_ticket_form' ).first()
			: $();

		if ( ! $form.length ) {
			// Templates differ between the bundled themes; fall back to the next
			// booking form after this wrapper in document order.
			$form = $wrap.nextAll().find( 'form.mp_rbfw_ticket_form' ).first();
			if ( ! $form.length ) {
				$form = $wrap.parent().find( 'form.mp_rbfw_ticket_form' ).first();
			}
		}

		return $form;
	}

	function setHidden( $form, name, value ) {
		var $input = $form.find( 'input[name="' + name + '"]' ).first();

		if ( ! $input.length ) {
			$input = $( '<input type="hidden">' ).attr( 'name', name );
			$form.append( $input );
		}

		$input.val( value );
	}

	/** Hide the booking form until the questions are answered. */
	function gate( $wrap ) {
		var $form = bookingFormFor( $wrap );

		if ( $form.length ) {
			$form.addClass( 'rbfw-gf-gated' );
		}
	}

	function open( $wrap, entryId, token ) {
		var $form = bookingFormFor( $wrap );

		if ( ! $form.length ) {
			return;
		}

		setHidden( $form, 'rbfw_gf_entry_id', entryId );
		setHidden( $form, 'rbfw_gf_token', token );

		$form.removeClass( 'rbfw-gf-gated' );
		$wrap.addClass( 'rbfw-gf-wrap--answered' );
		$wrap.find( '.rbfw-gf-gate-note' ).remove();

		if ( CONFIG.i18n && CONFIG.i18n.answersSaved && ! $wrap.find( '.rbfw-gf-done' ).length ) {
			$( '<p class="rbfw-gf-done" role="status"></p>' )
				.text( CONFIG.i18n.answersSaved )
				.appendTo( $wrap );
		}

		// Bring the newly revealed booking form into view without yanking the
		// page for anyone who has asked for reduced motion.
		var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var node = $form.get( 0 );

		if ( node && node.scrollIntoView ) {
			node.scrollIntoView( { behavior: reduce ? 'auto' : 'smooth', block: 'start' } );
		}
	}

	/**
	 * The item id travels with the Gravity submission so the entry can be pinned
	 * to the rental it was answered for.
	 */
	function stampItemId( $wrap ) {
		var itemId = $wrap.data( 'rbfwGfItem' );
		var $gf = $wrap.find( 'form[id^="gform_"]' ).first();

		if ( ! $gf.length || ! itemId ) {
			return;
		}

		if ( ! $gf.find( 'input[name="rbfw_gf_item_id"]' ).length ) {
			$gf.append(
				$( '<input type="hidden" name="rbfw_gf_item_id">' ).val( itemId )
			);
		}
	}

	function initWrap( $wrap ) {
		var mode = $wrap.data( 'rbfwGfMode' );

		stampItemId( $wrap );

		// 'booking' mode: this form IS the order form, so the rental booking form
		// is hidden for good and never revealed.
		if ( 'booking' === mode ) {
			gate( $wrap );
			return;
		}

		if ( 'required' === mode && ! $wrap.hasClass( 'rbfw-gf-wrap--answered' ) ) {
			gate( $wrap );
		}
	}

	function claimFrom( $scope ) {
		var $claim = $scope.find( '.rbfw-gf-claim' ).first();

		if ( ! $claim.length ) {
			return;
		}

		var $wrap = $claim.closest( '.rbfw-gf-wrap' );

		if ( ! $wrap.length ) {
			return;
		}

		// Nothing to hand over in 'booking' mode: the submission already created
		// the booking server-side, and there is no booking form to reveal.
		if ( 'booking' === $wrap.data( 'rbfwGfMode' ) ) {
			return;
		}

		open( $wrap, $claim.data( 'entryId' ), $claim.data( 'token' ) );
	}

	$( function () {
		$( '.rbfw-gf-wrap' ).each( function () {
			initWrap( $( this ) );
		} );

		// A confirmation already on the page (non-AJAX submit, or a page restored
		// from cache) still has to release the gate.
		claimFrom( $( document ) );
	} );

	// Gravity Forms fires this after every AJAX render, including each page of a
	// multi-page form, and once more for the confirmation.
	$( document ).on( 'gform_post_render', function ( e, formId ) {
		$( '.rbfw-gf-wrap[data-rbfw-gf-form="' + formId + '"]' ).each( function () {
			stampItemId( $( this ) );
		} );
	} );

	$( document ).on( 'gform_confirmation_loaded', function ( e, formId ) {
		claimFrom( $( '.rbfw-gf-wrap[data-rbfw-gf-form="' + formId + '"]' ) );
	} );
} )( jQuery );
