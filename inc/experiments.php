<?php
/**
 * Shared experiment/variant registry for the ?debug=1 control panel.
 * Every checklist TEST or MOCK UP item is registered here so it becomes a
 * selectable, session-persisted variant without bespoke wiring.
 *
 * Usage in templates:  ah_experiment('main_headline')  => active variant key
 * Panel rendering:      echo ah_experiment_panel_html();
 * Query params:         x_<id>=<variant>   (x_<id>=default resets that one)
 * Reset all:            reset_experiments=1
 */

if (!defined('AH_EXPERIMENTS_LOADED')) {
	define('AH_EXPERIMENTS_LOADED', true);

	if (session_status() === PHP_SESSION_NONE) {
		@session_start();
	}

	/**
	 * Registry. Each entry:
	 *   group   => panel section heading
	 *   label   => control label
	 *   default => default variant key (the control/baseline)
	 *   options => [variant key => human label]
	 *   note    => optional gate/compliance reminder shown in the panel
	 */
	$GLOBALS['ah_experiments'] = [
		// C — Dynamic main headline
		'main_headline' => [
			'group' => 'Copy & Headlines',
			'label' => 'Main headline (C)',
			'default' => 'find_affordable',
			'options' => [
				'find_affordable' => 'Find Affordable Healthcare (control)',
				'ppo' => 'Find a PPO Plan',
				'hmo' => 'Find an HMO Plan',
				'fast_quotes' => 'Get healthcare quotes in under 30&nbsp;seconds',
				'free_quotes' => 'Find free healthcare quotes',
			],
		],
		// I — Homepage / form CTA language
		'hero_cta' => [
			'group' => 'Copy & Headlines',
			'label' => 'Hero CTA line (I)',
			'default' => 'dont_overpay',
			'options' => [
				'dont_overpay' => "Don't overpay for coverage again (control)",
				'get_30' => 'Get healthcare quotes in under 30&nbsp;seconds',
				'get_quotes_30' => 'Get quotes in under 30&nbsp;seconds',
				'affordable_30' => 'Affordable healthcare quotes in under 30&nbsp;seconds',
				'save_hundreds' => '20 seconds could save you hundreds monthly',
			],
			'note' => 'Timing/savings claims need compliance sign-off (I).',
		],
		'timing_claim' => [
			'group' => 'Copy & Headlines',
			'label' => 'Timing claim (I)',
			'default' => 'none',
			'options' => [
				'none' => 'No timing claim (control)',
				's10' => '10 seconds',
				's20' => '20 seconds',
				's30' => '30&nbsp;seconds',
			],
			'note' => 'COMPLIANCE: substantiate before publishing.',
		],
		'savings_msg' => [
			'group' => 'Copy & Headlines',
			'label' => 'Savings message (F/I)',
			'default' => 'off',
			'options' => [
				'off' => 'Off (control)',
				'on' => 'Show savings message',
			],
			'note' => 'COMPLIANCE: substantiate any savings claim.',
		],
		// D — Logo/icon impression (image variants handled by logo select; this is treatment)
		'logo_treatment' => [
			'group' => 'Brand & Color',
			'label' => 'Logo treatment (D)',
			'default' => 'grayscale_bold',
			'options' => [
				'grayscale_bold' => 'Grayscale + bold Healthcare (control)',
				'full_color' => 'Full color',
				'mono' => 'Monochrome',
			],
			'note' => 'PRESERVE grayscale+bold; COMPLIANCE: no carrier copy.',
		],
		// F — Gold shade + CTA color
		'gold_shade' => [
			'group' => 'Brand & Color',
			'label' => 'Gold shade (F)',
			'default' => 'control',
			'options' => [
				'control' => 'Current gold (control)',
				'vibrant' => 'Vibrant inviting gold',
				'bright' => 'Bright yellow-gold',
			],
		],
		'cta_color' => [
			'group' => 'Brand & Color',
			'label' => 'CTA button color (F)',
			'default' => 'green',
			'options' => [
				'green' => 'Healthcare green (control)',
				'gold' => 'Approved gold',
			],
		],
		// H — Numeric font
		'numeric_font' => [
			'group' => 'Typography',
			'label' => 'Numeric font (H)',
			'default' => 'current',
			'options' => [
				'current' => 'Current (control)',
				'a' => 'Option A — modern',
				'b' => 'Option B — rounded',
				'c' => 'Option C — geometric',
			],
			'note' => 'Watch 3, 0, 8; keep some playful character.',
		],
		// G — Form layout
		'form_layout' => [
			'group' => 'Typography',
			'label' => 'Form layout (G)',
			'default' => 'current',
			'options' => [
				'current' => 'Current (control)',
				'calm' => 'Calmer / neutral hierarchy',
			],
		],
		// E — ZIP area alignment
		'zip_alignment' => [
			'group' => 'Homepage',
			'label' => 'ZIP alignment (E)',
			'default' => 'current',
			'options' => [
				'current' => 'Current (control)',
				'fixed' => 'Corrected centering',
			],
		],
		// J — Phone number top-right
		'phone_topright' => [
			'group' => 'Homepage',
			'label' => 'Top-right phone (J)',
			'default' => 'off',
			'options' => [
				'off' => 'Hidden (control)',
				'on' => 'Show phone number',
			],
		],
		// L — Mobile trust strip position
		'trust_strip_pos' => [
			'group' => 'Mobile',
			'label' => 'Trust strip position (L)',
			'default' => 'current',
			'options' => [
				'current' => 'Current (control)',
				'lower' => 'Lower, above address bar',
				'behind' => 'Slightly behind chrome',
			],
		],
		// M — Mobile disclaimer position
		'disclaimer_pos' => [
			'group' => 'Mobile',
			'label' => 'Disclaimer position (M)',
			'default' => 'current',
			'options' => [
				'current' => 'Current (control)',
				'lower' => 'Lower near back-control area',
			],
			'note' => 'COMPLIANCE: keep readable, do not obscure.',
		],
		// N — Next bar
		'next_bar' => [
			'group' => 'Form Flow',
			'label' => 'Auto-advance Next bar (N)',
			'default' => 'on',
			'options' => [
				'on' => 'Show (control)',
				'off' => 'Remove on auto-advance steps',
			],
			'note' => 'PRESERVE auto-advance + Back-state.',
		],
		// V — Household income
		'income_field' => [
			'group' => 'Form Fields',
			'label' => 'Household income (V)',
			'default' => 'required',
			'options' => [
				'required' => 'Required (control)',
				'optional_in' => 'Optional — inside field',
				'optional_beside' => 'Optional — beside label',
			],
		],
		// W — Reason for shopping
		'reason_field' => [
			'group' => 'Form Fields',
			'label' => 'Reason for shopping (W)',
			'default' => 'required',
			'options' => [
				'required' => 'Required (control)',
				'optional_paren' => 'Optional (in parentheses)',
				'optional_placeholder' => 'Optional as placeholder',
				'optional_inbox' => 'Optional inside box',
			],
		],
		// X — Contact-field explanation
		'contact_explain' => [
			'group' => 'Form Fields',
			'label' => 'Contact explanation (X)',
			'default' => 'none',
			'options' => [
				'none' => 'None (control)',
				'phone_link' => 'Phone: send you a link',
				'email_info' => 'Email: send information',
				'both' => 'Email + phone: link & agent',
			],
			'note' => 'COMPLIANCE: consent/disclosure language.',
		],
		// T — Final form step copy
		'final_step_copy' => [
			'group' => 'Final Step',
			'label' => 'Final step copy (T)',
			'default' => 'control',
			'options' => [
				'control' => 'Current (control)',
				'almost_there' => "You're almost there, [First]",
				'two_questions' => 'For a valid quote, two more questions',
			],
		],
		// Y — Confirmation headline
		'confirmation_headline' => [
			'group' => 'Final Step',
			'label' => 'Confirmation headline (Y)',
			'default' => 'progress',
			'options' => [
				'progress' => "You're almost there (control)",
				'congrats' => 'Congratulations, your quote is ready',
			],
			'note' => 'COMPLIANCE: only if a quote is genuinely ready.',
		],
		// K — Exit-intent modal
		'exit_modal' => [
			'group' => 'Experiments',
			'label' => 'Exit-intent modal (K)',
			'default' => 'off',
			'options' => [
				'off' => 'Off (control)',
				'plain' => 'Are you sure you want to leave?',
				'agent' => 'With call-center agent image',
			],
			'note' => 'No trapping; accessible focus/dismiss.',
		],
		// Z — Immediate call + SMS
		'submit_flow' => [
			'group' => 'Experiments',
			'label' => 'Post-submit flow (Z)',
			'default' => 'control',
			'options' => [
				'control' => 'Standard + day-zero SMS (control)',
				'sms_info' => 'SMS link to plan info',
				'sms_agent' => 'SMS link to agent',
				'callback' => 'Request immediate callback',
			],
			'note' => 'Preview only — do NOT auto-dial; legal wording + consent required.',
		],
	];

	// Resolve active variants from query params + session.
	if (isset($_GET['reset_experiments']) && $_GET['reset_experiments'] === '1') {
		unset($_SESSION['ah_experiments']);
	}
	$stored = isset($_SESSION['ah_experiments']) && is_array($_SESSION['ah_experiments'])
		? $_SESSION['ah_experiments']
		: [];
	foreach ($GLOBALS['ah_experiments'] as $id => $cfg) {
		$param = 'x_' . $id;
		if (!isset($_GET[$param])) {
			continue;
		}
		$value = strval($_GET[$param]);
		if ($value === 'default' || $value === $cfg['default'] || !isset($cfg['options'][$value])) {
			unset($stored[$id]);
			continue;
		}
		$stored[$id] = $value;
	}
	$_SESSION['ah_experiments'] = $stored;

	$GLOBALS['ah_experiment_values'] = [];
	$ahPageDefaults = isset($GLOBALS['ah_experiment_page_defaults']) && is_array($GLOBALS['ah_experiment_page_defaults'])
		? $GLOBALS['ah_experiment_page_defaults']
		: [];
	foreach ($GLOBALS['ah_experiments'] as $id => $cfg) {
		// Per-page default (set before header include) applies unless the visitor's session overrides it.
		$pageDefault = isset($ahPageDefaults[$id]) && isset($cfg['options'][$ahPageDefaults[$id]])
			? $ahPageDefaults[$id]
			: $cfg['default'];
		$GLOBALS['ah_experiment_values'][$id] = isset($stored[$id]) && isset($cfg['options'][$stored[$id]])
			? $stored[$id]
			: $pageDefault;
	}
}

