<?php
/**
 * Shared ?debug=1 "Variant Settings" control panel.
 *
 * Included by inc/navbar.php and multi-quote/inc/navbar.php so the panel lives
 * in ONE place instead of being duplicated (and manually kept in sync).
 *
 * The including navbar must have already computed these in scope:
 *   $showVariantDebug, $themeControlValue, $debugLogoOptions, $logoDebugLabel,
 *   $logoVersion, $renderVariantOptions (callable), $activeOverrides,
 *   $defaultSwatchHex, $logoAssetDir.
 */

if (empty($showVariantDebug)) {
	return;
}

// Filesystem path that maps to the web root '/', derived from the img asset dir
// so logo filemtime lookups work from either navbar location.
$ahDebugRoot = dirname($logoAssetDir);

$ahColorFields = [
	'primary'   => 'Primary',
	'secondary' => 'Secondary',
	'accent'    => 'Accent',
	'tertiary'  => 'Tertiary',
];
$ahActiveTheme = isset($activeTheme) ? $activeTheme : 'default';
$ahThemeOptions = [
	'1'  => ['default', '1 default'],
	'2'  => ['logo-match', '2 logo-match'],
	'3'  => ['ohio-healthplans', '3 ohio-healthplans'],
	'4'  => ['golden-trust', 'Golden Trust'],
	'5'  => ['warm-orange', 'Warm Orange'],
	'6'  => ['modern-amber', 'Modern Amber'],
	'7'  => ['coral-red', 'Coral Red'],
	'8'  => ['strong-red', 'Strong Red'],
	'9'  => ['burnt-orange', 'Burnt Orange'],
	'10' => ['bright-healthcare', 'Bright Healthcare'],
	'11' => ['premium-copper', 'Premium Copper'],
];
?>
<style id="ah-debug-css">
	#ah-debug-panel{position:fixed;right:12px;bottom:12px;z-index:1300;width:min(374px,94vw);max-height:88vh;
		display:flex;flex-direction:column;color:#eaf1fb;
		background:linear-gradient(180deg,rgba(24,53,104,.98),rgba(16,37,78,.98));
		border:1px solid rgba(255,255,255,.12);border-radius:14px;
		font:600 12px/1.4 'Segoe UI',system-ui,sans-serif;letter-spacing:.15px;
		box-shadow:0 18px 48px rgba(3,12,32,.5);overflow:hidden;
		-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);}
	#ah-debug-panel *{box-sizing:border-box;}
	#ah-debug-panel.is-dragging{user-select:none;box-shadow:0 24px 60px rgba(3,12,32,.6);}
	#ah-debug-panel.is-collapsed{width:auto;max-height:none;}
	#ah-debug-panel.is-collapsed #ah-debug-toolbar,
	#ah-debug-panel.is-collapsed #ah-debug-grid,
	#ah-debug-panel.is-collapsed #ah-debug-actions{display:none;}
	#ah-debug-panel.is-collapsed .ahp-head{border-bottom:0;}

	.ahp-head{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 12px;
		cursor:grab;background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.1);touch-action:none;}
	.ahp-head:active{cursor:grabbing;}
	.ahp-title{display:flex;align-items:center;gap:8px;min-width:0;}
	.ahp-title strong{font-size:13px;font-weight:800;letter-spacing:.2px;white-space:nowrap;}
	.ahp-grip{opacity:.4;font-size:13px;letter-spacing:-3px;padding-right:2px;}
	.ahp-count{display:none;font-size:10px;font-weight:800;background:#f0a500;color:#3a2600;border-radius:10px;padding:1px 7px;}
	.ahp-count.on{display:inline-block;}
	.ahp-head-actions{display:flex;align-items:center;gap:6px;flex:0 0 auto;}
	.ahp-pill{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;opacity:.72;
		background:rgba(255,255,255,.12);border-radius:20px;padding:2px 8px;}
	.ahp-iconbtn{border:0;background:rgba(255,255,255,.12);color:#fff;width:24px;height:24px;border-radius:6px;
		cursor:pointer;font-size:14px;line-height:1;display:flex;align-items:center;justify-content:center;padding:0;}
	.ahp-iconbtn:hover{background:rgba(255,255,255,.24);}

	.ahp-toolbar{display:flex;flex-direction:column;gap:8px;padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.08);flex:0 0 auto;}
	.ahp-search{width:100%;padding:8px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.2);
		background:rgba(255,255,255,.96);color:#102b52;font:inherit;}
	.ahp-search:focus{outline:2px solid #24add4;outline-offset:1px;}
	.ahp-chips{display:flex;flex-wrap:wrap;gap:6px;}
	.ahp-chip{border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.08);color:#dbe7f7;
		font:700 11px 'Segoe UI',sans-serif;padding:5px 9px;border-radius:20px;cursor:pointer;white-space:nowrap;}
	.ahp-chip:hover{background:rgba(255,255,255,.16);}
	.ahp-chip.is-on{background:#24add4;border-color:#24add4;color:#04263c;}
	#ah-compliance-only.is-on{background:#e0563f;border-color:#e0563f;color:#fff;}

	#ah-debug-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;overflow-y:auto;flex:1 1 auto;min-height:0;padding:12px;}
	.ahp-field{display:flex;flex-direction:column;gap:5px;min-width:0;}
	.ahp-field.hide{display:none;}
	.ahp-field--wide{grid-column:1 / span 2;}
	.ahp-field-label{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;
		text-transform:uppercase;letter-spacing:.4px;opacity:.85;}
	.ahp-swatch{display:inline-block;width:12px;height:12px;border-radius:3px;border:1px solid rgba(255,255,255,.45);}
	.ahp-select{width:100%;padding:7px 8px;border-radius:8px;border:1px solid #89a;background:#fff;color:#102b52;font:inherit;}
	.ahp-empty{grid-column:1 / span 2;text-align:center;opacity:.6;font-weight:500;padding:14px 4px;}

	#ah-debug-actions{display:flex;flex-direction:column;gap:8px;padding:12px;flex:0 0 auto;
		border-top:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.14);}
	.ahp-savehint{font-size:11px;font-weight:600;color:#ffe0a3;background:rgba(240,165,0,.14);
		border:1px solid rgba(240,165,0,.35);border-radius:8px;padding:6px 9px;}
	.ahp-actions-row{display:flex;gap:8px;}
	.ahp-btn{flex:1;padding:9px 10px;border:0;border-radius:8px;font:800 12px 'Segoe UI',sans-serif;cursor:pointer;}
	.ahp-btn:hover{filter:brightness(1.06);}
	.ahp-btn-apply{background:#24add4;color:#04263c;}
	.ahp-btn-apply.is-dirty{animation:ahpPulse 1.5s ease-in-out infinite;}
	.ahp-btn-reset{background:#6fbf4a;color:#0f2e0e;}
	.ahp-btn-link{background:rgba(255,255,255,.14);color:#fff;width:100%;flex:none;}
	.ahp-btn:focus-visible,.ahp-chip:focus-visible,.ahp-iconbtn:focus-visible{outline:2px solid #7fd4ec;outline-offset:1px;}
	@keyframes ahpPulse{0%,100%{box-shadow:0 0 0 0 rgba(36,173,212,.55);}50%{box-shadow:0 0 0 6px rgba(36,173,212,0);}}
	@media (max-width:480px){#ah-debug-panel{width:94vw;}}
</style>
<div id="ah-debug-panel" role="region" aria-label="Variant settings control panel">
	<div class="ahp-head" id="ah-debug-head">
		<div class="ahp-title">
			<span class="ahp-grip" aria-hidden="true">&#8942;&#8942;</span>
			<strong>Variant Settings</strong>
			<span id="ah-active-count" class="ahp-count" title="Variants changed from control">0</span>
		</div>
		<div class="ahp-head-actions">
			<span class="ahp-pill">debug</span>
			<button id="ah-dock" class="ahp-iconbtn" type="button" title="Dock to the other side">&#8646;</button>
			<button id="ah-toggle" class="ahp-iconbtn" type="button" title="Collapse / expand (backtick key)" aria-label="Collapse or expand panel">&#8211;</button>
		</div>
	</div>
	<div id="ah-debug-toolbar" class="ahp-toolbar">
		<input id="ah-filter" class="ahp-search" type="search" placeholder="Filter settings&hellip;" autocomplete="off" aria-label="Filter settings">
		<div class="ahp-chips">
			<button id="ah-changed-only" class="ahp-chip" type="button" aria-pressed="false" title="Show only settings changed from control">&#9679; Changed</button>
			<button id="ah-compliance-only" class="ahp-chip" type="button" aria-pressed="false" title="Show only compliance-gated items">&#9888; Compliance</button>
			<button id="ah-expandall" class="ahp-chip" type="button" title="Expand / collapse all groups">Expand all</button>
		</div>
	</div>
	<div id="ah-debug-grid">
		<label class="ahp-field" data-search="theme" data-control="1">
			<span class="ahp-field-label">Theme</span>
			<select id="ah-theme" class="ahp-select">
<?php foreach ($ahThemeOptions as $ahThemeVal => $ahThemeMeta): ?>
				<option value="<?= $ahThemeVal ?>"<?= $ahActiveTheme === $ahThemeMeta[0] ? ' selected' : '' ?>><?= htmlspecialchars($ahThemeMeta[1], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
			</select>
		</label>
		<label class="ahp-field" data-search="logo" data-control="1">
			<span class="ahp-field-label">Logo</span>
			<select id="ah-logo" class="ahp-select">
				<?php foreach ($debugLogoOptions as $logoNumber => $debugLogoSrc): ?>
					<?php $logoOptVersion = @filemtime($ahDebugRoot . $debugLogoSrc) ?: $logoVersion; ?>
					<option value="<?= intval($logoNumber) ?>" data-img="<?= htmlspecialchars($debugLogoSrc, ENT_QUOTES, 'UTF-8') ?>?v=<?= $logoOptVersion ?>"<?= intval($logoDebugLabel) === intval($logoNumber) ? ' selected' : '' ?>>Logo <?= intval($logoNumber) ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php foreach ($ahColorFields as $ahKey => $ahLabel): ?>
			<label class="ahp-field" data-search="<?= strtolower($ahLabel) ?> color" data-control="0">
				<span class="ahp-field-label"><?= $ahLabel ?> <span id="ah-<?= $ahKey ?>-swatch" class="ahp-swatch"></span></span>
				<select id="ah-<?= $ahKey ?>" class="ahp-select"><?= $renderVariantOptions($ahKey, isset($activeOverrides[$ahKey]) ? $activeOverrides[$ahKey] : 0) ?></select>
			</label>
		<?php endforeach; ?>
		<label class="ahp-field ahp-field--wide" data-search="input border color" data-control="0">
			<span class="ahp-field-label">Input Border <span id="ah-input-swatch" class="ahp-swatch"></span></span>
			<select id="ah-input" class="ahp-select"><?= $renderVariantOptions('input-border', isset($activeOverrides['input-border']) ? $activeOverrides['input-border'] : 0) ?></select>
		</label>
		<?= function_exists('ah_experiment_panel_html') ? ah_experiment_panel_html() : '' ?>
		<div id="ah-empty" class="ahp-empty" hidden>No settings match your filter.</div>
	</div>
	<div id="ah-debug-actions">
		<div id="ah-savehint" class="ahp-savehint" hidden>Unsaved preview &mdash; Apply to persist to your session.</div>
		<div class="ahp-actions-row">
			<button id="ah-apply" class="ahp-btn ahp-btn-apply" type="button">Apply &amp; Save</button>
			<button id="ah-reset" class="ahp-btn ahp-btn-reset" type="button">Reset Overrides</button>
		</div>
		<button id="ah-copylink" class="ahp-btn ahp-btn-link" type="button" title="Copy a link that reproduces this exact setup">&#128279; Copy shareable link</button>
	</div>
</div>
<script>
	(function () {
		var panel = document.getElementById('ah-debug-panel');
		if (!panel) { return; }

		var head = document.getElementById('ah-debug-head');
		var grid = document.getElementById('ah-debug-grid');
		var actions = document.getElementById('ah-debug-actions');
		var toolbar = document.getElementById('ah-debug-toolbar');
		var applyBtn = document.getElementById('ah-apply');
		var resetBtn = document.getElementById('ah-reset');
		var toggleBtn = document.getElementById('ah-toggle');
		var dockBtn = document.getElementById('ah-dock');

		function lsGet(k, d) { try { var v = localStorage.getItem(k); return v === null ? d : v; } catch (e) { return d; } }
		function lsSet(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }

		var defaultSwatchHex = <?php echo json_encode($defaultSwatchHex, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
		var fields = {
			theme: document.getElementById('ah-theme'),
			logo: document.getElementById('ah-logo'),
			primary: document.getElementById('ah-primary'),
			secondary: document.getElementById('ah-secondary'),
			accent: document.getElementById('ah-accent'),
			tertiary: document.getElementById('ah-tertiary'),
			input: document.getElementById('ah-input')
		};
		var colorKeys = ['primary', 'secondary', 'accent', 'tertiary', 'input'];

		// ---- Collapse / expand (button + backtick shortcut) ----
		function setCollapsed(collapsed) {
			panel.classList.toggle('is-collapsed', collapsed);
			if (toggleBtn) toggleBtn.innerHTML = collapsed ? '&#43;' : '&#8211;';
			lsSet('ahDebugCollapsed', collapsed ? '1' : '0');
		}
		if (toggleBtn) {
			toggleBtn.addEventListener('click', function () { setCollapsed(!panel.classList.contains('is-collapsed')); });
		}
		setCollapsed(lsGet('ahDebugCollapsed', '0') === '1');
		document.addEventListener('keydown', function (e) {
			var t = e.target, tag = t && t.tagName;
			if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA' || (t && t.isContentEditable)) { return; }
			if (e.key === '`') { e.preventDefault(); setCollapsed(!panel.classList.contains('is-collapsed')); }
		});

		// ---- Position: dock corner + free drag (both persisted) ----
		function clampPos() {
			if (!panel.style.left) { return; }
			var r = panel.getBoundingClientRect();
			var maxL = Math.max(4, window.innerWidth - r.width - 4);
			var maxT = Math.max(4, window.innerHeight - 44);
			var l = parseFloat(panel.style.left) || 0, tp = parseFloat(panel.style.top) || 0;
			panel.style.left = Math.max(4, Math.min(l, maxL)) + 'px';
			panel.style.top = Math.max(4, Math.min(tp, maxT)) + 'px';
		}
		function applyDock(side) {
			panel.style.left = ''; panel.style.top = ''; panel.style.right = ''; panel.style.bottom = '';
			if (side === 'left') { panel.style.left = '12px'; } else { panel.style.right = '12px'; }
			panel.style.bottom = '12px';
			lsSet('ahDebugDock', side);
		}
		var savedPos = null;
		try { savedPos = JSON.parse(lsGet('ahDebugPos', 'null')); } catch (e) {}
		if (savedPos && typeof savedPos.left === 'number') {
			panel.style.right = ''; panel.style.bottom = '';
			panel.style.left = savedPos.left + 'px'; panel.style.top = savedPos.top + 'px';
			clampPos();
		} else {
			applyDock(lsGet('ahDebugDock', 'right'));
		}
		if (dockBtn) {
			dockBtn.addEventListener('click', function () {
				lsSet('ahDebugPos', 'null'); savedPos = null;
				applyDock(lsGet('ahDebugDock', 'right') === 'right' ? 'left' : 'right');
			});
		}
		(function () {
			var dragging = false, sx = 0, sy = 0, sl = 0, st = 0;
			head.addEventListener('pointerdown', function (e) {
				if (e.target.closest('button')) { return; }
				var r = panel.getBoundingClientRect();
				dragging = true; sx = e.clientX; sy = e.clientY; sl = r.left; st = r.top;
				panel.style.right = ''; panel.style.bottom = '';
				panel.style.left = r.left + 'px'; panel.style.top = r.top + 'px';
				panel.classList.add('is-dragging');
				try { head.setPointerCapture(e.pointerId); } catch (err) {}
				e.preventDefault();
			});
			head.addEventListener('pointermove', function (e) {
				if (!dragging) { return; }
				panel.style.left = (sl + e.clientX - sx) + 'px';
				panel.style.top = (st + e.clientY - sy) + 'px';
			});
			function endDrag() {
				if (!dragging) { return; }
				dragging = false;
				panel.classList.remove('is-dragging');
				clampPos();
				savedPos = { left: parseFloat(panel.style.left) || 0, top: parseFloat(panel.style.top) || 0 };
				lsSet('ahDebugPos', JSON.stringify(savedPos));
			}
			head.addEventListener('pointerup', endDrag);
			head.addEventListener('pointercancel', endDrag);
			head.addEventListener('dblclick', function (e) {
				if (e.target.closest('button')) { return; }
				lsSet('ahDebugPos', 'null'); savedPos = null; applyDock(lsGet('ahDebugDock', 'right'));
			});
		})();
		window.addEventListener('resize', clampPos);

		// ---- Color swatches + live preview ----
		function updateSwatch(key) {
			var select = fields[key], swatch = document.getElementById('ah-' + key + '-swatch');
			if (!select || !swatch) { return; }
			var option = select.options[select.selectedIndex];
			var hex = option ? option.getAttribute('data-hex') : '';
			if (!hex && defaultSwatchHex[key]) { hex = defaultSwatchHex[key]; }
			if (hex) { swatch.style.backgroundColor = hex; swatch.style.borderColor = 'rgba(255,255,255,.75)'; }
			else { swatch.style.backgroundColor = 'transparent'; swatch.style.borderColor = 'rgba(255,255,255,.45)'; }
		}
		var colorVarMap = { primary: '--color-primary', secondary: '--color-secondary', accent: '--color-accent', tertiary: '--color-tertiary', input: '--color-input-border' };
		function livePreviewColor(key) {
			var select = fields[key], mapKey = colorVarMap[key];
			if (!select || !mapKey) { return; }
			var opt = select.options[select.selectedIndex];
			var hex = opt ? opt.getAttribute('data-hex') : '';
			if (!hex && defaultSwatchHex[key]) { hex = defaultSwatchHex[key]; }
			if (hex) { document.body.style.setProperty(mapKey, hex); }
		}
		colorKeys.forEach(function (key) {
			if (!fields[key]) { return; }
			fields[key].addEventListener('change', function () { updateSwatch(key); livePreviewColor(key); });
			updateSwatch(key);
		});

		// ---- Build query params for Apply / Reset / share link ----
		function buildParams(resetOnly) {
			var params = new URLSearchParams(window.location.search);
			params.set('debug', '1');
			params.set('theme', fields.theme.value || '1');
			params.set('logo', fields.logo.value || '1');
			if (resetOnly) {
				params.set('reset_palette', '1');
				params.set('reset_experiments', '1');
				colorKeys.forEach(function (key) { params.set(key, '0'); });
			} else {
				params.delete('reset_palette');
				params.delete('reset_experiments');
				colorKeys.forEach(function (key) {
					var value = fields[key].value;
					if (value) { params.set(key, value); }
				});
				document.querySelectorAll('#ah-debug-panel [data-exp]').forEach(function (sel) {
					params.set('x_' + sel.id.replace('ahx-', ''), sel.value);
				});
			}
			return params;
		}
		applyBtn.addEventListener('click', function () { window.location.search = buildParams(false).toString(); });
		resetBtn.addEventListener('click', function () { window.location.search = buildParams(true).toString(); });

		// ---- Unsaved-preview indicator ----
		function snapshot() {
			var s = { theme: fields.theme.value, logo: fields.logo.value };
			colorKeys.forEach(function (k) { s[k] = fields[k].value; });
			document.querySelectorAll('#ah-debug-panel [data-exp]').forEach(function (sel) { s[sel.id] = sel.value; });
			return JSON.stringify(s);
		}
		var savedState = snapshot();
		var saveHint = document.getElementById('ah-savehint');
		function refreshDirty() {
			var dirty = snapshot() !== savedState;
			if (saveHint) { saveHint.hidden = !dirty; }
			applyBtn.classList.toggle('is-dirty', dirty);
		}
		panel.addEventListener('change', refreshDirty);

		// ---- Custom visual dropdowns for logo + color selects ----
		var openMenus = [];
		function optionVisual(option, variant) {
			var frag = document.createElement('span');
			frag.style.cssText = 'display:flex;align-items:center;gap:6px;overflow:hidden;white-space:nowrap;';
			if (variant === 'logo') {
				var img = document.createElement('img');
				img.src = option.getAttribute('data-img') || '';
				img.alt = option.textContent;
				img.style.cssText = 'min-height:20px;max-height:22px;max-width:150px;object-fit:contain;background:#fff;';
				img.onerror = function () { this.onerror = null; this.src = '/img/logo.svg'; };
				frag.appendChild(img);
			} else {
				var square = document.createElement('span');
				var hex = option.getAttribute('data-hex');
				square.style.cssText = 'display:inline-block;width:14px;height:14px;border-radius:3px;flex:0 0 auto;border:1px solid rgba(0,0,0,.25);';
				if (hex) { square.style.background = hex; }
				else { square.style.background = 'repeating-conic-gradient(#ccc 0% 25%, #fff 0% 50%) 50% / 8px 8px'; }
				var text = document.createElement('span');
				text.textContent = option.textContent;
				text.style.cssText = 'overflow:hidden;text-overflow:ellipsis;';
				frag.appendChild(square);
				frag.appendChild(text);
			}
			return frag;
		}
		function enhanceSelect(select, variant) {
			if (!select || select.dataset.enhanced) { return; }
			select.dataset.enhanced = '1';
			select.style.display = 'none';
			var wrap = document.createElement('div');
			wrap.style.cssText = 'position:relative;width:100%;';
			var trigger = document.createElement('button');
			trigger.type = 'button';
			trigger.style.cssText = 'width:100%;display:flex;align-items:center;justify-content:space-between;gap:6px;padding:7px 8px;border-radius:8px;border:1px solid #89a;background:#fff;color:#102b52;cursor:pointer;font:inherit;min-height:32px;';
			var triggerContent = document.createElement('span');
			triggerContent.style.cssText = 'display:flex;align-items:center;gap:6px;overflow:hidden;';
			var caret = document.createElement('span');
			caret.textContent = '\u25BE';
			caret.style.cssText = 'opacity:.6;flex:0 0 auto;';
			trigger.appendChild(triggerContent);
			trigger.appendChild(caret);
			var menu = document.createElement('div');
			menu.style.cssText = 'display:none;position:absolute;left:0;top:calc(100% + 4px);z-index:30;width:100%;max-height:210px;overflow:auto;background:#fff;border:1px solid #89a;border-radius:8px;box-shadow:0 8px 18px rgba(0,0,0,.25);padding:4px;';
			wrap.appendChild(trigger);
			wrap.appendChild(menu);
			select.parentNode.insertBefore(wrap, select.nextSibling);
			function renderTrigger() {
				triggerContent.innerHTML = '';
				var opt = select.options[select.selectedIndex];
				if (opt) { triggerContent.appendChild(optionVisual(opt, variant)); }
			}
			Array.prototype.forEach.call(select.options, function (opt, i) {
				var row = document.createElement('button');
				row.type = 'button';
				row.style.cssText = 'width:100%;display:flex;align-items:center;gap:6px;padding:6px;border:0;border-radius:6px;background:transparent;color:#102b52;cursor:pointer;text-align:left;font:inherit;';
				row.appendChild(optionVisual(opt, variant));
				row.addEventListener('mouseenter', function () { row.style.background = '#eef3fb'; });
				row.addEventListener('mouseleave', function () { row.style.background = 'transparent'; });
				row.addEventListener('click', function () {
					select.selectedIndex = i;
					select.dispatchEvent(new Event('change', { bubbles: true }));
					renderTrigger();
					menu.style.display = 'none';
				});
				menu.appendChild(row);
			});
			trigger.addEventListener('click', function (event) {
				event.stopPropagation();
				var isOpen = menu.style.display === 'block';
				openMenus.forEach(function (m) { m.style.display = 'none'; });
				menu.style.display = isOpen ? 'none' : 'block';
			});
			menu.addEventListener('click', function (event) { event.stopPropagation(); });
			openMenus.push(menu);
			select.addEventListener('change', renderTrigger);
			renderTrigger();
		}
		document.addEventListener('click', function () { openMenus.forEach(function (m) { m.style.display = 'none'; }); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { openMenus.forEach(function (m) { m.style.display = 'none'; }); } });
		enhanceSelect(fields.logo, 'logo');
		['primary', 'secondary', 'accent', 'tertiary', 'input'].forEach(function (key) { enhanceSelect(fields[key], 'color'); });

		// ---- Active counts (experiments changed from control) ----
		var activeCountEl = document.getElementById('ah-active-count');
		function refreshCounts() {
			var total = 0;
			document.querySelectorAll('#ah-debug-panel .ahx-group').forEach(function (g) {
				var n = 0;
				g.querySelectorAll('.ahx-item').forEach(function (it) { if (it.classList.contains('active')) { n++; } });
				total += n;
				var badge = g.querySelector('.ahx-gcount');
				if (badge) { badge.textContent = n + ' active'; badge.classList.toggle('on', n > 0); }
				g.classList.toggle('has-active', n > 0);
			});
			if (activeCountEl) { activeCountEl.textContent = total; activeCountEl.classList.toggle('on', total > 0); }
		}
		function markExp(sel) {
			var item = sel.closest('.ahx-item');
			if (item) { item.classList.toggle('active', sel.value !== sel.getAttribute('data-default')); }
		}

		// ---- Filter: text + "changed only" + "compliance only" ----
		var filter = document.getElementById('ah-filter');
		var complianceBtn = document.getElementById('ah-compliance-only');
		var changedBtn = document.getElementById('ah-changed-only');
		var emptyEl = document.getElementById('ah-empty');
		var complianceOnly = lsGet('ahDebugComplianceOnly', '0') === '1';
		var changedOnly = lsGet('ahDebugChangedOnly', '0') === '1';
		function isChangedEl(el) {
			if (el.classList.contains('ahx-item')) { return el.classList.contains('active'); }
			var sel = el.querySelector('select');
			return !!sel && sel.value !== el.getAttribute('data-control');
		}
		function matchEl(el) {
			var q = filter ? filter.value.trim().toLowerCase() : '';
			var textHit = !q || (el.getAttribute('data-search') || '').indexOf(q) !== -1;
			var compHit = !complianceOnly || el.getAttribute('data-compliance') === '1';
			var chgHit = !changedOnly || isChangedEl(el);
			return textHit && compHit && chgHit;
		}
		function applyFilter() {
			var anyVisible = false;
			var forceOpen = (filter && filter.value.trim()) || complianceOnly || changedOnly;
			document.querySelectorAll('#ah-debug-grid > .ahp-field').forEach(function (el) {
				var hit = matchEl(el);
				el.classList.toggle('hide', !hit);
				if (hit) { anyVisible = true; }
			});
			document.querySelectorAll('#ah-debug-panel .ahx-group').forEach(function (g) {
				var anyShown = false;
				g.querySelectorAll('.ahx-item').forEach(function (it) {
					var hit = matchEl(it);
					it.classList.toggle('hide', !hit);
					if (hit) { anyShown = true; anyVisible = true; }
				});
				g.style.display = anyShown ? '' : 'none';
				if (forceOpen && anyShown) { g.open = true; }
			});
			if (emptyEl) { emptyEl.hidden = anyVisible; }
		}
		function syncChip(btn, on, onBg) {
			if (!btn) { return; }
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
			btn.classList.toggle('is-on', on);
		}
		if (filter) {
			filter.addEventListener('input', function () { lsSet('ahDebugFilter', filter.value); applyFilter(); });
			var savedFilter = lsGet('ahDebugFilter', ''); if (savedFilter) { filter.value = savedFilter; }
		}
		if (complianceBtn) {
			complianceBtn.addEventListener('click', function () {
				complianceOnly = !complianceOnly; lsSet('ahDebugComplianceOnly', complianceOnly ? '1' : '0');
				syncChip(complianceBtn, complianceOnly); applyFilter();
			});
			syncChip(complianceBtn, complianceOnly);
		}
		if (changedBtn) {
			changedBtn.addEventListener('click', function () {
				changedOnly = !changedOnly; lsSet('ahDebugChangedOnly', changedOnly ? '1' : '0');
				syncChip(changedBtn, changedOnly); applyFilter();
			});
			syncChip(changedBtn, changedOnly);
		}

		// ---- Experiment selects: live body attr, counts, filter refresh, per-item reset ----
		document.querySelectorAll('#ah-debug-panel [data-exp]').forEach(function (sel) {
			sel.addEventListener('change', function () {
				document.body.setAttribute('data-x-' + sel.id.replace('ahx-', ''), sel.value);
				markExp(sel);
				refreshCounts();
				if (changedOnly) { applyFilter(); }
			});
		});
		document.querySelectorAll('#ah-debug-panel .ahx-reset').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var sel = document.getElementById(btn.getAttribute('data-target'));
				if (!sel) { return; }
				sel.value = sel.getAttribute('data-default');
				sel.dispatchEvent(new Event('change', { bubbles: true }));
			});
		});

		// ---- Expand / collapse all groups ----
		var expandAllBtn = document.getElementById('ah-expandall');
		if (expandAllBtn) {
			expandAllBtn.addEventListener('click', function () {
				var groups = document.querySelectorAll('#ah-debug-panel .ahx-group');
				var anyClosed = Array.prototype.some.call(groups, function (g) { return !g.open; });
				groups.forEach(function (g) { g.open = anyClosed; });
				expandAllBtn.textContent = anyClosed ? 'Collapse all' : 'Expand all';
			});
		}

		// ---- Copy shareable link ----
		var copyBtn = document.getElementById('ah-copylink');
		if (copyBtn) {
			copyBtn.addEventListener('click', function () {
				var url = window.location.origin + window.location.pathname + '?' + buildParams(false).toString();
				var done = function () { var t = copyBtn.innerHTML; copyBtn.innerHTML = '&#10003; Link copied'; setTimeout(function () { copyBtn.innerHTML = t; }, 1400); };
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(url).then(done, function () { window.prompt('Copy this link:', url); });
				} else { window.prompt('Copy this link:', url); }
			});
		}

		// ---- Persist each group's open/closed state ----
		(function () {
			var stored = {};
			try { stored = JSON.parse(lsGet('ahDebugGroups', '{}')) || {}; } catch (e) {}
			document.querySelectorAll('#ah-debug-panel .ahx-group').forEach(function (g) {
				var key = g.getAttribute('data-group');
				if (Object.prototype.hasOwnProperty.call(stored, key)) { g.open = !!stored[key]; }
				g.addEventListener('toggle', function () {
					try {
						var cur = JSON.parse(lsGet('ahDebugGroups', '{}')) || {};
						cur[key] = g.open;
						lsSet('ahDebugGroups', JSON.stringify(cur));
					} catch (e) {}
				});
			});
		})();

		// ---- Per-group reset ----
		document.querySelectorAll('#ah-debug-panel .ahx-greset').forEach(function (btn) {
			btn.addEventListener('click', function (event) {
				event.preventDefault();
				event.stopPropagation();
				var group = btn.closest('.ahx-group');
				if (!group) { return; }
				group.querySelectorAll('[data-exp]').forEach(function (sel) {
					if (sel.value !== sel.getAttribute('data-default')) {
						sel.value = sel.getAttribute('data-default');
						sel.dispatchEvent(new Event('change', { bubbles: true }));
					}
				});
			});
		});

		refreshCounts();
		applyFilter();
	})();
</script>
