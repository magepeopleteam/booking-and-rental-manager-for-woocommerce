/**
 * Delivery & Collection block behaviour.
 *
 * Shows the address/distance fields once a leg is ticked, and previews the price from the
 * band table the server embedded on the block.
 *
 * The preview is a COURTESY ONLY. Nothing here is trusted: the browser posts the customer's
 * choice (which legs, address, distance) and the server recomputes every amount from the
 * stored bands. Keeping the same band logic on both sides means the customer is not
 * surprised at checkout, but tampering with this file changes nothing about what is charged.
 */
( function ( $ ) {
	'use strict';

	function num( value ) {
		var n = parseFloat( String( value ).replace( ',', '.' ) );
		return isNaN( n ) ? 0 : n;
	}

	function readJson( $el, key, fallback ) {
		try {
			var raw = $el.attr( 'data-' + key );
			var out = raw ? JSON.parse( raw ) : null;
			return out || fallback;
		} catch ( e ) {
			return fallback;
		}
	}

	/** First band covering this distance, inclusive on both bounds. Mirrors the PHP. */
	function bandPrice( km, bands ) {
		for ( var i = 0; i < bands.length; i++ ) {
			if ( km >= num( bands[ i ].from ) && km <= num( bands[ i ].to ) ) {
				return num( bands[ i ].price );
			}
		}
		return null;
	}

	function money( amount ) {
		var cfg = window.rbfwDelivery || {};
		var n   = ( Math.round( amount * 100 ) / 100 ).toFixed( 2 );
		return ( cfg.symbol || '' ) + n;
	}

	function t( key, fallback ) {
		var i = window.rbfwDelivery && window.rbfwDelivery.i18n;
		return ( i && i[ key ] ) ? i[ key ] : fallback;
	}

	function update( $block ) {
		var wantDelivery   = $block.find( 'input[name="rbfw_delivery_wanted"]' ).is( ':checked' );
		var wantCollection = $block.find( 'input[name="rbfw_collection_wanted"]' ).is( ':checked' );
		var $fields        = $block.find( '.rbfw-delivery-fields' );
		var $quote         = $block.find( '.rbfw-delivery-quote' );

		if ( ! wantDelivery && ! wantCollection ) {
			$fields.slideUp( 120 );
			$quote.empty().removeClass( 'is-error' );
			$block.removeData( 'quote' );
			$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: 0, error: '' } ] );
			return;
		}

		$fields.slideDown( 120 );

		var km      = num( $block.find( '.rbfw-delivery-distance' ).val() );
		var baseFee = num( $block.attr( 'data-base-fee' ) );
		var free    = num( $block.attr( 'data-free-radius' ) );
		var maxKm   = num( $block.attr( 'data-max-distance' ) );
		var mode    = $block.attr( 'data-collection-mode' ) || 'same';
		var bands   = readJson( $block, 'bands', [] );
		var cBands  = readJson( $block, 'collection-bands', [] );

		// No zone chosen yet — prompt rather than quoting a misleading zero.
		if ( ! $block.find( '.rbfw-delivery-distance' ).val() ) {
			$quote.removeClass( 'is-error' ).html(
				'<span class="rbfw-delivery-quote-hint">' + t( 'enterDistance', 'Choose your area to see the price.' ) + '</span>'
			);
			$block.removeData( 'quote' );
			$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: 0, error: 'incomplete' } ] );
			return;
		}

		if ( maxKm > 0 && km > maxKm ) {
			$quote.addClass( 'is-error' ).text(
				t( 'outOfRange', 'Sorry, we only deliver within %s km.' ).replace( '%s', maxKm )
			);
			$block.removeData( 'quote' );
			$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: 0, error: 'out_of_range' } ] );
			return;
		}

		var delivery = 0, collection = 0, label = '';

		if ( free > 0 && km <= free ) {
			label = t( 'freeZone', 'Free delivery zone' );
		} else {
			var price = bandPrice( km, bands );
			if ( price === null ) {
				$quote.addClass( 'is-error' ).text( t( 'noBand', 'We could not work out a price for that distance. Please contact us.' ) );
				$block.removeData( 'quote' );
				$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: 0, error: 'no_band' } ] );
				return;
			}
			if ( wantDelivery ) { delivery = baseFee + price; }
			if ( wantCollection ) {
				if ( 'free' === mode ) {
					collection = 0;
				} else if ( 'own' === mode ) {
					var own = bandPrice( km, cBands );
					collection = ( own === null ) ? ( baseFee + price ) : ( baseFee + own );
				} else {
					collection = baseFee + price;
				}
			}
		}

		var total = delivery + collection;
		var rows  = '';
		if ( wantDelivery )   { rows += '<span>' + t( 'delivery', 'Delivery' ) + ': <strong>' + money( delivery ) + '</strong></span>'; }
		if ( wantCollection ) { rows += '<span>' + t( 'collection', 'Collection' ) + ': <strong>' + money( collection ) + '</strong></span>'; }
		if ( label )          { rows += '<span class="rbfw-delivery-quote-badge">' + label + '</span>'; }

		$quote.removeClass( 'is-error' ).html( rows );
		$block.data( 'quote', total );

		// The price tables listen for this so the running total stays honest.
		$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: total, error: '' } ] );
	}

	/* ── Booking summary integration ───────────────────────────────────── */

	/**
	 * Publish the delivery charge for the pricing scripts to pick up.
	 *
	 * The summary total is OWNED by those scripts, which rebuild it from scratch on every
	 * date / quantity / add-on change. Patching the rendered total afterwards would be
	 * undone by the customer's very next click, so the value is exposed here and added
	 * inside their own calculation instead (see rbfw_delivery_price in sd_script.js).
	 *
	 * This is display only. The amount actually charged is recomputed server-side from the
	 * band table at add-to-cart, so nothing in the browser can change what the customer
	 * pays — this only keeps the figure on screen honest.
	 */
	$( document.body ).on( 'rbfw_delivery_changed', function ( e, data ) {
		window.rbfwDeliveryTotal = ( data && data.total ) ? data.total : 0;

		// Nudge the pricing engine to repaint with the new figure. Each item type has its
		// own recalculation entry point, so try the known ones and fall back to updating
		// just our own line when none is present (e.g. a flow with no live summary).
		var recalc = window.rbfw_price_calculation_sd   // single day / timely
			|| window.rbfw_price_calculation_md         // multi day
			|| window.calculateTotal;
		if ( typeof recalc === 'function' ) {
			try { recalc(); return; } catch ( err ) { /* fall through to the local update */ }
		}

		var $line = $( '.rbfw-delivery-costing' );
		if ( window.rbfwDeliveryTotal > 0 ) {
			$line.find( '.rbfw-delivery-cost-value' ).html( money( window.rbfwDeliveryTotal ) );
			$line.show();
		} else {
			$line.hide();
		}
	} );

	$( document ).on( 'change', '.rbfw-delivery-toggle', function () {
		update( $( this ).closest( '.rbfw-delivery-block' ) );
	} );

	$( document ).on( 'input change', '.rbfw-delivery-distance, .rbfw-delivery-address', function () {
		update( $( this ).closest( '.rbfw-delivery-block' ) );
	} );

	/**
	 * Block submission when a chosen delivery is incomplete or out of range.
	 *
	 * Without this the customer would submit, the server would refuse the quote, and the
	 * booking would silently go through with no delivery attached — the worst outcome for
	 * both sides. The server refusal still stands on its own; this just surfaces it early.
	 */
	$( document ).on( 'submit', '.mp_rbfw_ticket_form', function ( e ) {
		var $block = $( this ).find( '.rbfw-delivery-block' );
		if ( ! $block.length ) { return; }

		var wants = $block.find( '.rbfw-delivery-toggle:checked' ).length > 0;
		if ( ! wants ) { return; }

		var $distance = $block.find( '.rbfw-delivery-distance' );
		var $address  = $block.find( '.rbfw-delivery-address' );
		var maxKm     = num( $block.attr( 'data-max-distance' ) );
		var km        = num( $distance.val() );

		if ( ! $distance.val() || km < 0 ) {
			e.preventDefault();
			$distance.trigger( 'focus' );
			$block.find( '.rbfw-delivery-quote' ).addClass( 'is-error' ).text( t( 'needDistance', 'Please choose how far you are from us.' ) );
			return false;
		}
		if ( maxKm > 0 && km > maxKm ) {
			e.preventDefault();
			$distance.trigger( 'focus' );
			return false;
		}
		if ( $address.attr( 'data-required' ) && ! $.trim( $address.val() ) ) {
			e.preventDefault();
			$address.trigger( 'focus' );
			$block.find( '.rbfw-delivery-quote' ).addClass( 'is-error' ).text( t( 'needAddress', 'Please enter the delivery address.' ) );
			return false;
		}
	} );

	$( function () {
		$( '.rbfw-delivery-block' ).each( function () { update( $( this ) ); } );
	} );

} )( jQuery );
