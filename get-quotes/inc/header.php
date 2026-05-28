<!doctype html>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/inc/globalvars.php';
	$analyticsEnabled = isset($enableAnalytics) ? $enableAnalytics : true;
	$gtmContainers = isset($gtm_containers) ? $gtm_containers : ['GTM-5DHQH9H', 'GTM-NGLCRXJH', 'GTM-MJMNPM5'];
	$googleAdsIds = isset($google_ads_ids) ? $google_ads_ids : ['AW-340114397'];
	$gaIds = isset($ga_measurement_ids) ? $ga_measurement_ids : ['UA-203937944-1', 'UA-203921006-1'];
?>
<?php 
$phonemin = array_map(function($val) {
	$val = str_replace(' ', '-', $val);
	$val = preg_replace('/[^A-Za-z0-9\-]/', '', $val);
	return preg_replace('/-+/', '-', $val);
}, $phone);
?>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?php if(isset($title) == true && !empty($title)) { echo $title . ' | '; } echo $sitename; ?></title>
		<meta name="description" content="">
		<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
		<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,user-scalable=no">
		<script type="application/javascript">(function(w,d,t,r,u){w[u]=w[u]||[];w[u].push({'projectId':'10000','properties':{'pixelId':'437053'}});var s=d.createElement(t);s.src=r;s.async=true;s.onload=s.onreadystatechange=function(){var y,rs=this.readyState,c=w[u];if(rs&&rs!="complete"&&rs!="loaded"){return}try{y=YAHOO.ywa.I13N.fireBeacon;w[u]=[];w[u].push=function(p){y([p])};y(c)}catch(e){}};var scr=d.getElementsByTagName(t)[0],par=scr.parentNode;par.insertBefore(s,scr)})(window,document,"script","https://s.yimg.com/wi/ytc.js","dotq");</script>
		<script src="https://cdn.optimizely.com/js/6132781645.js"></script>
		<link rel="apple-touch-icon" href="/img/favicon.png">
		<link rel="icon" href="/img/favicon.png" type="image/x-icon">
		<?php if(!empty($_SESSION['agent']))  { ?>
			<link rel='stylesheet' href='/css/bootstrap.min.css'>
		<?php } ?>
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<!-- <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600' rel='stylesheet' type='text/css'> -->
		<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Questrial|Quicksand:300,400,700'>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
		<style>
			.icon-stack { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 2.6rem; height: 2.6rem; }
			.icon-stack .icon-base svg { width: 100%; height: 100%; }
			.icon-stack .icon-overlay { position: absolute; width: 60%; height: 60%; }
			.icon-stack .icon-overlay.icon-hash { transform: translate(6%, -4%); }
			.icon-stack .icon-overlay.icon-pencil { transform: translate(8%, -6%); }
			.icon-stack .icon-overlay.icon-scale { transform: translate(-6%, 0); }
		</style>
		<link rel="stylesheet" href="css/main.css">
		<!--[if gte IE 9]>
		  <style type="text/css">
			.gradient {
			   filter: none;
			}
		  </style>
		<![endif]-->

		<style>


		</style>
		<!--[if lt IE 9]>
			<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
			<script>window.html5 || document.write('<script src="js/vendor/html5shiv.js"><\/script>')</script>
		<![endif]-->

		<?php if ($analyticsEnabled && (!isset($_GET['type']) || $_GET['type'] != 'medicare')) { ?>
			<script src="https://www.googleoptimize.com/optimize.js?id=OPT-5C3KKQB"></script>
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

			<!-- Bing 07/29/2021 -->
			<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"134598208"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>  
			<script src="https://www.googleoptimize.com/optimize.js?id=OPT-W2KQF8T"></script>
		<?php } ?>

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

	<noscript>
		<img src="http://api.trustedform.com/ns.gif" />
	</noscript>
	<!-- End TrustedForm Certificate -->
<?php
$sendTags = '';
if ((isset($_GET['city']) && !empty($_GET['city'])) || (isset($_GET['state']) &&  !empty($_GET['state']))) {
	$sendtags = 'OneSignal.sendTags({ ' . ((isset($_GET['city']) && !empty($_GET['city'])) ? 'loc_city: \'' . ucwords(strtolower($_GET['city'])) . '\', ' : '') . ((isset($_GET['state']) && !empty($_GET['state'])) ? 'loc_state: \'' . strtoupper($_GET['state']) . '\', ' : '') . '});';
}
date_default_timezone_set('America/New_York');
if ((((date('N') >= 1 && date('N') <= 4) && (date('G') <  8 || date('G') >= 22 || (date('i') == 8 && date('i') < 55))) || //  mon-thu  <  8:55am & >= 10:00pm
	 ((date('N') == 5)                   && (date('G') <  8 || date('G') >= 18 || (date('i') == 8 && date('i') < 55))) || //      fri  <  8:55am & >=  6:00pm
	 ((date('N') == 6))                                                                                                || //      sat                 ALL DAY
	 ((date('N') == 7)                   && (date('G') < 10 || date('G') >= 19))                                          //      sun  < 10:00am & >=  7:00pm
) || strtoupper($_GET['state']) == 'CA') { echo '<!-- OneSignal -->
<link rel="manifest" href="/manifest.json">
<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>
<!--<script>
	var OneSignal = window.OneSignal || [];
	OneSignal.push(["init", {
		appId: "APP_ID_HERE",
		autoRegister: true,
		notifyButton: {
			enable: false
		},
		welcomeNotification: {
			"title": "You\'re all set!",
			"message": "Thanks for subscribing! We\'ll keep you updated with the most affordable health insurance options.",
			/* "url": "http://affordable-arizona-healthcare.com/" */
		}
	}]);
	OneSignal.push(function() { ' . $sendtags . '
		OneSignal.isPushNotificationsEnabled(function(isEnabled) {
			if (!isEnabled) { OneSignal.push(function() { OneSignal.registerForPushNotifications(); }) };
		});
		OneSignal.isPushNotificationsEnabled().then(function(isEnabled) {
			if (!isEnabled) { OneSignal.push(function() { OneSignal.registerForPushNotifications(); }) };
		});
	});
</script>-->
<!-- End OneSignal -->'; }
?>
<?php include 'navbar.php'; ?>