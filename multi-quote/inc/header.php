<?php 
// Remove the session_start() line - let SessionManager handle sessions
// <?php if(session_status() === PHP_SESSION_NONE) session_start(); ?>

<!DOCTYPE html>
<?php include 'inc/globalvars.php';
	require_once __DIR__ . '/../../inc/experiments.php';
	$analyticsEnabled = isset($enableAnalytics) ? $enableAnalytics : true;
	$gtmContainers = isset($gtm_containers) ? $gtm_containers : ['GTM-KPSJW24'];
	$themePresets = [
		'default' => [
			'primary' => '#67cdcb',
			'secondary' => '#f2c230',
			'tertiary' => '#13676b',
			'accent' => '#8bc34a',
			'input-border' => '#203a72',
			'bg-page' => '#ffffff',
			'bg-panel' => '#ffffff',
			'svg' => '#13676b',
		],
		'logo-match' => [
			'primary' => '#24add4',
			'secondary' => '#f8be4e',
			'tertiary' => '#136a80',
			'accent' => '#8dc84a',
			'input-border' => '#136a80',
			'bg-page' => '#f7fbfd',
			'bg-panel' => '#ffffff',
			'svg' => '#24add4',
		],
		'ohio-healthplans' => [
			'primary' => '#124085',
			'secondary' => '#6fbf4a',
			'tertiary' => '#0c2f63',
			'accent' => '#8bc34a',
			'input-border' => '#124085',
			'bg-page' => '#f5f8fc',
			'bg-panel' => '#ffffff',
			'svg' => '#124085',
		],
		// Additional color schemes (shared teal primary + navy tertiary; hero color -> secondary/CTA, 4th color -> accent/light bg).
		'golden-trust' => [
			'primary' => '#008A8C', 'secondary' => '#F6B900', 'tertiary' => '#002958', 'accent' => '#F5F1E8',
			'input-border' => '#002958', 'bg-page' => '#F5F1E8', 'bg-panel' => '#ffffff', 'svg' => '#008A8C',
		],
		'warm-orange' => [
			'primary' => '#008A8C', 'secondary' => '#F58220', 'tertiary' => '#002958', 'accent' => '#FFD166',
			'input-border' => '#002958', 'bg-page' => '#ffffff', 'bg-panel' => '#ffffff', 'svg' => '#008A8C',
		],
		'modern-amber' => [
			'primary' => '#008A8C', 'secondary' => '#FFB000', 'tertiary' => '#002958', 'accent' => '#DCE9E7',
			'input-border' => '#002958', 'bg-page' => '#DCE9E7', 'bg-panel' => '#ffffff', 'svg' => '#008A8C',
		],
		'coral-red' => [
			'primary' => '#008A8C', 'secondary' => '#E94F4F', 'tertiary' => '#002958', 'accent' => '#F5C04A',
			'input-border' => '#002958', 'bg-page' => '#ffffff', 'bg-panel' => '#ffffff', 'svg' => '#008A8C',
		],
		'strong-red' => [
			'primary' => '#008A8C', 'secondary' => '#C83E4D', 'tertiary' => '#002958', 'accent' => '#F4B942',
			'input-border' => '#002958', 'bg-page' => '#ffffff', 'bg-panel' => '#ffffff', 'svg' => '#008A8C',
		],
		'burnt-orange' => [
			'primary' => '#008A8C', 'secondary' => '#E86A24', 'tertiary' => '#002958', 'accent' => '#F2E8D5',
			'input-border' => '#002958', 'bg-page' => '#F2E8D5', 'bg-panel' => '#ffffff', 'svg' => '#008A8C',
		],
		'bright-healthcare' => [
			'primary' => '#008A8C', 'secondary' => '#FFC928', 'tertiary' => '#002958', 'accent' => '#EF5350',
			'input-border' => '#002958', 'bg-page' => '#ffffff', 'bg-panel' => '#ffffff', 'svg' => '#008A8C',
		],
		'premium-copper' => [
			'primary' => '#008A8C', 'secondary' => '#C96932', 'tertiary' => '#002958', 'accent' => '#E8B44C',
			'input-border' => '#002958', 'bg-page' => '#ffffff', 'bg-panel' => '#ffffff', 'svg' => '#008A8C',
		],
	];
	$themeAliases = [
		'1' => 'default',
		'2' => 'logo-match',
		'3' => 'ohio-healthplans',
		'4' => 'golden-trust',
		'5' => 'warm-orange',
		'6' => 'modern-amber',
		'7' => 'coral-red',
		'8' => 'strong-red',
		'9' => 'burnt-orange',
		'10' => 'bright-healthcare',
		'11' => 'premium-copper',
		'default' => 'default',
		'logo-match' => 'logo-match',
		'ohio-healthplans' => 'ohio-healthplans',
		'golden-trust' => 'golden-trust',
		'warm-orange' => 'warm-orange',
		'modern-amber' => 'modern-amber',
		'coral-red' => 'coral-red',
		'strong-red' => 'strong-red',
		'burnt-orange' => 'burnt-orange',
		'bright-healthcare' => 'bright-healthcare',
		'premium-copper' => 'premium-copper',
		'green-core' => 'default',
		'gold-warm' => 'default',
		'gold-bright' => 'default',
		'ocean-fresh' => 'ohio-healthplans',
		'spectrum-vibrant' => 'logo-match',
	];
	$colorVariants = [
		'primary' => [1 => '#67cdcb', 2 => '#24add4', 3 => '#1fae8d', 4 => '#124085', 5 => '#0f5b78'],
		'secondary' => [1 => '#f2711c', 2 => '#e8590c', 3 => '#e8452e', 4 => '#d9480f', 5 => '#c2410c', 6 => '#b8860b', 7 => '#d4a017', 8 => '#e8b923', 9 => '#f2cc0f', 10 => '#ffd700'],
		'accent' => [1 => '#8bc34a', 2 => '#6fbf4a', 3 => '#24c188', 4 => '#42b883', 5 => '#9ad14b'],
		'tertiary' => [1 => '#13676b', 2 => '#136a80', 3 => '#124085', 4 => '#0c2f63', 5 => '#203a72'],
		'input-border' => [1 => '#203a72', 2 => '#136a80', 3 => '#124085', 4 => '#2f3f4f', 5 => '#1f4f78'],
		'bg-page' => [1 => '#ffffff', 2 => '#f7fbfd', 3 => '#f5f8fc', 4 => '#f2f5f9', 5 => '#eef6f2'],
		'bg-panel' => [1 => '#ffffff', 2 => '#f9fcff', 3 => '#f7fafc', 4 => '#f4f8f2', 5 => '#f3f6fb'],
		'svg' => [1 => '#13676b', 2 => '#24add4', 3 => '#124085', 4 => '#6fbf4a', 5 => '#8bc34a'],
	];
	$overrideParamMap = [
		'primary' => 'primary',
		'secondary' => 'secondary',
		'accent' => 'accent',
		'tertiary' => 'tertiary',
		'input' => 'input-border',
		'background' => 'bg-page',
		'panelbg' => 'bg-panel',
		'svg' => 'svg',
	];
	$maxLogoVariant = 50;
	if (isset($_GET['theme'])) {
		$requestedTheme = strval($_GET['theme']);
		if (isset($themeAliases[$requestedTheme])) {
			$_SESSION['ah_theme'] = $themeAliases[$requestedTheme];
		}
	}
	if (isset($_GET['reset_palette']) && $_GET['reset_palette'] === '1') {
		unset($_SESSION['ah_palette_overrides']);
	}
	$storedOverrides = isset($_SESSION['ah_palette_overrides']) && is_array($_SESSION['ah_palette_overrides'])
		? $_SESSION['ah_palette_overrides']
		: [];
	foreach ($overrideParamMap as $queryParam => $colorKey) {
		if (!isset($_GET[$queryParam])) {
			continue;
		}
		if ($_GET[$queryParam] === '0') {
			unset($storedOverrides[$colorKey]);
			continue;
		}
		$variantIndex = filter_var($_GET[$queryParam], FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1, 'max_range' => count($colorVariants[$colorKey])],
		]);
		if ($variantIndex !== false) {
			$storedOverrides[$colorKey] = $variantIndex;
		}
	}
	$_SESSION['ah_palette_overrides'] = $storedOverrides;
	if (isset($_GET['logo'])) {
		$requestedLogo = filter_var($_GET['logo'], FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1, 'max_range' => $maxLogoVariant],
		]);
		if ($requestedLogo !== false) {
			$_SESSION['ah_logo'] = $requestedLogo;
		}
	}
	$activeTheme = isset($_SESSION['ah_theme']) ? $_SESSION['ah_theme'] : 'default';
	if (!isset($themePresets[$activeTheme])) {
		$activeTheme = 'default';
	}
	$resolvedPalette = $themePresets[$activeTheme];
	foreach ($storedOverrides as $colorKey => $variantIndex) {
		if (isset($colorVariants[$colorKey][$variantIndex])) {
			$resolvedPalette[$colorKey] = $colorVariants[$colorKey][$variantIndex];
		}
	}
	if (function_exists('ah_experiment')) {
		$ahGoldShade = ah_experiment('gold_shade');
		if ($ahGoldShade === 'vibrant') { $resolvedPalette['secondary'] = '#ffb020'; }
		elseif ($ahGoldShade === 'bright') { $resolvedPalette['secondary'] = '#ffd21a'; }
	}
	$themeInlineStyle = '--color-primary:' . $resolvedPalette['primary']
		. '; --color-secondary:' . $resolvedPalette['secondary']
		. '; --color-tertiary:' . $resolvedPalette['tertiary']
		. '; --color-accent:' . $resolvedPalette['accent']
		. '; --color-input-border:' . $resolvedPalette['input-border']
		. '; --color-bg-page:' . $resolvedPalette['bg-page']
		. '; --color-bg-panel:' . $resolvedPalette['bg-panel']
		. '; --color-svg:' . $resolvedPalette['svg']
		. ';';

	$activeLogoNumber = isset($_SESSION['ah_logo']) ? intval($_SESSION['ah_logo']) : 1;
	if ($activeLogoNumber < 1 || $activeLogoNumber > $maxLogoVariant) {
		$activeLogoNumber = 1;
	}
	$activeLogoSrc = '/img/logo.svg';
	$activeLogoPath = __DIR__ . '/../../img/logo.svg';
	$namedLogoMap = [
		1 => ['/img/logo.svg', __DIR__ . '/../../img/logo.svg'],
		2 => ['/img/logo-2.svg', __DIR__ . '/../../img/logo-2.svg'],
	];
	if (isset($namedLogoMap[$activeLogoNumber]) && file_exists($namedLogoMap[$activeLogoNumber][1])) {
		$activeLogoSrc = $namedLogoMap[$activeLogoNumber][0];
		$activeLogoPath = $namedLogoMap[$activeLogoNumber][1];
	} else {
		$logoCandidates = [
			'/img/logo-' . $activeLogoNumber . '.svg' => __DIR__ . '/../../img/logo-' . $activeLogoNumber . '.svg',
			'/img/logo-' . $activeLogoNumber . '.png' => __DIR__ . '/../../img/logo-' . $activeLogoNumber . '.png',
			'/img/logo-' . $activeLogoNumber . '.webp' => __DIR__ . '/../../img/logo-' . $activeLogoNumber . '.webp',
		];
		foreach ($logoCandidates as $candidateSrc => $candidatePath) {
			if (file_exists($candidatePath)) {
				$activeLogoSrc = $candidateSrc;
				$activeLogoPath = $candidatePath;
				break;
			}
		}
	}
	$activeLogoVersion = @filemtime($activeLogoPath) ?: time();
	// $googleAdsIds = isset($google_ads_ids) ? $google_ads_ids : ['AW-340114397'];
	// $gaIds = isset($ga_measurement_ids) ? $ga_measurement_ids : ['UA-203937944-1', 'UA-203921006-1'];