if (!function_exists('ah_experiment')) {
	/** Active variant key for an experiment id. */
	function ah_experiment($id)
	{
		return isset($GLOBALS['ah_experiment_values'][$id]) ? $GLOBALS['ah_experiment_values'][$id] : null;
	}
}

if (!function_exists('ah_experiment_body_attrs')) {
	/** data-x-<id>="<variant>" attributes for every experiment, for CSS/JS targeting on <body>. */
	function ah_experiment_body_attrs()
	{
		if (empty($GLOBALS['ah_experiment_values'])) {
			return '';
		}
		$out = [];
		foreach ($GLOBALS['ah_experiment_values'] as $id => $value) {
			$out[] = 'data-x-' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8')
				. '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
		}
		return implode(' ', $out);
	}
}

if (!function_exists('ah_experiment_json')) {
	/** JSON map of active experiment variants for client-side use. */
	function ah_experiment_json()
	{
		$values = isset($GLOBALS['ah_experiment_values']) ? $GLOBALS['ah_experiment_values'] : [];
		return json_encode($values, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}
}

if (!function_exists('ah_experiment_panel_html')) {
	/** Grouped, collapsible <select> controls for the debug panel (selects tagged data-exp). */
	function ah_experiment_panel_html()
	{
		if (empty($GLOBALS['ah_experiments'])) {
			return '';
		}
		$esc = function ($s) {
			return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
		};

		$groups = [];
		$activeTotal = 0;
		foreach ($GLOBALS['ah_experiments'] as $id => $cfg) {
			$groups[$cfg['group']][$id] = $cfg;
			if ($GLOBALS['ah_experiment_values'][$id] !== $cfg['default']) {
				$activeTotal++;
			}
		}

		$html = '<style>'
			. '.ahx-group{grid-column:1 / span 2;border-top:1px solid rgba(255,255,255,.18);margin-top:6px;}'
			. '.ahx-group>summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;padding:7px 2px;font-size:11px;text-transform:uppercase;letter-spacing:.6px;opacity:.85;user-select:none;}'
			. '.ahx-group>summary::-webkit-details-marker{display:none;}'
			. '.ahx-group>summary::before{content:"\25B8";display:inline-block;margin-right:6px;transition:transform .15s;opacity:.7;}'
			. '.ahx-group[open]>summary::before{transform:rotate(90deg);}'
			. '.ahx-sumright{display:flex;align-items:center;gap:6px;flex:0 0 auto;}'
			. '.ahx-gcount{font-weight:700;font-size:10px;background:rgba(255,255,255,.16);border-radius:10px;padding:1px 7px;letter-spacing:0;opacity:0;transition:opacity .15s;}'
			. '.ahx-gcount.on{opacity:1;background:#f0a500;color:#3a2600;}'
			. '.ahx-greset{display:none;border:0;background:rgba(255,255,255,.14);color:#cfe3ff;font:700 11px/1 "Segoe UI",sans-serif;padding:3px 7px;border-radius:5px;cursor:pointer;letter-spacing:0;}'
			. '.ahx-group.has-active>summary .ahx-greset{display:inline-block;}'
			. '.ahx-greset:hover{background:rgba(255,255,255,.28);}'
			. '.ahx-body{display:grid;grid-template-columns:1fr;gap:8px;padding:2px 0 8px;}'
			. '.ahx-item{display:flex;flex-direction:column;gap:4px;}'
			. '.ahx-item.hide{display:none;}'
			. '.ahx-head{display:flex;align-items:center;justify-content:space-between;gap:6px;}'
			. '.ahx-badges{display:flex;align-items:center;gap:5px;flex:0 0 auto;}'
			. '.ahx-dot{width:8px;height:8px;border-radius:50%;background:#f0a500;box-shadow:0 0 0 2px rgba(240,165,0,.25);display:none;}'
			. '.ahx-item.active .ahx-dot{display:inline-block;}'
			. '.ahx-flag{font-size:9px;font-weight:800;letter-spacing:.4px;background:#c0392b;color:#fff;border-radius:4px;padding:1px 5px;text-transform:uppercase;}'
			. '.ahx-reset{align-self:flex-start;display:none;border:0;background:rgba(255,255,255,.14);color:#cfe3ff;font:600 10px/1 "Segoe UI",sans-serif;padding:4px 7px;border-radius:5px;cursor:pointer;}'
			. '.ahx-item.active .ahx-reset{display:inline-block;}'
			. '.ahx-reset:hover{background:rgba(255,255,255,.26);}'
			. '.ahx-note{font-weight:500;font-size:10px;opacity:.7;}'
			. '.ahx-note.compliance{opacity:.9;color:#ffcf70;}'
			. '</style>';

		foreach ($groups as $groupName => $items) {
			$gActive = 0;
			foreach ($items as $id => $cfg) {
				if ($GLOBALS['ah_experiment_values'][$id] !== $cfg['default']) {
					$gActive++;
				}
			}
			$html .= '<details class="ahx-group' . ($gActive > 0 ? ' has-active' : '') . '" data-group="' . $esc($groupName) . '"'
				. ($gActive > 0 ? ' open' : '') . '>';
			$html .= '<summary><span>' . $esc($groupName) . '</span>'
				. '<span class="ahx-sumright">'
				. '<button type="button" class="ahx-greset" data-group="' . $esc($groupName) . '" title="Reset this group to control">&#8635; group</button>'
				. '<span class="ahx-gcount' . ($gActive > 0 ? ' on' : '') . '">'
				. intval($gActive) . ' active</span></span></summary>';
			$html .= '<div class="ahx-body">';
			foreach ($items as $id => $cfg) {
				$active = $GLOBALS['ah_experiment_values'][$id];
				$isActive = ($active !== $cfg['default']);
				$note = isset($cfg['note']) ? $cfg['note'] : '';
				$isCompliance = ($note !== '' && stripos($note, 'compliance') !== false);
				$search = strtolower($cfg['label'] . ' ' . $note . ' ' . $groupName);

				$html .= '<label class="ahx-item' . ($isActive ? ' active' : '') . '"'
					. ' data-exp-id="' . $esc($id) . '"'
					. ' data-default="' . $esc($cfg['default']) . '"'
					. ($isCompliance ? ' data-compliance="1"' : '')
					. ' data-search="' . $esc($search) . '">';
				$html .= '<span class="ahx-head"><span class="ahx-item-label">' . $esc($cfg['label']) . '</span>'
					. '<span class="ahx-badges">'
					. ($isCompliance ? '<span class="ahx-flag" title="Requires compliance sign-off">&#9888; compliance</span>' : '')
					. '<span class="ahx-dot" title="Changed from control"></span>'
					. '</span></span>';
				$html .= '<select data-exp="1" id="ahx-' . $esc($id) . '" data-default="' . $esc($cfg['default'])
					. '" style="padding:6px;border-radius:6px;border:1px solid #89a;color:#102b52;">';
				foreach ($cfg['options'] as $value => $optLabel) {
					$sel = ($value === $active) ? ' selected' : '';
					$html .= '<option value="' . $esc($value) . '"' . $sel . '>' . $esc($optLabel) . '</option>';
				}
				$html .= '</select>';
				$html .= '<button type="button" class="ahx-reset" data-target="ahx-' . $esc($id) . '">&#8635; back to control</button>';
				if ($note !== '') {
					$html .= '<span class="ahx-note' . ($isCompliance ? ' compliance' : '') . '">' . $esc($note) . '</span>';
				}
				$html .= '</label>';
			}
			$html .= '</div></details>';
		}
		return $html;
	}
}

if (!function_exists('ah_experiment_active_count')) {
	/** Number of experiments currently changed from their control/default. */
	function ah_experiment_active_count()
	{
		if (empty($GLOBALS['ah_experiments'])) {
			return 0;
		}
		$n = 0;
		foreach ($GLOBALS['ah_experiments'] as $id => $cfg) {
			if ($GLOBALS['ah_experiment_values'][$id] !== $cfg['default']) {
				$n++;
			}
		}
		return $n;
	}
}

