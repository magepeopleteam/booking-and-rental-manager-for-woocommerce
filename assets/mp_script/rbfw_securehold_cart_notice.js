/**
 * Security deposit notice for the Cart and Checkout blocks.
 *
 * Renders the notices RBFW_SecureHold_Compat adds to the Store API cart
 * (extensions.rbfw_securehold.notices) in the order summary. They come with
 * every cart response, so the notice follows quantity changes and removals.
 */
( function () {
	'use strict';

	if ( ! window.wp || ! window.wp.element || ! window.wp.plugins || ! window.wc || ! window.wc.blocksCheckout ) {
		return;
	}

	var el = window.wp.element.createElement;
	var ExperimentalOrderMeta = window.wc.blocksCheckout.ExperimentalOrderMeta;

	function DepositNotices( props ) {
		var data = props.extensions && props.extensions.rbfw_securehold;
		var notices = data && Array.isArray( data.notices ) ? data.notices : [];
		if ( ! notices.length ) {
			return null;
		}

		return el(
			'div',
			{ className: 'rbfw-deposit-notices' },
			notices.map( function ( notice, index ) {
				return el(
					'div',
					{ key: notice.type + index, className: 'rbfw-securehold-deposit-note rbfw-deposit-notice-' + notice.type },
					el( 'i', { className: 'fas ' + ( 'held' === notice.type ? 'fa-lock' : 'fa-info-circle' ), 'aria-hidden': 'true' } ),
					el( 'span', { dangerouslySetInnerHTML: { __html: notice.html } } )
				);
			} )
		);
	}

	window.wp.plugins.registerPlugin( 'rbfw-securehold-deposit-notice', {
		render: function () {
			return el( ExperimentalOrderMeta, null, el( DepositNotices ) );
		},
		scope: 'woocommerce-checkout'
	} );
}() );
