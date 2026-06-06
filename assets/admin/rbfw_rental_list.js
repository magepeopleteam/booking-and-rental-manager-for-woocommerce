/* ==========================================================================
   RBFW Rental Items - list interactions
   View toggle, live search, custom type dropdown, status tabs, pagination.
   ========================================================================== */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var fleet = document.querySelector('.rbfw-fleet');
		if (!fleet) { return; }

		var grid     = document.getElementById('rbfwGrid');
		var table    = document.getElementById('rbfwTable');
		var gridBtn  = document.getElementById('rbfwGridBtn');
		var listBtn  = document.getElementById('rbfwListBtn');
		var searchEl = document.getElementById('rbfwSearchInput');
		var searchBox = searchEl ? searchEl.closest('.rbfw-search-box') : null;
		var clearBtn = document.getElementById('rbfwSearchClear');
		var typeEl   = document.getElementById('rbfwTypeFilter');
		var emptyMsg = document.getElementById('rbfwEmptyMsg');
		var pageInfo = document.getElementById('rbfwPageInfo');
		var pageBtns = document.getElementById('rbfwPageBtns');
		var tabs     = Array.prototype.slice.call(document.querySelectorAll('.rbfw-filter-pill'));

		if (!grid) { return; }

		var PER_PAGE = 12;
		var STORE_KEY = 'rbfwRentalListView';
		var cards = Array.prototype.slice.call(grid.querySelectorAll('.rbfw-card'));
		var rows  = table ? Array.prototype.slice.call(table.querySelectorAll('tr.rbfw-row')) : [];

		var state = { view: 'grid', search: '', type: '', status: '', page: 1 };

		/* ---- View toggle (persisted) ---------------------------------- */
		function applyView() {
			var isGrid = state.view === 'grid';
			grid.style.display = isGrid ? 'grid' : 'none';
			if (table) { table.style.display = isGrid ? 'none' : 'table'; }
			gridBtn.classList.toggle('active', isGrid);
			listBtn.classList.toggle('active', !isGrid);
		}
		function setView(view) {
			state.view = view;
			try { window.localStorage.setItem(STORE_KEY, view); } catch (e) {}
			applyView();
			render();
		}
		try {
			var saved = window.localStorage.getItem(STORE_KEY);
			if (saved === 'list' || saved === 'grid') { state.view = saved; }
		} catch (e) {}
		gridBtn.addEventListener('click', function () { setView('grid'); });
		listBtn.addEventListener('click', function () { setView('list'); });

		/* ---- Matching --------------------------------------------------- */
		function matches(el) {
			var name = (el.getAttribute('data-name') || '');
			var type = (el.getAttribute('data-type') || '');
			var status = (el.getAttribute('data-status') || '');
			if (state.search && name.indexOf(state.search) === -1) { return false; }
			if (state.type && type !== state.type) { return false; }
			if (state.status && status !== state.status) { return false; }
			return true;
		}

		/* ---- Render with pagination ------------------------------------ */
		function render() {
			var items = state.view === 'list' ? rows : cards;
			var matched = items.filter(matches);
			var total = matched.length;
			var pages = Math.max(1, Math.ceil(total / PER_PAGE));
			if (state.page > pages) { state.page = pages; }
			var start = (state.page - 1) * PER_PAGE;
			var end = start + PER_PAGE;

			items.forEach(function (el) { el.style.display = 'none'; });
			matched.forEach(function (el, i) { if (i >= start && i < end) { el.style.display = ''; } });

			if (emptyMsg) { emptyMsg.style.display = total === 0 ? 'block' : 'none'; }
			renderPageInfo(total, start, end);
			renderPageButtons(pages);
		}
		function renderPageInfo(total, start, end) {
			if (!pageInfo) { return; }
			if (total === 0) { pageInfo.textContent = '0 items'; return; }
			pageInfo.textContent = 'Showing ' + (start + 1) + '-' + Math.min(end, total) + ' of ' + total + ' items';
		}
		function makeBtn(label, page, opts) {
			opts = opts || {};
			var btn = document.createElement('button');
			btn.className = 'rbfw-page-btn' + (opts.active ? ' active' : '');
			btn.innerHTML = label;
			if (opts.disabled) { btn.disabled = true; }
			else {
				btn.addEventListener('click', function () {
					state.page = page; render();
					fleet.scrollIntoView({ behavior: 'smooth', block: 'start' });
				});
			}
			return btn;
		}
		function renderPageButtons(pages) {
			if (!pageBtns) { return; }
			pageBtns.innerHTML = '';
			if (pages <= 1) { return; }
			pageBtns.appendChild(makeBtn('&#8249;', state.page - 1, { disabled: state.page === 1 }));
			for (var p = 1; p <= pages; p++) {
				pageBtns.appendChild(makeBtn(String(p), p, { active: p === state.page }));
			}
			pageBtns.appendChild(makeBtn('&#8250;', state.page + 1, { disabled: state.page === pages }));
		}

		function resetAndRender() { state.page = 1; render(); }

		/* ---- Live search ------------------------------------------------ */
		if (searchEl) {
			var onSearch = function () {
				state.search = searchEl.value.toLowerCase().trim();
				if (searchBox) { searchBox.classList.toggle('has-value', searchEl.value.length > 0); }
				resetAndRender();
			};
			searchEl.addEventListener('input', onSearch);
			searchEl.addEventListener('search', onSearch);
			if (searchBox) {
				searchEl.addEventListener('focus', function () { searchBox.classList.add('is-focused'); });
				searchEl.addEventListener('blur', function () { searchBox.classList.remove('is-focused'); });
			}
			if (clearBtn) {
				clearBtn.addEventListener('click', function () { searchEl.value = ''; searchEl.focus(); onSearch(); });
			}
		}

		/* ---- Custom styled dropdown (type filter) ---------------------- */
		function buildDropdown(select) {
			var options = Array.prototype.slice.call(select.options);
			var wrap = document.createElement('div');
			wrap.className = 'rbfw-dropdown';
			var toggle = document.createElement('button');
			toggle.type = 'button';
			toggle.className = 'rbfw-dropdown-toggle';
			toggle.setAttribute('aria-haspopup', 'listbox');
			var label = document.createElement('span');
			label.textContent = options[select.selectedIndex] ? options[select.selectedIndex].text : '';
			var caret = document.createElement('span');
			caret.className = 'rbfw-caret';
			caret.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>';
			toggle.appendChild(label);
			toggle.appendChild(caret);
			var menu = document.createElement('div');
			menu.className = 'rbfw-dropdown-menu';
			menu.setAttribute('role', 'listbox');
			var check = '<span class="rbfw-check"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>';
			options.forEach(function (opt) {
				var item = document.createElement('div');
				item.className = 'rbfw-dropdown-option' + (opt.value === select.value ? ' selected' : '');
				item.setAttribute('role', 'option');
				item.innerHTML = '<span>' + opt.text + '</span>' + check;
				item.addEventListener('click', function () {
					select.value = opt.value;
					label.textContent = opt.text;
					menu.querySelectorAll('.rbfw-dropdown-option').forEach(function (o) { o.classList.remove('selected'); });
					item.classList.add('selected');
					wrap.classList.remove('open');
					state.type = opt.value;
					resetAndRender();
				});
				menu.appendChild(item);
			});
			toggle.addEventListener('click', function (e) { e.stopPropagation(); wrap.classList.toggle('open'); });
			document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) { wrap.classList.remove('open'); } });
			document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { wrap.classList.remove('open'); } });
			wrap.appendChild(toggle);
			wrap.appendChild(menu);
			select.parentNode.insertBefore(wrap, select.nextSibling);
		}
		if (typeEl) { buildDropdown(typeEl); }

		/* ---- Status tabs ------------------------------------------------ */
		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				tabs.forEach(function (t) { t.classList.remove('active'); });
				this.classList.add('active');
				state.status = this.getAttribute('data-status') || '';
				resetAndRender();
			});
		});

		applyView();
		render();
	});
})();
