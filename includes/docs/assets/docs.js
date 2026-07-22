/**
 * Rental Docs — self-contained admin behaviour (no dependencies, no network).
 *
 * Live search (contextual, case-insensitive substring) with safe DOM
 * highlighting, empty-block collapsing, accordions, copy buttons and
 * scrollspy nav. All matching is client-side; nothing is sent to the server.
 * Highlighting builds text nodes only (never innerHTML), so no markup can be
 * injected from the query or the documentation strings.
 */
(function () {
	'use strict';

	var root = document.querySelector('.rbfw-docs');
	if (!root) { return; }

	var search    = document.getElementById('rbfw-docs-search');
	var clearBtn  = document.getElementById('rbfw-docs-search-clear');
	var noResults = document.getElementById('rbfw-docs-noresults');
	var navlinks  = toArray(root.querySelectorAll('.rbfw-docs-navlink'));
	var sections  = toArray(root.querySelectorAll('.rbfw-doc-section'));
	var accs      = toArray(root.querySelectorAll('.rbfw-doc-acc'));
	var allEntry  = toArray(root.querySelectorAll('.rbfw-doc-entry'));

	// Leaf entries: searchable units that are NOT an accordion shell.
	var leaves = allEntry.filter(function (el) { return !el.classList.contains('rbfw-doc-acc'); });

	// Group containers that own entry rows/cards/steps.
	var groups = toArray(root.querySelectorAll('.rbfw-doc-tablewrap, .rbfw-doc-cards, .rbfw-doc-flow, .rbfw-doc-fieldlist'));
	var subheads = toArray(root.querySelectorAll('.rbfw-doc-h3'));
	var leads = toArray(root.querySelectorAll('.rbfw-doc-lead, .rbfw-doc-section > .rbfw-doc-muted'));

	function toArray(nl) { return Array.prototype.slice.call(nl); }
	function accIsUnit(acc) { return !acc.querySelector('.rbfw-doc-entry:not(.rbfw-doc-acc)'); }

	function closest(el, sel) {
		while (el && el !== document) {
			if (el.nodeType === 1 && el.matches(sel)) { return el; }
			el = el.parentNode;
		}
		return null;
	}

	/* -------- Build contextual haystacks (section + heading + tab) -------- */
	function sectionChild(el, sec) {
		var n = el;
		while (n && n.parentNode !== sec) { n = n.parentNode; }
		return n;
	}
	function precedingH3(el, sec) {
		var child = sectionChild(el, sec);
		if (!child) { return null; }
		var p = child.previousElementSibling;
		while (p) {
			if (p.classList && p.classList.contains('rbfw-doc-h3')) { return p; }
			p = p.previousElementSibling;
		}
		return null;
	}
	function textOf(el) { return el ? (el.textContent || '') : ''; }

	allEntry.forEach(function (el) {
		var parts = [ el.getAttribute('data-search') || '' ];
		var sec   = closest(el, '.rbfw-doc-section');
		if (sec) {
			parts.push(textOf(sec.querySelector('.rbfw-doc-h2')));
			parts.push(textOf(precedingH3(el, sec)));
		}
		var acc = el.classList.contains('rbfw-doc-acc') ? null : closest(el, '.rbfw-doc-acc');
		if (acc) { parts.push(textOf(acc.querySelector('.rbfw-doc-acc-title'))); }
		el.setAttribute('data-haystack', parts.join(' ').replace(/\s+/g, ' ').toLowerCase());
	});

	/* ----------------------------- Clicks ----------------------------- */
	root.addEventListener('click', function (e) {
		var head = closest(e.target, '.rbfw-doc-acc-head');
		if (head) {
			var acc  = head.parentElement;
			var open = acc.classList.toggle('is-open');
			head.setAttribute('aria-expanded', open ? 'true' : 'false');
			return;
		}
		var copy = closest(e.target, '.rbfw-doc-copybtn');
		if (copy) { doCopy(copy); }
	});

	/* ----------------------------- Copy ------------------------------- */
	function doCopy(btn) {
		var code = btn.parentElement.querySelector('[data-copy]');
		if (!code) { return; }
		var text  = code.textContent;
		var label = btn.querySelector('.rbfw-doc-copytext');
		var orig  = label ? label.textContent : '';
		var done  = function () {
			btn.classList.add('is-copied');
			if (label) { label.textContent = 'Copied!'; }
			setTimeout(function () {
				btn.classList.remove('is-copied');
				if (label) { label.textContent = orig; }
			}, 1500);
		};
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(done, function () { fallbackCopy(text, done); });
		} else {
			fallbackCopy(text, done);
		}
	}
	function fallbackCopy(text, cb) {
		var ta = document.createElement('textarea');
		ta.value = text;
		ta.setAttribute('readonly', '');
		ta.style.position = 'fixed';
		ta.style.opacity  = '0';
		document.body.appendChild(ta);
		ta.select();
		try { document.execCommand('copy'); } catch (err) {}
		document.body.removeChild(ta);
		cb();
	}

	/* -------------------------- Highlighting -------------------------- */
	/* Rebuilds text nodes with <mark> using textContent only — no innerHTML,
	   no regex, so neither the query nor the source strings can inject markup. */
	function clearMarks(scope) {
		var marks = scope.querySelectorAll('mark.rbfw-doc-hl');
		for (var i = marks.length - 1; i >= 0; i--) {
			var m = marks[i], p = m.parentNode;
			if (!p) { continue; }
			p.replaceChild(document.createTextNode(m.textContent), m);
			p.normalize();
		}
	}
	function highlight(scope, q) {
		if (!scope || !q) { return; }
		var walker = document.createTreeWalker(scope, NodeFilter.SHOW_TEXT, null, false);
		var nodes = [], node;
		while ((node = walker.nextNode())) {
			var parent = node.parentNode;
			if (!parent || parent.nodeName === 'SCRIPT' || parent.nodeName === 'STYLE' || parent.nodeName === 'MARK') { continue; }
			if ((node.nodeValue || '').toLowerCase().indexOf(q) !== -1) { nodes.push(node); }
		}
		for (var i = 0; i < nodes.length; i++) { splitNode(nodes[i], q); }
	}
	function splitNode(node, q) {
		var text = node.nodeValue, low = text.toLowerCase();
		var frag = document.createDocumentFragment();
		var idx = 0, pos;
		while ((pos = low.indexOf(q, idx)) !== -1) {
			if (pos > idx) { frag.appendChild(document.createTextNode(text.slice(idx, pos))); }
			var mark = document.createElement('mark');
			mark.className = 'rbfw-doc-hl';
			mark.textContent = text.slice(pos, pos + q.length);
			frag.appendChild(mark);
			idx = pos + q.length;
		}
		if (idx < text.length) { frag.appendChild(document.createTextNode(text.slice(idx))); }
		if (node.parentNode) { node.parentNode.replaceChild(frag, node); }
	}

	/* ---------------------------- Search ------------------------------ */
	var HIDE  = 'rbfw-doc-hidden';
	var timer = null;

	function onSearch() {
		clearTimeout(timer);
		var q = (search.value || '').trim().toLowerCase();
		timer = setTimeout(function () { runSearch(q); }, 110);
	}

	function runSearch(q) {
		clearMarks(root);
		root.classList.toggle('is-searching', !!q);

		var perSection = {};
		sections.forEach(function (s) { perSection[s.getAttribute('data-section')] = 0; });

		// Leaf entries.
		leaves.forEach(function (el) {
			var hay   = el.getAttribute('data-haystack') || '';
			var match = !q || hay.indexOf(q) !== -1;
			el.classList.toggle(HIDE, !match);
			if (match && q) { highlight(el, q); }
			if (match) {
				var sec = closest(el, '.rbfw-doc-section');
				if (sec) { perSection[sec.getAttribute('data-section')]++; }
			}
		});

		// Accordions.
		accs.forEach(function (acc) {
			var hay      = acc.getAttribute('data-haystack') || '';
			var selfHit  = !!q && hay.indexOf(q) !== -1;
			var hasChild = !!acc.querySelector('.rbfw-doc-entry:not(.rbfw-doc-acc):not(.' + HIDE + ')');
			var isUnit   = accIsUnit(acc);
			var visible  = !q || selfHit || hasChild;

			acc.classList.toggle(HIDE, !visible);

			var head = acc.querySelector('.rbfw-doc-acc-head');
			if (q && visible) {
				acc.classList.add('is-open');
				if (head) { head.setAttribute('aria-expanded', 'true'); }
				if (selfHit) {
					highlight(isUnit ? acc : head, q);
				}
			} else if (!q) {
				acc.classList.remove('is-open');
				if (head) { head.setAttribute('aria-expanded', 'false'); }
			}

			if (visible && isUnit) {
				var sec = closest(acc, '.rbfw-doc-section');
				if (sec) { perSection[sec.getAttribute('data-section')]++; }
			}
		});

		collapseChrome(q);

		// Sections + nav counts.
		var total = 0;
		sections.forEach(function (s) {
			var id = s.getAttribute('data-section');
			var c  = perSection[id] || 0;
			total += c;
			s.classList.toggle(HIDE, !!q && c === 0);
			var link = root.querySelector('.rbfw-docs-navlink[data-target="' + id + '"]');
			if (link) {
				var badge = link.querySelector('.rbfw-docs-navcount');
				if (badge) { badge.textContent = c; }
				link.classList.toggle('rbfw-doc-nav-empty', !!q && c === 0);
			}
		});

		if (noResults) { noResults.hidden = !(q && total === 0); }
	}

	/* Hide headings / tables / card-grids / intros that have no live matches. */
	function collapseChrome(q) {
		groups.forEach(function (g) {
			var owned = g.querySelectorAll('.rbfw-doc-entry');
			if (!owned.length) { return; } // e.g. shortcode attr table (no entries)
			var vis = g.querySelectorAll('.rbfw-doc-entry:not(.' + HIDE + ')').length;
			g.classList.toggle(HIDE, !!q && vis === 0);
		});

		subheads.forEach(function (h) {
			if (!q) { h.classList.remove(HIDE); return; }
			var anyVisible = false, p = h.nextElementSibling;
			while (p && !(p.classList && p.classList.contains('rbfw-doc-h3'))) {
				if (p.classList && !p.classList.contains(HIDE)) {
					var owned = p.querySelectorAll ? p.querySelectorAll('.rbfw-doc-entry') : [];
					if (!owned.length || p.querySelectorAll('.rbfw-doc-entry:not(.' + HIDE + ')').length) {
						anyVisible = true;
						break;
					}
				}
				p = p.nextElementSibling;
			}
			h.classList.toggle(HIDE, !anyVisible);
		});

		leads.forEach(function (el) { el.classList.toggle(HIDE, !!q); });
	}

	function clearSearch() {
		search.value = '';
		runSearch('');
		search.focus();
	}

	if (search)   { search.addEventListener('input', onSearch); }
	if (clearBtn) { clearBtn.addEventListener('click', clearSearch); }
	if (search) {
		search.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { clearSearch(); }
		});
	}

	/* --------------------------- Nav / scroll ------------------------- */
	navlinks.forEach(function (link) {
		link.addEventListener('click', function (e) {
			var id  = link.getAttribute('data-target');
			var sec = document.getElementById('rbfw-sec-' + id);
			if (sec) {
				e.preventDefault();
				sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
				setActive(id);
			}
		});
	});

	function setActive(id) {
		navlinks.forEach(function (l) {
			l.classList.toggle('is-active', l.getAttribute('data-target') === id);
		});
	}

	if ('IntersectionObserver' in window) {
		var spy = new IntersectionObserver(function (obsEntries) {
			obsEntries.forEach(function (en) {
				if (en.isIntersecting) { setActive(en.target.getAttribute('data-section')); }
			});
		}, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });
		sections.forEach(function (s) { spy.observe(s); });
	}
}());
