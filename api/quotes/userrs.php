<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");

require_once "../../config/Database.php";
require_once "../../models/Quote.php";
require_once "../../models/HttpResponse.php";

$db = new Database();
$quote = new Quote($db);
$http = new HttpResponse();

if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
  if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
      $http->notAuthorized("You must authenticate yourself before you can use our REST API services");
      exit();
  } else {
      $username = $_SERVER['PHP_AUTH_USER'];
      $password = $_SERVER['PHP_AUTH_PW'];
      $query = "SELECT * FROM users WHERE username = ?";
      $results = $db->fetchOne($query, $username);
      if ($results === 0 || $results['password'] !== $password) {
          $http->notAuthorized("You provided wrong credentials");
          exit();
      } else {
          $user_id = $results['id'];
      }
  }
  }

if (!isset($_GET['id'])) {
  // USER ID MUST BE PROVIDED 
  $http->badRequest("Please provide the user id to fetch the quote relating to that particular user");
  exit();
}
if (isset($_GET['id']) && !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
  // ERROR ONLY INTEGERS ARE ALLOWED
  $http->badRequest("Only Integers are allowed");
  exit();
}

$id = $_GET['id'];
$resultsData = $quote->fetchUsersQuote($id);
$resultsInfo = $db->executeCall($username, 1000, 86400);

if ($resultsData === 0) {
  $http->notFound("User with the id $id quote doesn't exist");
}else if ($resultsInfo === -1) {
  $http->paymentRequired();
}else {
  $http->OK($resultsInfo, $resultsData);
}