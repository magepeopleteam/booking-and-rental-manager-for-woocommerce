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

	/**
	 * Whether a leg is selected.
	 *
	 * A locked leg is a DISABLED checkbox plus a hidden input carrying the real field name,
	 * because a disabled control does not submit. Reading `:checked` alone would therefore
	 * report a mandatory leg as unselected and quote the customer nothing.
	 *
	 * @param {jQuery} $block
	 * @param {string} name Field name.
	 * @return {boolean}
	 */
	function legChosen( $block, name ) {
		var $hidden = $block.find( 'input[type="hidden"][name="' + name + '"]' );
		if ( $hidden.length ) { return 'yes' === $hidden.val(); }
		return $block.find( 'input[name="' + name + '"]' ).is( ':checked' );
	}

	function update( $block ) {
		var wantDelivery   = legChosen( $block, 'rbfw_delivery_wanted' );
		var wantCollection = legChosen( $block, 'rbfw_collection_wanted' );
		var $fields        = $block.find( '.rbfw-delivery-fields' );
		var $quote         = $block.find( '.rbfw-delivery-quote' );

		if ( ! wantDelivery && ! wantCollection ) {
			$fields.slideUp( 120 );
			$quote.empty().removeClass( 'is-error' );
			$block.removeData( 'quote' );
			$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: 0, delivery: 0, collection: 0, error: '' } ] );
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
			$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: 0, delivery: 0, collection: 0, error: 'incomplete' } ] );
			return;
		}

		if ( maxKm > 0 && km > maxKm ) {
			$quote.addClass( 'is-error' ).text(
				t( 'outOfRange', 'Sorry, we only deliver within %s km.' ).replace( '%s', maxKm )
			);
			$block.removeData( 'quote' );
			$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: 0, delivery: 0, collection: 0, error: 'out_of_range' } ] );
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
				$( document.body ).trigger( 'rbfw_delivery_changed', [ { total: 0, delivery: 0, collection: 0, error: 'no_band' } ] );
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

		// The price tables listen for this so the running total stays honest. Legs are
		// reported separately as well as summed: they are billed as two services and can be
		// priced by different band tables, so the summary shows a line for each.
		$( document.body ).trigger( 'rbfw_delivery_changed', [ {
			total: total,
			delivery: wantDelivery ? delivery : 0,
			collection: wantCollection ? collection : 0,
			error: ''
		} ] );
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
		window.rbfwDeliveryTotal      = ( data && data.total ) ? data.total : 0;
		window.rbfwDeliveryLegs       = {
			delivery:   ( data && data.delivery ) ? data.delivery : 0,
			collection: ( data && data.collection ) ? data.collection : 0
		};

		repaintTotals();
	} );

	/**
	 * Recalculation entry points, keyed by the summary box the booking form on the page
	 * actually rendered.
	 *
	 * Taking the FIRST function that happens to be defined was wrong. Every booking script
	 * is enqueued on every rental page (see RBFW_Dependencies::rbfw_enqueue_scripts), so
	 * rbfw_price_calculation_sd is ALWAYS defined — including on a multi-day item, where it
	 * rebuilt the summary from the single-day fields alone. The multi-day duration cost
	 * lives in #rbfw_duration_price, which that function never reads, so choosing delivery
	 * dropped the entire rental off the Price row and left only the delivery legs and fees.
	 * In Standalone mode that was not merely cosmetic: rbfw_native_checkout.js posts the
	 * displayed total minus delivery, so the booking was created for the fees alone.
	 *
	 * Matching on the summary markup keeps each item type with its own arithmetic.
	 */
	var RECALC_BY_SUMMARY = [
		// Multi day and multiple items — both render '.rbfw_bikecarmd_price_result'.
		[ '.rbfw_bikecarmd_price_result', 'rbfw_price_calculation_md' ],
		// Single day, plus the step / timely flows.
		[ '.rbfw_bikecarsd_price_summary, .rbfw_bikecarsd_price_summary_only', 'rbfw_price_calculation_sd' ]
	];

	/**
	 * Ask the booking form's own pricing engine to repaint with the new delivery figure.
	 *
	 * Falls back to updating just our own two lines when the page has no live summary, or
	 * when the engine throws — a delivery quote must never be able to break the form.
	 */
	function repaintTotals() {
		for ( var i = 0; i < RECALC_BY_SUMMARY.length; i++ ) {
			if ( ! $( RECALC_BY_SUMMARY[ i ][ 0 ] ).length ) { continue; }

			var recalc = window[ RECALC_BY_SUMMARY[ i ][ 1 ] ];
			if ( typeof recalc !== 'function' ) { continue; }

			try {
				recalc();
				return;
			} catch ( err ) {
				break; // Fall through to the local update below.
			}
		}

		rbfwRenderDeliveryLines();
	}

	/** Fill the two cost lines from the last quote. Shared with the pricing scripts. */
	function rbfwRenderDeliveryLines() {
		var legs = window.rbfwDeliveryLegs || { delivery: 0, collection: 0 };

		$( '.rbfw-delivery-costing' ).each( function () {
			var $l = $( this );
			if ( legs.delivery > 0 ) {
				$l.find( '.rbfw-delivery-cost-value' ).html( money( legs.delivery ) );
				$l.show();
			} else { $l.hide(); }
		} );

		$( '.rbfw-collection-costing' ).each( function () {
			var $l = $( this );
			if ( legs.collection > 0 ) {
				$l.find( '.rbfw-collection-cost-value' ).html( money( legs.collection ) );
				$l.show();
			} else { $l.hide(); }
		} );
	}
	window.rbfwRenderDeliveryLines = rbfwRenderDeliveryLines;

	$( document ).on( 'change', '.rbfw-delivery-toggle', function () {
		update( $( this ).closest( '.rbfw-delivery-block' ) );
	} );

	$( document ).on( 'input change', '.rbfw-delivery-distance, .rbfw-delivery-address', function () {
		update( $( this ).closest( '.rbfw-delivery-block' ) );
	} );

	/**
	 * Whether this form's delivery choice satisfies the shop's rules.
	 *
	 * Returns false and shows the reason; the caller stops the submission.
	 *
	 * @param {jQuery} $form
	 * @return {boolean}
	 */
	function deliveryIsValid( $form ) {
		var $block = $form.find( '.rbfw-delivery-block' );
		if ( ! $block.length ) { return true; }

		var $quote = $block.find( '.rbfw-delivery-quote' );
		var fail   = function ( $field, key, fallback ) {
			$quote.show().addClass( 'is-error' ).text( t( key, fallback ) );
			if ( $field && $field.length ) { $field.trigger( 'focus' ); }
			$( 'html, body' ).animate( { scrollTop: $block.offset().top - 90 }, 200 );
			return false;
		};

		var mode           = $block.attr( 'data-require-mode' ) || 'off';
		var hasDelivery    = $block.find( '[name="rbfw_delivery_wanted"]' ).length > 0;
		var hasCollection  = $block.find( '[name="rbfw_collection_wanted"]' ).length > 0;
		var wantDelivery   = legChosen( $block, 'rbfw_delivery_wanted' );
		var wantCollection = legChosen( $block, 'rbfw_collection_wanted' );
		var wants          = wantDelivery || wantCollection;

		// "Both" only demands a leg the shop actually offers — a checkbox that was never
		// rendered cannot be required, or nobody could book at all.
		if ( 'both' === mode ) {
			if ( ( hasDelivery && ! wantDelivery ) || ( hasCollection && ! wantCollection ) ) {
				return fail( null, 'needBoth', 'This rental is booked with delivery and collection together — please select both.' );
			}
		} else if ( ! wants ) {
			if ( 'any' === mode ) {
				return fail( null, 'needChoice', 'Please choose whether you would like delivery or collection.' );
			}
			return true;
		}

		var $distance = $block.find( '.rbfw-delivery-distance' );
		var maxKm     = num( $block.attr( 'data-max-distance' ) );
		var km        = num( $distance.val() );

		if ( ! $distance.val() || km < 0 ) {
			return fail( $distance, 'needDistance', 'Please choose how far you are from us.' );
		}
		if ( maxKm > 0 && km > maxKm ) {
			return fail( $distance, 'outOfRange', 'Sorry, we only deliver within %s km.' );
		}

		// Every field the shop marked required, in the order they appear, so the customer is
		// sent to the first thing they missed rather than the last.
		var required = [
			[ $block.find( '.rbfw-delivery-address' ), 'needAddress', 'Please enter the delivery address.' ],
			[ $block.find( '.rbfw-delivery-phone' ), 'needPhone', 'Please give us a contact number for the delivery.' ],
			[ $block.find( '.rbfw-delivery-note' ), 'needNote', 'Please add delivery notes so we can find you.' ]
		];

		for ( var i = 0; i < required.length; i++ ) {
			var $field = required[ i ][ 0 ];
			if ( $field.length && $field.attr( 'data-required' ) && ! $.trim( $field.val() ) ) {
				return fail( $field, required[ i ][ 1 ], required[ i ][ 2 ] );
			}
		}

		return true;
	}

	/**
	 * Stop the booking before anything else acts on it.
	 *
	 * stopImmediatePropagation, not just preventDefault: in Standalone mode
	 * rbfw_native_checkout.js has its own delegated handler on the SAME element, and jQuery's
	 * `return false` only prevents the default and stops BUBBLING — sibling handlers on
	 * document still run. Without this the checkout modal opened over the error and the
	 * customer went all the way to payment before the server refused them.
	 *
	 * This is a courtesy gate only. rbfw_delivery_validate_input() re-checks every rule on
	 * both checkout paths, so removing this in the browser changes nothing.
	 */
	function guard( e ) {
		var $form = $( e.currentTarget ).closest( '.mp_rbfw_ticket_form' );
		if ( ! $form.length ) { $form = $( e.target ).closest( '.mp_rbfw_ticket_form' ); }
		if ( ! $form.length || deliveryIsValid( $form ) ) { return; }

		e.preventDefault();
		e.stopImmediatePropagation();
	}

	// Registered before the standalone-checkout script binds its own handlers (the delivery
	// assets are enqueued first), so this runs first and can stop it.
	$( document ).on( 'submit', '.mp_rbfw_ticket_form', guard );
	$( document ).on( 'click', '.mp_rbfw_book_now_submit, [name="add-to-cart"], [type="submit"]', function ( e ) {
		if ( $( e.currentTarget ).closest( '.mp_rbfw_ticket_form' ).length ) { guard( e ); }
	} );

	/**
	 * Initialise any delivery block that has not been set up yet.
	 *
	 * On the step / timely flow the block is injected by AJAX AFTER DOM-ready, so a one-shot
	 * pass on load never saw it. That alone left the address and zone fields hidden — and
	 * with both legs LOCKED there is no change event coming either, because a disabled
	 * checkbox never fires one. The customer was told delivery was included and then given
	 * nowhere to say where they are.
	 *
	 * Marked per block so repeated AJAX refreshes cannot re-run it over a part-filled form.
	 */
	function initBlocks() {
		$( '.rbfw-delivery-block' ).each( function () {
			var $block = $( this );
			if ( $block.data( 'rbfwInit' ) ) { return; }
			$block.data( 'rbfwInit', true );
			update( $block );
		} );
	}

	$( initBlocks );

	// The booking panel is rebuilt over AJAX on every date change, bringing a fresh block
	// with it. Deferred slightly so the new markup is in the DOM before we look for it.
	$( document ).ajaxComplete( function () {
		window.setTimeout( initBlocks, 50 );
	} );

} )( jQuery );
