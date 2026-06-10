<?php 
session_start();
require_once __DIR__ . '/../inc/env.php';
include '../inc/globalvars.php';

if (isset($_SERVER['HTTP_CLIENT_IP']))
  $ip = $_SERVER['HTTP_CLIENT_IP'];
else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
else if(isset($_SERVER['HTTP_X_FORWARDED']))
  $ip = $_SERVER['HTTP_X_FORWARDED'];
else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
  $ip = $_SERVER['HTTP_FORWARDED_FOR'];
else if(isset($_SERVER['HTTP_FORWARDED']))
  $ip = $_SERVER['HTTP_FORWARDED'];
else if(isset($_SERVER['REMOTE_ADDR']))
  $ip = $_SERVER['REMOTE_ADDR'];

$conn = get_db_connection();
?>

<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,user-scalable=no">
	<title><?php if(isset($title) == true && !empty($title)) { echo $title . ' | '; } echo $sitename; ?></title>
	<link rel='stylesheet' href='../css/bootstrap.min.css'>
	<link rel='stylesheet' href='../css/fonts.css'>
	<link href="/fa5/web-fonts-with-css/css/fontawesome-all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../css/style.css">
	<link rel="apple-touch-icon" href="../img/favicon.png">
	<link rel="icon" href="../img/favicon.png" type="image/x-icon">
	<script defer src="/fa5/svg-with-js/js/fontawesome-all.min.js"></script>
	<!-- Bing 07/29/2021 -->
	<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"134598208"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>  
</head>
<body>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MJMNPM5"height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php include '../inc/navbar.php'; ?>

<div class="container py-5 my-5">
 
 <form method="post" action="../get-quotes/inc/download_confirmation.php" class="text-center">
  <input class="btn btn-primary text-white border-0 mt-5 py-3 px-5" style="background-color:#67cdcb!important" type="submit" value="Export Full List" name="Export">
 
  <table class="table table-striped table-hover mt-5">
    <thead>
      <tr>
        <th>ID</th>
        <th>Email</th>
        <th>IP Address</th>
        <th>Date</th>
        <th>Time</th>
        <th>Referrer</th>
      </tr>
    </thead>
    <?php 
     $query = "SELECT * FROM bademail ORDER BY id DESC LIMIT 1000";
     $result = mysqli_query($conn,$query);
     $user_arr = array();
     while($row = mysqli_fetch_array($result)){
      $id = $row['ID'];
      $email = $row['email'];
      $ipaddress = $row['ip_address'];
      $timestamp = $row['timestamp'];
      $date = date('d/m/Y', strtotime($timestamp));
      $time = date('h:s:i A', strtotime($timestamp));
      $referrer = $row['referrer'];
      $user_arr[] = array($id,$email,$ipaddress,$timestamp,$referrer);
   ?>
    <tbody>
      <tr>
        <td><?php echo $id; ?></td>
        <td><?php echo $email; ?></td>
        <td><?php echo $ipaddress; ?></td>
        <td><?php echo $date; ?></td>
        <td><?php echo $time; ?></td>
        <td><?php echo $referrer; ?></td>
      </tr>
    </tbody>
   <?php
    }
   ?>
   </table>
   <?php 
    $serialize_user_arr = serialize($user_arr);
   ?>
  <textarea name='export_data' style='display: none;'><?php echo $serialize_user_arr; ?></textarea>
 </form>
</div>

<?php include '../inc/footer.php'; ?>