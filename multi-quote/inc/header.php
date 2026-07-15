<?php 
// Remove the session_start() line - let SessionManager handle sessions
// <?php if(session_status() === PHP_SESSION_NONE) session_start(); ?>

<!DOCTYPE html>
<?php include 'inc/globalvars.php';
	$analyticsEnabled = isset($enableAnalytics) ? $enableAnalytics : true;
	$gtmContainers = isset($gtm_containers) ? $gtm_containers : ['GTM-KPSJW24'];
	// $googleAdsIds = isset($google_ads_ids) ? $google_ads_ids : ['AW-340114397'];
	// $gaIds = isset($ga_measurement_ids) ? $ga_measurement_ids : ['UA-203937944-1', 'UA-203921006-1'];
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
	<?php if ($enableAnalytics): ?>
	<!-- Bing 07/29/2021 -->
	<!-- <script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"134598208"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>   -->
	<!-- Bing 11/01/2032 -->
	<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"134598208", enableAutoSpaTracking: true};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>
	<?php endif; ?>

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
<body>
	<?php if ($analyticsEnabled): ?>
		<?php foreach ($gtmContainers as $gtmId): ?>
			<?php $safeGtmId = htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8'); ?>
			<!-- Google Tag Manager (noscript) -->
			<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= $safeGtmId ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
			<!-- End Google Tag Manager (noscript) -->
		<?php endforeach; ?>
	<?php endif; ?>
	<?php include 'inc/navbar.php'; ?>