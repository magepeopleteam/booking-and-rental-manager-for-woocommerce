/**
 * Categories admin page: header stats are server-rendered (no JS needed),
 * everything below is client-side over the already-rendered card grid — no
 * round trip for search, filter pills, sort, view toggle, or pagination.
 * Add/Edit/Delete go over AJAX. Entirely self-contained (own media-picker
 * wiring via wp.media()) — no dependency on any theme script.
 */
( function () {
	'use strict';

	if ( 'undefined' === typeof rbfwCategoryManager ) {
		return;
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var grid = document.getElementById( 'rbfw-cat-grid' );
		var modal = document.getElementById( 'rbfw-cat-modal' );
		if ( ! modal ) {
			return;
		}

		var form = document.getElementById( 'rbfw-cat-form' );
		var title = document.getElementById( 'rbfw-cat-modal-title' );
		var errorBox = document.getElementById( 'rbfw-cat-modal-error' );
		var submitBtn = document.getElementById( 'rbfw-cat-modal-submit' );
		var perPage = parseInt( rbfwCategoryManager.perPage, 10 ) || 15;

		var fields = {
			termId: document.getElementById( 'rbfw-cat-term-id' ),
			name: document.getElementById( 'rbfw-cat-name' ),
			slug: document.getElementById( 'rbfw-cat-slug' ),
			description: document.getElementById( 'rbfw-cat-description' ),
			imageId: document.getElementById( 'rbfw-cat-image-id' ),
		};
		var imagePreview = document.getElementById( 'rbfw-cat-image-preview' );
		var imageSelectBtn = document.getElementById( 'rbfw-cat-image-select' );
		var imageRemoveBtn = document.getElementById( 'rbfw-cat-image-remove' );
		var imagePreviewEmptyHtml = '<span class="dashicons dashicons-format-image" aria-hidden="true"></span>';

		/* ---------------------------------------------------------------
		 * Media picker — same wp.media() pattern as any other admin image
		 * field, just self-contained here instead of relying on a theme script.
		 * ------------------------------------------------------------- */

		if ( imageSelectBtn ) {
			imageSelectBtn.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				var frame = wp.media( {
					title: rbfwCategoryManager.selectImageTitle,
					multiple: false,
					library: { type: 'image' },
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					fields.imageId.value = attachment.id;
					imagePreview.innerHTML = '<img src="' + attachment.url + '" alt="" />';
					imageRemoveBtn.style.display = '';
				} );

				frame.open();
			} );
		}

		if ( imageRemoveBtn ) {
			imageRemoveBtn.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				fields.imageId.value = '';
				imagePreview.innerHTML = imagePreviewEmptyHtml;
				imageRemoveBtn.style.display = 'none';
			} );
		}

		/* ---------------------------------------------------------------
		 * Add/Edit modal
		 * ------------------------------------------------------------- */

		function resetForm() {
			form.reset();
			fields.termId.value = '0';
			fields.imageId.value = '';
			imagePreview.innerHTML = imagePreviewEmptyHtml;
			imageRemoveBtn.style.display = 'none';
			hideError();
		}

		function showError( message ) {
			errorBox.textContent = message;
			errorBox.hidden = false;
		}

		function hideError() {
			errorBox.hidden = true;
			errorBox.textContent = '';
		}

		function openModal() {
			modal.hidden = false;
			document.body.classList.add( 'rbfw-cat-modal-open' );
			window.setTimeout( function () {
				fields.name.focus();
			}, 50 );
		}

		function closeModal() {
			modal.hidden = true;
			document.body.classList.remove( 'rbfw-cat-modal-open' );
		}

		function openForAdd() {
			resetForm();
			title.textContent = rbfwCategoryManager.i18nAddTitle || 'Add New Category';
			openModal();
		}

		function openForEdit( term ) {
			resetForm();
			title.textContent = rbfwCategoryManager.i18nEditTitle || 'Edit Category';

			fields.termId.value = term.id;
			fields.name.value = term.name || '';
			fields.slug.value = term.slug || '';
			fields.description.value = term.description || '';
			fields.imageId.value = term.imageId || '';

			if ( term.imageUrl ) {
				imagePreview.innerHTML = '<img src="' + term.imageUrl + '" alt="" />';
				imageRemoveBtn.style.display = '';
			}

			openModal();
		}

		document.getElementById( 'rbfw-cat-add-btn' ).addEventListener( 'click', openForAdd );
		document.getElementById( 'rbfw-cat-modal-close' ).addEventListener( 'click', closeModal );
		document.getElementById( 'rbfw-cat-modal-cancel' ).addEventListener( 'click', closeModal );
		document.getElementById( 'rbfw-cat-modal-backdrop' ).addEventListener( 'click', closeModal );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' !== event.key ) {
				return;
			}
			if ( ! modal.hidden ) {
				closeModal();
				return;
			}
			closeAllCardMenus();
		} );

		/* ---------------------------------------------------------------
		 * Card actions: edit, delete, the "…" menu
		 * ------------------------------------------------------------- */

		function closeAllCardMenus() {
			grid.querySelectorAll( '.rbfw-cat-card__menu-panel' ).forEach( function ( panel ) {
				panel.hidden = true;
				var btn = panel.previousElementSibling;
				if ( btn ) {
					btn.setAttribute( 'aria-expanded', 'false' );
				}
			} );
		}

		document.addEventListener( 'click', function ( event ) {
			if ( ! event.target.closest( '.rbfw-cat-card__menu' ) ) {
				closeAllCardMenus();
			}
		} );

		if ( grid ) {
			grid.addEventListener( 'click', function ( event ) {
				var menuBtn = event.target.closest( '.rbfw-cat-card__menu-btn' );
				var editBtn = event.target.closest( '.rbfw-cat-card__edit' );
				var deleteBtn = event.target.closest( '.rbfw-cat-card__delete' );

				if ( menuBtn ) {
					var panel = menuBtn.nextElementSibling;
					var willOpen = panel.hidden;
					closeAllCardMenus();
					if ( willOpen ) {
						panel.hidden = false;
						menuBtn.setAttribute( 'aria-expanded', 'true' );
					}
					return;
				}

				if ( editBtn ) {
					var card = editBtn.closest( '.rbfw-cat-card' );
					try {
						openForEdit( JSON.parse( card.dataset.term ) );
					} catch ( e ) {
						openForAdd();
					}
					return;
				}

				if ( deleteBtn ) {
					handleDelete( deleteBtn.closest( '.rbfw-cat-card' ) );
				}
			} );
		}

		function handleDelete( card ) {
			var termId = card.dataset.termId;
			if ( ! window.confirm( rbfwCategoryManager.i18nConfirm ) ) {
				return;
			}

			var body = new URLSearchParams();
			body.set( 'action', 'rbfw_category_manager_delete' );
			body.set( 'nonce', rbfwCategoryManager.nonce );
			body.set( 'term_id', termId );

			card.style.pointerEvents = 'none';

			window.fetch( rbfwCategoryManager.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body,
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( response ) {
					if ( ! response || ! response.success ) {
						card.style.pointerEvents = '';
						window.alert( ( response && response.data && response.data.message ) || rbfwCategoryManager.i18nError );
						return;
					}
					card.classList.add( 'is-removing' );
					window.setTimeout( function () {
						card.remove();
						maybeShowEmptyState();
						refreshView();
					}, 150 );
				} )
				.catch( function () {
					card.style.pointerEvents = '';
					window.alert( rbfwCategoryManager.i18nError );
				} );
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			hideError();

			if ( ! fields.name.value.trim() ) {
				showError( rbfwCategoryManager.i18nNameRequired );
				return;
			}

			var body = new URLSearchParams();
			body.set( 'action', 'rbfw_category_manager_save' );
			body.set( 'nonce', rbfwCategoryManager.nonce );
			body.set( 'term_id', fields.termId.value );
			body.set( 'name', fields.name.value );
			body.set( 'slug', fields.slug.value );
			body.set( 'description', fields.description.value );
			body.set( 'image_id', fields.imageId.value );

			submitBtn.disabled = true;
			submitBtn.textContent = rbfwCategoryManager.i18nSaving;

			window.fetch( rbfwCategoryManager.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body,
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( response ) {
					submitBtn.disabled = false;
					submitBtn.textContent = rbfwCategoryManager.i18nSave;

					if ( ! response || ! response.success ) {
						showError( ( response && response.data && response.data.message ) || rbfwCategoryManager.i18nError );
						return;
					}

					upsertCard( response.data.termId, response.data.cardHtml );
					closeModal();
				} )
				.catch( function () {
					submitBtn.disabled = false;
					submitBtn.textContent = rbfwCategoryManager.i18nSave;
					showError( rbfwCategoryManager.i18nError );
				} );
		} );

		function upsertCard( termId, cardHtml ) {
			var emptyState = grid.querySelector( '.rbfw-cat-grid__empty' );
			if ( emptyState ) {
				emptyState.remove();
			}

			var existing = document.getElementById( 'rbfw-cat-card-' + termId );
			var wrapper = document.createElement( 'div' );
			wrapper.innerHTML = cardHtml.trim();
			var newCard = wrapper.firstElementChild;

			if ( existing ) {
				existing.replaceWith( newCard );
			} else {
				grid.appendChild( newCard );
			}

			refreshView();
		}

		function maybeShowEmptyState() {
			if ( grid.querySelector( '.rbfw-cat-card' ) ) {
				return;
			}
			var empty = document.createElement( 'p' );
			empty.className = 'rbfw-cat-grid__empty';
			empty.textContent = rbfwCategoryManager.i18nEmpty;
			grid.appendChild( empty );
		}

		/* ---------------------------------------------------------------
		 * Search + filter pills + sort + pagination — one pipeline over the
		 * already-rendered cards. Visual order within the current page is set
		 * via CSS `order` rather than moving DOM nodes.
		 * ------------------------------------------------------------- */

		var searchInput = document.getElementById( 'rbfw-cat-search' );
		var filterPills = document.querySelectorAll( '.rbfw-cat-filter-pill' );
		var sortSelect = document.getElementById( 'rbfw-cat-sort' );
		var showingLabel = document.getElementById( 'rbfw-cat-showing' );
		var paginationEl = document.getElementById( 'rbfw-cat-pagination' );
		var noMatchRow = null;

		var state = {
			filter: 'all',
			page: 1,
		};

		filterPills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function () {
				filterPills.forEach( function ( p ) {
					p.classList.remove( 'is-active' );
				} );
				pill.classList.add( 'is-active' );
				state.filter = pill.dataset.filter;
				state.page = 1;
				refreshView();
			} );
		} );

		if ( searchInput ) {
			searchInput.addEventListener( 'input', function () {
				state.page = 1;
				refreshView();
			} );
		}

		if ( sortSelect ) {
			sortSelect.addEventListener( 'change', refreshView );
		}

		function getComparator() {
			switch ( sortSelect ? sortSelect.value : 'name-asc' ) {
				case 'name-desc':
					return function ( a, b ) {
						return b.dataset.termName.localeCompare( a.dataset.termName );
					};
				case 'count-desc':
					return function ( a, b ) {
						return parseInt( b.dataset.count, 10 ) - parseInt( a.dataset.count, 10 );
					};
				case 'count-asc':
					return function ( a, b ) {
						return parseInt( a.dataset.count, 10 ) - parseInt( b.dataset.count, 10 );
					};
				default:
					return function ( a, b ) {
						return a.dataset.termName.localeCompare( b.dataset.termName );
					};
			}
		}

		function refreshView() {
			var term = searchInput ? searchInput.value.trim().toLowerCase() : '';
			var cards = Array.prototype.slice.call( grid.querySelectorAll( '.rbfw-cat-card' ) );

			var matches = cards.filter( function ( card ) {
				if ( term && card.dataset.termName.indexOf( term ) === -1 ) {
					return false;
				}
				if ( 'with-items' === state.filter && '1' !== card.dataset.hasItems ) {
					return false;
				}
				if ( 'empty' === state.filter && '0' !== card.dataset.hasItems ) {
					return false;
				}
				return true;
			} );

			matches.sort( getComparator() );

			var totalPages = Math.max( 1, Math.ceil( matches.length / perPage ) );
			state.page = Math.min( state.page, totalPages );
			var start = ( state.page - 1 ) * perPage;
			var pageItems = matches.slice( start, start + perPage );
			var pageSet = new Set( pageItems );

			cards.forEach( function ( card ) {
				card.hidden = ! pageSet.has( card );
			} );
			pageItems.forEach( function ( card, index ) {
				card.style.order = index;
			} );

			if ( noMatchRow ) {
				noMatchRow.remove();
				noMatchRow = null;
			}
			if ( 0 === matches.length && cards.length > 0 ) {
				noMatchRow = document.createElement( 'p' );
				noMatchRow.className = 'rbfw-cat-grid__no-match';
				noMatchRow.textContent = rbfwCategoryManager.i18nNoMatch;
				grid.appendChild( noMatchRow );
			}

			renderShowingLabel( matches.length, start, pageItems.length );
			renderPagination( totalPages );
		}

		function renderShowingLabel( total, start, pageCount ) {
			if ( ! showingLabel ) {
				return;
			}
			if ( 0 === total ) {
				showingLabel.textContent = '';
				return;
			}
			var template = rbfwCategoryManager.i18nShowing || 'Showing %1$s to %2$s of %3$s categories';
			showingLabel.textContent = template
				.replace( '%1$s', String( start + 1 ) )
				.replace( '%2$s', String( start + pageCount ) )
				.replace( '%3$s', String( total ) );
		}

		function renderPagination( totalPages ) {
			if ( ! paginationEl ) {
				return;
			}
			paginationEl.innerHTML = '';
			if ( totalPages <= 1 ) {
				return;
			}

			var prev = document.createElement( 'button' );
			prev.type = 'button';
			prev.textContent = '‹';
			prev.disabled = 1 === state.page;
			prev.addEventListener( 'click', function () {
				state.page = Math.max( 1, state.page - 1 );
				refreshView();
			} );
			paginationEl.appendChild( prev );

			for ( var i = 1; i <= totalPages; i++ ) {
				( function ( pageNum ) {
					var btn = document.createElement( 'button' );
					btn.type = 'button';
					btn.textContent = String( pageNum );
					if ( pageNum === state.page ) {
						btn.classList.add( 'is-active' );
					}
					btn.addEventListener( 'click', function () {
						state.page = pageNum;
						refreshView();
					} );
					paginationEl.appendChild( btn );
				} )( i );
			}

			var next = document.createElement( 'button' );
			next.type = 'button';
			next.textContent = '›';
			next.disabled = state.page === totalPages;
			next.addEventListener( 'click', function () {
				state.page = Math.min( totalPages, state.page + 1 );
				refreshView();
			} );
			paginationEl.appendChild( next );
		}

		/* ---------------------------------------------------------------
		 * Grid / list view toggle
		 * ------------------------------------------------------------- */

		var viewButtons = document.querySelectorAll( '.rbfw-cat-view-toggle__btn' );
		viewButtons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				viewButtons.forEach( function ( b ) {
					b.classList.remove( 'is-active' );
					b.setAttribute( 'aria-pressed', 'false' );
				} );
				btn.classList.add( 'is-active' );
				btn.setAttribute( 'aria-pressed', 'true' );
				grid.classList.toggle( 'is-list-view', 'list' === btn.dataset.view );
			} );
		} );

		/* ---------------------------------------------------------------
		 * Deep-link support + initial render
		 * ------------------------------------------------------------- */

		if ( rbfwCategoryManager.openEditId ) {
			var targetCard = document.getElementById( 'rbfw-cat-card-' + rbfwCategoryManager.openEditId );
			if ( targetCard ) {
				try {
					openForEdit( JSON.parse( targetCard.dataset.term ) );
				} catch ( e ) {
					// Ignore — target card had no usable payload, just skip auto-open.
				}
			}
		}

		refreshView();
	} );
} )();