?>

<html lang="en" >
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,user-scalable=no,viewport-fit=cover">
	<title><?php if(isset($title) == true && !empty($title)) { echo $title . ' | '; } echo $sitename; ?></title>
	<?php if ($analyticsEnabled): ?>
		<?php foreach ($gtmContainers as $gtmId): ?>
			<?php $safeGtmId = htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8'); ?>
			<!-- Google Tag Manager -->
			<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','dataLayer','<?= $safeGtmId ?>');</script>
			<!-- End Google Tag Manager -->
		<?php endforeach; ?>

		<?php /* if (!empty($googleAdsIds) || !empty($gaIds)): ?>
			<?php $gtagBootstrapId = $googleAdsIds[0] ?? ($gaIds[0] ?? ''); ?>
			<?php if (!empty($gtagBootstrapId)): ?>
				<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gtagBootstrapId, ENT_QUOTES, 'UTF-8'); ?>"></script>
			<?php endif; ?>
			<script>
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
				gtag('js', new Date());
				<?php foreach ($googleAdsIds as $adsId): ?>
				gtag('config', '<?= htmlspecialchars($adsId, ENT_QUOTES, 'UTF-8'); ?>');
				<?php endforeach; ?>
				<?php foreach ($gaIds as $gaId): ?>
				gtag('config', '<?= htmlspecialchars($gaId, ENT_QUOTES, 'UTF-8'); ?>');
				<?php endforeach; ?>
			</script>
		<?php endif; */ ?>
	<?php endif; ?>
	<link rel='stylesheet' href='../css/bootstrap.min.css'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

	<style>
		.icon-stack { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 2.6rem; height: 2.6rem; }
		.icon-stack .icon-base svg { width: 100%; height: 100%; }
		.icon-stack .icon-overlay { position: absolute; width: 60%; height: 60%; }
		.icon-stack .icon-overlay.icon-hash { transform: translate(6%, -4%); }
		.icon-stack .icon-overlay.icon-pencil { transform: translate(8%, -6%); }
		.icon-stack .icon-overlay.icon-scale { transform: translate(-6%, 0); }
	</style>
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./inc/shared/loading-modal/loading-modal.css">
	<link rel="apple-touch-icon" href="./img/favicon.png">
	<link rel="icon" href="./img/favicon.png" type="image/x-icon">
	<script>
		(function() {
			function baseCircle(filled) {
				return filled
					? '<svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="currentColor" /></svg>'
					: '<svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.8" /></svg>';
			}
			function overlay(name) {
				if (name === 'hash') return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 9h14M5 15h14M10 4l-2 16M16 4l-2 16"/></svg>';
				if (name === 'pencil') return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4l11-11a2.8 2.8 0 0 0-4-4L4 16v4"/><path d="M13.5 6.5l4 4"/></svg>';
				if (name === 'scale') return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10"/><path d="M6 7h12"/><path d="M6 7l-2 5 2 5a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/><path d="M18 7l-2 5 2 5a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg>';
				return '';
			}
			function convertLayer(layer) {
				var icons = layer.querySelectorAll('i');
				if (!icons.length) return;
				var baseIcon = Array.from(icons).find(function(el){ return el.className.indexOf('circle') !== -1; });
				var overlayIcon = Array.from(icons).find(function(el){ return el !== baseIcon; });
				var name = overlayIcon ? (overlayIcon.className.match(/fa-([a-z-]+)/) || [])[1] : '';
				var filled = baseIcon ? baseIcon.classList.contains('fas') : false;
				var overlayName = name === 'hashtag' ? 'hash' : (name === 'balance-scale' ? 'scale' : name);
				if (!overlayName) return;
				layer.innerHTML = '<span class="icon-stack"><span class="icon-base">' + baseCircle(filled) + '</span><span class="icon-overlay icon-' + overlayName + '">' + overlay(overlayName) + '</span></span>';
				layer.classList.remove('fa-layers');
			}
			document.addEventListener('DOMContentLoaded', function() {
				document.querySelectorAll('.fa-layers').forEach(convertLayer);
			});
		})();
	</script>
	
	<!-- Generate a TrustedForm Certificate -->
	<script type="text/javascript">
		(function() {
			var field = 'TrustedFormToken';
			var provideReferrer = false;
			var tf = document.createElement('script');
			tf.type = 'text/javascript'; tf.async = true; 
			tf.src = 'http' + ('https:' == document.location.protocol ? 's' : '') +
			'://api.trustedform.com/trustedform.js?provide_referrer=' + escape(provideReferrer) + '&field=' + escape(field) + '&l='+new Date().getTime()+Math.random();
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(tf, s); }
		)();
	</script>
	
</head>
<body data-theme="<?= htmlspecialchars($activeTheme, ENT_QUOTES, 'UTF-8') ?>" class="<?= isset($_GET['debug']) ? 'ah-debug-on' : '' ?>" <?= function_exists('ah_experiment_body_attrs') ? ah_experiment_body_attrs() : '' ?> style="<?= htmlspecialchars($themeInlineStyle, ENT_QUOTES, 'UTF-8') ?>">
	<?php if (function_exists('ah_experiment_json')): ?><script>window.AH_EXPERIMENTS = <?= ah_experiment_json() ?>;</script><?php endif; ?>
	<?php if ($analyticsEnabled): ?>
		<?php foreach ($gtmContainers as $gtmId): ?>
			<?php $safeGtmId = htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8'); ?>
			<!-- Google Tag Manager (noscript) -->
			<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= $safeGtmId ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
			<!-- End Google Tag Manager (noscript) -->
		<?php endforeach; ?>
	<?php endif; ?>
	<?php include 'inc/navbar.php'; ?>