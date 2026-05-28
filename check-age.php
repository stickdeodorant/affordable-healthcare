<?php 

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$dob = $_GET['date'];

getAge($dob);

function getAge($birthday){
  $DOB = preg_replace('~\D~', '/', $birthday);
  $bday = new DateTime($DOB);
  // print_r($bday);
  $bdayDay = $bday->format('d');
  $firstDayofMonth = '-' . $bdayDay+1 . 'days';
  // echo($firstDayofMonth);
  $bday->modify($firstDayofMonth);
  // print_r($bday);
  $today = new DateTime('midnight today');
  $difference = $bday->diff($today);
  // print_r($difference);
  $ageMonths = $difference->format('%m') + 12 * $difference->format('%y');
  $age = ceil($ageMonths/12);


  // set response code - 200 OK
  if ($birthday != '') {
    http_response_code(200);
  } else {
    http_response_code(500);
  }
  
  // show products data in json format
  if ($birthday != '') {
    echo json_encode($age);
  } else {
    echo json_encode('Please enter a valid date');
  }
 }

//ORINGIAL FUNCTION
// function getAge($birthday){
//   $bday = new DateTime($birthday);
//   $difference = $bday->diff(new DateTime());
//   $ageMonths = $difference->format('%m') + 12 * $difference->format('%y');
//   if ($ageMonths < 774) { // If user is younger than 64.5 
//     $age = 'T65-SM-u';
//   } else {
//     $age = 'T65-SM';
//   }

//   // set response code - 200 OK
//   if ($birthday != '') {
//     http_response_code(200);
//   } else {
//     http_response_code(500);
//   }
  
//   // show products data in json format
//   if ($birthday != '') {
//     echo json_encode($age);
//   } else {
//     echo json_encode('Please enter a valid date');
//   }

//   //return $year_diff;
//  }
?>