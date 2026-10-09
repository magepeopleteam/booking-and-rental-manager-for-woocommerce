/**
 * SecureHold WP compatibility for the booking forms.
 *
 * When SecureHold holds a rental's security deposit on the customer's card, the
 * cart no longer adds that deposit to the payable total. The form calculators add
 * it from the hidden rbfw_security_deposit_enable field, so switch that field off
 * for every form of an item that shows the card-hold note. Runs from the footer,
 * before any calculator's ready handler.
 */
( function () {
	'use strict';

	var held = {};
	var notes = document.querySelectorAll( '.rbfw-securehold-deposit-note[data-rbfw-item]' );
	Array.prototype.forEach.call( notes, function ( note ) {
		held[ note.getAttribute( 'data-rbfw-item' ) ] = true;
	} );

	Array.prototype.forEach.call( document.querySelectorAll( 'form.mp_rbfw_ticket_form' ), function ( form ) {
		var item = form.querySelector( '[name="rbfw_post_id"]' );
		var deposit = form.querySelector( '[name="rbfw_security_deposit_enable"]' );
		if ( item && deposit && held[ item.value ] ) {
			deposit.value = 'no';
		}
	} );
}() );
