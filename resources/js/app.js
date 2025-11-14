import '../css/app.css'
import Alpine from 'alpinejs'

// Initialize Alpine.js globally for Blade components using x-data/x-show
window.Alpine = Alpine
Alpine.start()

// SearchSelect component behavior: enhances div[data-ss]
(() => {
	if (window.__searchSelectInit) return;
	window.__searchSelectInit = true;

	const normalize = (s) => (s||'').toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g,'');

	const init = (root) => {
		if (!root || root.getAttribute('data-ss-bound') === '1') return; // skip already bound
		const variant = root.getAttribute('data-ss-variant') || 'input';
		const inline = root.getAttribute('data-ss-inline') === '1';
		// Elements differ per variant
		const textInput = root.querySelector('[data-ss-text]'); // always exists (input variant OR inside panel / inline button for dropdown)
		const displayBtn = root.querySelector('[data-ss-display]');
		const toggleBtn = root.querySelector('[data-ss-toggle]'); // only for input variant
		const displayTextEl = root.querySelector('[data-ss-display-text]');
		const panel = root.querySelector('[data-ss-panel]');
		const input = root.querySelector('[data-ss-input]');
		const list = root.querySelector('[data-ss-list]');
		const empty = root.querySelector('[data-ss-empty]');
		// Validate required controls
		const fail = (reason) => {
			root.setAttribute('data-ss-fail', reason);
			root.style.outline = '2px solid #dc2626';
			root.style.position = 'relative';
			if (!root.querySelector('.ss-fail-tag')) {
				const tag = document.createElement('div');
				tag.textContent = 'SS ERR';
				tag.className = 'ss-fail-tag';
				tag.style.cssText = 'position:absolute;top:-10px;right:-10px;background:#dc2626;color:#fff;font-size:10px;padding:2px 4px;border-radius:4px;z-index:9999;font-weight:600;';
				root.appendChild(tag);
			}
			console.warn('[searchable-select] init failed:', reason, root);
		};
		if (!panel || !input || !list) return fail('missing-core');
		if (variant === 'input' && (!textInput || !toggleBtn)) return fail('missing-input-controls');
		if (variant === 'dropdown' && (!displayBtn || !textInput)) return fail('missing-dropdown-controls'); // textInput inside panel OR inline
		root.setAttribute('data-ss-bound','1');

		const allOptions = Array.from(list.querySelectorAll('[data-ss-option]'));

		const triggerEl = variant === 'input' ? textInput : displayBtn; // element holding aria-expanded
		const open = () => {
			panel.classList.remove('hidden');
			triggerEl && triggerEl.setAttribute('aria-expanded','true');
			filter('');
			// Focus search box (textInput)
			setTimeout(() => textInput && textInput.focus(), 0);
			document.addEventListener('mousedown', onDoc);
		};
		const close = () => {
			if (panel.classList.contains('hidden')) return;
			panel.classList.add('hidden');
			triggerEl && triggerEl.setAttribute('aria-expanded','false');
			document.removeEventListener('mousedown', onDoc);
		};
		const onDoc = (e) => { if (!root.contains(e.target)) close(); };

		const selectValue = (val, text) => {
			input.value = val;
			if (variant === 'input') {
				if (typeof text === 'string') {
					textInput.value = text;
				} else if (!val) {
					textInput.value = '';
				}
			} else if (variant === 'dropdown') {
				if (displayTextEl) displayTextEl.textContent = (text || '') || (val ? String(val) : '');
			}
			allOptions.forEach(o => {
				const ov = o.getAttribute('data-value') || '';
				if (ov === val) {
					o.setAttribute('aria-selected','true');
					o.classList.add('bg-emerald-50','text-emerald-700');
				} else {
					o.setAttribute('aria-selected','false');
					o.classList.remove('bg-emerald-50','text-emerald-700');
				}
			});
			close();
		};

			const filter = (raw) => {
				const term = raw.trim();
				const t = normalize(term);
			let shown = 0;
			allOptions.forEach(opt => {
				const v = opt.getAttribute('data-value') || '';
				const txt = (opt.getAttribute('data-label') || opt.textContent || '').trim();
				const isPlaceholder = v === '';
				const ok = isPlaceholder || !t || normalize(txt).includes(t);
				opt.style.display = ok ? '' : 'none';
				if (ok && !isPlaceholder) shown++;
			});
			if (empty) empty.classList.toggle('hidden', shown !== 0);
		};


				// ===== Dynamic Search Dropdown (no initial data, fetch on demand) =====
				const dynamicInit = (root) => {
					if (!root || root.getAttribute('data-dsd-bound') === '1') return;
					const endpoint = root.getAttribute('data-dsd-endpoint');
					const labelField = root.getAttribute('data-dsd-label-field') || 'name';
					const valueField = root.getAttribute('data-dsd-value-field') || 'id';
					const minChars = parseInt(root.getAttribute('data-dsd-min-chars')||'0',10);
					const debounceMs = parseInt(root.getAttribute('data-dsd-debounce')||'180',10);
					const btn = root.querySelector('[data-dsd-display]');
					const panel = root.querySelector('[data-dsd-panel]');
					const search = root.querySelector('[data-dsd-search]');
					const list = root.querySelector('[data-dsd-list]');
					const hidden = root.querySelector('[data-dsd-input]');
					const empty = root.querySelector('[data-dsd-empty]');
					const loading = root.querySelector('[data-dsd-loading]');
					const errorEl = root.querySelector('[data-dsd-error]');
					const displayText = root.querySelector('[data-dsd-display-text]');
					if (!endpoint || !btn || !panel || !search || !list || !hidden) {
						root.setAttribute('data-dsd-fail','missing-core');
						root.style.outline = '2px solid #dc2626';
						return;
					}
					root.setAttribute('data-dsd-bound','1');
					// Prefill if initial value present (server-side rendered)
					if (root.getAttribute('data-dsd-initial') === '1' && hidden.value) {
						displayText.classList.remove('opacity-70');
					}
					let open = false; let currentIndex = -1; let abortCtrl = null; let lastQuery=''; let lastFetchId=0;
					const opts = () => Array.from(list.querySelectorAll('[data-dsd-option]'));
					const close = () => { if (!open) return; open=false; panel.classList.add('hidden'); btn.setAttribute('aria-expanded','false'); currentIndex=-1; root.removeAttribute('data-dsd-open'); };
					const openPanel = () => { if (open) return; open=true; panel.classList.remove('hidden'); btn.setAttribute('aria-expanded','true'); root.setAttribute('data-dsd-open','1'); if(!list.children.length) fetchData(''); search.focus(); };
					btn.addEventListener('click', e => { e.preventDefault(); open?close():openPanel(); });
					document.addEventListener('mousedown', e => { if (!root.contains(e.target)) close(); });
					btn.addEventListener('keydown', e => { if(['Enter',' '].includes(e.key)){ e.preventDefault(); open?close():openPanel(); } if(e.key==='ArrowDown'){ e.preventDefault(); openPanel(); move(1);} });
					const move = (delta) => {
						const options = opts(); if(!options.length) return; currentIndex = Math.max(0, Math.min(options.length-1, currentIndex+delta)); options.forEach((o,i)=>{ if(i===currentIndex){ o.classList.add('bg-emerald-50'); o.scrollIntoView({block:'nearest'}); } else { o.classList.remove('bg-emerald-50'); } });
					};
					search.addEventListener('keydown', e => {
						if(e.key==='ArrowDown'){ e.preventDefault(); move(1); }
						else if(e.key==='ArrowUp'){ e.preventDefault(); move(-1); }
						else if(e.key==='Enter'){ e.preventDefault(); const options=opts(); const target = options[currentIndex]||options[0]; if(target) select(target); }
						else if(e.key==='Escape'){ e.preventDefault(); close(); btn.focus(); }
					});
					const select = (el) => {
						const value = el.getAttribute('data-value');
						const label = el.getAttribute('data-label');
						hidden.value = value; displayText.textContent = label; displayText.classList.remove('opacity-70'); close(); btn.focus();
					};
					list.addEventListener('click', e => { const opt=e.target.closest('[data-dsd-option]'); if(opt) select(opt); });
					const render = (items) => {
						list.innerHTML=''; currentIndex=-1; items.forEach(it => {
							const li=document.createElement('li'); li.setAttribute('data-dsd-option',''); li.setAttribute('role','option'); li.setAttribute('data-value', it[valueField]); li.setAttribute('data-label', it[labelField] || it.display || it.name || it.id);
							li.className='cursor-pointer px-3 py-2 hover:bg-emerald-50';
							li.textContent = it[labelField] || it.display || it.name || it.id;
							list.appendChild(li);
						});
						empty.classList.toggle('hidden', !!items.length);
					};
					const setState = (phase) => {
						loading.classList.add('hidden'); errorEl.classList.add('hidden');
						if(phase==='loading') loading.classList.remove('hidden');
						else if(phase==='error') errorEl.classList.remove('hidden');
					};
					const debounced = (() => { let t=null; return (fn) => { clearTimeout(t); t=setTimeout(fn, debounceMs); }; })();
					const fetchData = (query) => {
						lastFetchId++; const fetchId=lastFetchId; lastQuery=query;
						if(abortCtrl) abortCtrl.abort(); abortCtrl = new AbortController();
						setState('loading');
						const url = new URL(endpoint, window.location.origin);
						if(query) url.searchParams.set('q', query);
						fetch(url.toString(), { headers:{'Accept':'application/json'}, signal: abortCtrl.signal })
							.then(r => r.ok ? r.json() : Promise.reject(new Error(r.status)))
							.then(json => { if(fetchId!==lastFetchId) return; const data = Array.isArray(json)?json:json.data||[]; render(data); setState(''); })
							.catch(err => { if(fetchId!==lastFetchId) return; if(err.name==='AbortError') return; console.warn('[dsd] fetch error', err); setState('error'); });
					};
					search.addEventListener('input', () => {
						const q = search.value.trim(); if(q.length < minChars){ render([]); empty.classList.add('hidden'); return; }
						debounced(()=> fetchData(q));
					});
					// Auto close on resize (panel might reposition)
					window.addEventListener('resize', () => { if(open) close(); });
				};

				const dynamicSetup = () => {
					document.querySelectorAll('[data-dsd]').forEach(dynamicInit);
				};
				window.initDynamicSearchDropdowns = dynamicSetup;
				dynamicSetup();
				const mo2 = new MutationObserver(muts => {
					muts.forEach(m => m.addedNodes.forEach(n => {
						if(n.nodeType===1){
							if(n.matches?.('[data-dsd]')) dynamicInit(n);
							else n.querySelectorAll?.('[data-dsd]').forEach(dynamicInit);
						}
					}));
				});
				mo2.observe(document.documentElement, { childList:true, subtree:true });
		// Open/close triggers differ
		if (variant === 'input') {
			toggleBtn.addEventListener('click', (e) => { e.preventDefault(); panel.classList.contains('hidden') ? open() : close(); });
			textInput.addEventListener('focus', () => { if (panel.classList.contains('hidden')) open(); });
			textInput.addEventListener('click', () => { if (panel.classList.contains('hidden')) open(); });
			textInput.addEventListener('input', () => filter(textInput.value));
			textInput.addEventListener('keydown', (e) => {
				if (e.key === 'ArrowDown') {
					e.preventDefault();
					const first = list.querySelector('[data-ss-option]:not([style*="display: none"])');
					first && first.focus();
				} else if (e.key === 'Escape') {
					close();
				}
			});
			textInput.addEventListener('blur', () => {
				setTimeout(() => {
					const ae = document.activeElement;
					if (!root.contains(ae)) close();
				}, 80);
			});
		} else if (variant === 'dropdown') {
			displayBtn.addEventListener('click', (e) => { e.preventDefault(); panel.classList.contains('hidden') ? open() : close(); });
			// Inline: open when input gains focus (user expects clicking into field to drop list)
			if (inline) {
				textInput.addEventListener('focus', () => { if (panel.classList.contains('hidden')) open(); });
			}
			textInput.addEventListener('input', () => {
				if (inline && panel.classList.contains('hidden')) open();
				filter(textInput.value);
			});
			textInput.addEventListener('keydown', (e) => {
				if (e.key === 'ArrowDown') {
					e.preventDefault();
					if (panel.classList.contains('hidden')) open();
					const first = list.querySelector('[data-ss-option]:not([style*="display: none"])');
					first && first.focus();
				} else if (e.key === 'Escape') {
					close();
					if (!inline) displayBtn.focus();
				} else if ((e.key === 'Enter' || e.key === ' ') && inline && panel.classList.contains('hidden')) {
					// Enter or Space with closed panel in inline mode should open, not submit form
					e.preventDefault();
					open();
				} else if (e.key === 'Enter' && !inline && !panel.classList.contains('hidden')) {
					// In non-inline mode, Enter while list open and input focused should select first visible
					const first = list.querySelector('[data-ss-option]:not([style*="display: none"])');
					if (first) {
						const val = first.getAttribute('data-value') || '';
						const txt = (first.getAttribute('data-label') || first.textContent || '').trim();
						selectValue(val, txt);
					}
				}
			});
			// Allow opening via keyboard on display wrapper (non-inline) or container (inline)
			displayBtn.addEventListener('keydown', (e) => {
				if ((e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') && panel.classList.contains('hidden')) {
					e.preventDefault();
					open();
					if (e.key === 'ArrowDown') {
						const first = list.querySelector('[data-ss-option]:not([style*="display: none"])');
						first && first.focus();
					}
				}
			});
			if (inline) {
				// allow clicking the chevron to toggle without losing input focus
				const inlineToggle = root.querySelector('[data-ss-inline-toggle]');
				inlineToggle && inlineToggle.addEventListener('click', (e) => { e.preventDefault(); panel.classList.contains('hidden') ? open() : close(); });
			}
			// DEBUG (temporary) - mark init & clicks
			if (!root.hasAttribute('data-ss-debug')) {
				root.setAttribute('data-ss-debug','1');
				console.log('[searchable-select] init variant='+variant+' inline='+inline, root);
				panel.addEventListener('transitionend', () => console.log('[searchable-select] panel transition end visible=' + !panel.classList.contains('hidden')));
			}
		}
		list.addEventListener('click', (e) => {
			const li = e.target.closest('[data-ss-option]');
			if (!li) return;
			const val = li.getAttribute('data-value') || '';
			const txt = (li.getAttribute('data-label') || li.textContent || '').trim();
			selectValue(val, txt);
		});
				// Make list items focusable for keyboard navigation and manage aria-activedescendant
				allOptions.forEach(o => o.setAttribute('tabindex','-1'));
				const setActive = (el) => {
					allOptions.forEach(o => o.classList.remove('ring','ring-emerald-400'));
					if (el) {
						textInput.setAttribute('aria-activedescendant', el.id || (el.id = 'opt-'+Math.random().toString(36).slice(2)));
						el.classList.add('ring','ring-emerald-400');
					}
				};
				list.addEventListener('keydown', (e) => {
					const current = document.activeElement.closest('[data-ss-option]');
					if (!current) return;
					if (e.key === 'ArrowDown') {
						e.preventDefault();
						let next = current.nextElementSibling;
						while (next && (next.style.display==='none' || !next.hasAttribute('data-ss-option'))) next = next.nextElementSibling;
						next && next.focus();
						setActive(next);
					} else if (e.key === 'ArrowUp') {
						e.preventDefault();
						let prev = current.previousElementSibling;
						while (prev && (prev.style.display==='none' || !prev.hasAttribute('data-ss-option'))) prev = prev.previousElementSibling;
						prev ? prev.focus() : textInput.focus();
						setActive(prev);
					} else if (e.key === 'Enter') {
						e.preventDefault();
						current.click();
					} else if (e.key === 'Escape') {
						e.preventDefault();
						close();
						textInput.focus();
					}
				});
				list.addEventListener('focusin', (e) => {
					const li = e.target.closest('[data-ss-option]');
					if (li) setActive(li);
				});
	};

	const setup = () => document.querySelectorAll('[data-ss]').forEach(init);
	// Observe future DOM mutations for dynamically added selects
	const mo = new MutationObserver((muts) => {
		for (const m of muts) {
			// Re-init for any added nodes
			m.addedNodes && m.addedNodes.forEach(n => {
				if (n.nodeType === 1) {
					if (n.matches && n.matches('[data-ss]')) init(n);
					n.querySelectorAll && n.querySelectorAll('[data-ss]').forEach(init);
				}
			});
			// If children of an existing root changed (Livewire partial diff), attempt rebind
			if (m.type === 'childList' && m.target && m.target.closest) {
				const parentRoot = m.target.closest('[data-ss]');
				if (parentRoot) {
					// Remove bound marker to force re-init if essential elements lost
					parentRoot.removeAttribute('data-ss-bound');
					init(parentRoot);
				}
			}
		}
	});
	mo.observe(document.documentElement, { childList: true, subtree: true });

	// Livewire integration: re-run setup after updates
	window.addEventListener('livewire:load', setup);
	window.addEventListener('livewire:update', () => {
		document.querySelectorAll('[data-ss]').forEach(r => r.removeAttribute('data-ss-bound'));
		setup();
	});
	// expose for manual re-init if needed
	window.initSearchSelects = setup;
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup, { once: true }); else setup();
})();

// Make plain selects searchable via keyboard
(() => {
	document.addEventListener('keydown', (e) => {
		if (!(e.target instanceof HTMLSelectElement)) return;
		const select = e.target;
		if (!select.multiple && (e.key.length === 1 || e.key === 'Backspace')) {
			if (!select.__searchTimeout) select.__searchText = '';
			clearTimeout(select.__searchTimeout);

			if (e.key === 'Backspace') {
				select.__searchText = select.__searchText.slice(0, -1);
			} else {
				select.__searchText += e.key.toLowerCase();
			}

			const normalize = (s) => (s||'').toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g,'');
			const searchNorm = normalize(select.__searchText);

			for (let opt of select.options) {
				const optText = normalize(opt.text);
				if (optText.startsWith(searchNorm) && opt.value) {
					select.value = opt.value;
					select.dispatchEvent(new Event('change', { bubbles: true }));
					break;
				}
			}

			select.__searchTimeout = setTimeout(() => {
				select.__searchText = '';
			}, 500);
		}
	});
})();
