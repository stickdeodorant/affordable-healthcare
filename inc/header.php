<?php if(session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>

<?php if (isset($_GET['gclid'])) {
	$_SESSION['gclid'] = $_GET['gclid'];
} ?>
<?php if (isset($_GET['utm_campaign'])) {
	$_SESSION['adset_id'] = $_GET['utm_campaign'];
	$_SESSION['utm_campaign'] = $_GET['utm_campaign'];
} ?>
<?php if (isset($_GET['utm_content'])) {
	$_SESSION['ad_id'] = $_GET['utm_content'];
	$_SESSION['utm_content'] = $_GET['utm_content'];
} ?>

<?php include __DIR__ . '/globalvars.php';
	$analyticsEnabled = isset($enableAnalytics) ? $enableAnalytics : true;
	$enableAnalytics = $analyticsEnabled;
	$gtmContainers = isset($gtm_containers) ? $gtm_containers : ['GTM-5DHQH9H', 'GTM-NGLCRXJH', 'GTM-MJMNPM5'];
	$googleAdsIds = isset($google_ads_ids) ? $google_ads_ids : ['AW-340114397'];
	$gaIds = isset($ga_measurement_ids) ? $ga_measurement_ids : ['UA-203937944-1', 'UA-203921006-1'];
	$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	if (strpos($url,'alpha') !== false) {
			$phoneVer = 'kobe1';
			$_SESSION['typ_phone'] = 'kobe_typ';
			$phoneVer = 'kobe2';
	} else {
		$phoneVersion = isset($_GET['num']) ? $_GET['num'] : 'not set';
		if ($phoneVersion == '1') {
			$phoneVer = 'kobe1';
		} else if ($phoneVersion == '2') {
			$phoneVer = 'kobe2';
		} else if ($phoneVersion == '3') {
			$phoneVer = 'kobe3';
		} else {
			$phoneVer = 'popup';
		}
	}
	if (isset($_GET['fb'])) {
		$_SESSION['fb'] = $_GET['fb'];
	}
	if (isset($_GET['usha'])) {
		$_SESSION['usha'] = $_GET['usha'];
	}
	if (isset($_GET['campaign'])) {
		if ($_GET['campaign'] != 'Magenta') {
			
			if ($_GET['campaign'] == 'Fa1') {
				$_SESSION['campaign'] = 'Falcons1';
			} else if ($_GET['campaign'] == 'Fa2') {
				$_SESSION['campaign'] = 'Falcons2';
			} else if ($_GET['campaign'] == 'Fa3') {
				$_SESSION['campaign'] = 'Falcons3';
			} else {
				$_SESSION['campaign'] = $_GET['campaign'];
			}
		} else {

			$_SESSION['campaign'] = 'Magenta';
			
		}
	
		if (isset($_GET['affiliate_ID'])) {
			$_SESSION['affiliate_ID'] = $_GET['affiliate_ID'];
		}

		if (isset($_GET['HIT_ID'])) {
			$_SESSION['HIT_ID'] = $_GET['HIT_ID'];
		}
		if (isset($_GET['Sub_ID'])) {
			$_SESSION['Sub_ID'] = $_GET['Sub_ID'];
		}
	}

	if(isset($_GET['Notes'])) { 
		$_SESSION['Notes'] = $_GET['Notes'];
	}
	if(isset($_GET['agent'])) { 
		$_SESSION['agent'] = $_GET['agent'];
	}

?>
<html lang="en" >
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,user-scalable=no">
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

		<?php if (!empty($googleAdsIds) || !empty($gaIds)): ?>
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
		<?php endif; ?>
	<?php endif; ?>
	<link rel='stylesheet' href='/css/bootstrap.min.css'>
	<?php /*<link rel='stylesheet' href='/css/fonts.css'>*/ ?>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
	<link rel="stylesheet" href="https://use.typekit.net/qkg3icr.css">

	<style>
		/* Tabler stack helper to replace legacy fa-layers */
		.icon-stack { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 2.6rem; height: 2.6rem; }
		.icon-stack .icon-base svg { width: 100%; height: 100%; }
		.icon-stack .icon-overlay { position: absolute; width: 60%; height: 60%; }
		.icon-stack .icon-overlay.icon-hash { transform: translate(6%, -4%); }
		.icon-stack .icon-overlay.icon-pencil { transform: translate(8%, -6%); }
		.icon-stack .icon-overlay.icon-scale { transform: translate(-6%, 0); }
	</style>
	<link rel="stylesheet" href="/css/style.css">
	<link rel="apple-touch-icon" href="/img/favicon.png">
	<link rel="icon" href="/img/favicon.png" type="image/x-icon">
	<script>
		// Replace legacy Font Awesome stacks with Tabler SVG stacks
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
	<?php if ($analyticsEnabled): ?>
	<!-- Bing 07/29/2021 -->
	<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"134598208"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>  
	<?php endif; ?>
	<!-- Bing 11/01/2032 -->
	<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"134598208", enableAutoSpaTracking: true};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>
</head>
<body>
	<?php if ($analyticsEnabled): ?>
		<?php foreach ($gtmContainers as $gtmId): ?>
			<?php $safeGtmId = htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8'); ?>
			<!-- Google Tag Manager (noscript) -->
			<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= $safeGtmId ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
			<!-- End Google Tag Manager (noscript) -->
		<?php endforeach; ?>
	<?php endif; ?>
	<?php include __DIR__ . '/navbar.php'; ?>