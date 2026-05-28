<?php
    if(session_status() === PHP_SESSION_NONE) session_start(); 
    error_reporting(0);
    include '../inc/globalvars.php';
?>
<!DOCTYPE html>
<?php 
    $_SESSION['gclid'] = $_GET['gclid'];
    
	$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	if (strpos($url,'alpha') !== false) {
			$phoneVer = 'kobe1';
			$_SESSION['typ_phone'] = 'kobe_typ';
			$phoneVer = 'kobe2';
	} else {
		$phoneVersion = $_GET['num'];
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
	$email = $_SESSION['Email'] ? $_SESSION['Email'] : $_GET['email'];
	$userPhone = $_SESSION['Primary_Phone'] ? $_SESSION['Primary_Phone'] : $_GET['phone'];

?>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,user-scalable=no">
	<title><?php if(isset($title) == true && !empty($title)) { echo $title . ' | '; } echo $sitename; ?></title>
	<script src="https://www.googleoptimize.com/optimize.js?id=OPT-5C3KKQB"></script>
	
    <!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-5DHQH9H');</script>
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-NGLCRXJH');</script>
	<!-- End Google Tag Manager -->
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-MJMNPM5');</script>
	<!-- End Google Tag Manager -->

	<!-- 07/06/2021 Global site tag (gtag.js) - Google Ads: 340114397 --><script async src="https://www.googletagmanager.com/gtag/js?id=AW-340114397"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'AW-340114397');</script>
	<!-- 08/03/2021 Global site tag (gtag.js) - Google Analytics --><script async src="https://www.googletagmanager.com/gtag/js?id=UA-203937944-1"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'UA-203937944-1');</script>
	<!-- 08/03/2021 Global site tag (gtag.js) - Google Analytics --><script async src="https://www.googletagmanager.com/gtag/js?id=UA-203921006-1"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'UA-203921006-1');</script>
	<!-- 08/03/2021 Google Tag Manager --><script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-MJMNPM5');</script>
	<!-- End Google Tag Manager -->

	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=AW-340114397"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', 'AW-340114397');
	</script>

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
	<?php /*<link rel='stylesheet' href='/css/fonts.css'>*/ ?>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link href="../../fa5/web-fonts-with-css/css/fontawesome-all.min.css" rel="stylesheet">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="css/style.css">
	<link rel="apple-touch-icon" href="../img/favicon.png">
	<link rel="icon" href="../img/favicon.png" type="image/x-icon">
	<script defer src="../fa5/svg-with-js/js/fontawesome-all.min.js"></script>
	<!-- Bing 07/29/2021 -->
	<!-- <script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"134598208"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>   -->
	<!-- Bing 11/01/2032 -->
	<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"134598208", enableAutoSpaTracking: true};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>
	<script>
   window.uetq = window.uetq || [];
   window.uetq.push('set', { 'pid': {
      'em': '<?php echo $email; ?>'
      'ph': '<?php echo $userPhone; ?>',
   } });
</script>
</head>
<body>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MJMNPM5"height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NGLCRXJH" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php include 'inc/navbar.php'; ?>