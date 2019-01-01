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

if (isset($_GET['number']) && !filter_var($_GET['number'], FILTER_VALIDATE_INT)) {
  // ERROR ONLY INTEGER IS ALLOWED
  $http->badRequest("Only a valid integer is allowed to fetch random number of quotes");
  exit();
}

$limit = isset($_GET['number']) ? $_GET['number'] : 1;

$resultsData = $quote->fetchRandomQuotes($limit);
$resultsInfo = $db->executeCall($username, 1000, 86400);

if ($resultsData === 0) {
  $http->notFound("No data was found");
}else if ($resultsInfo === -1) {
  $http->paymentRequired();
}else {
  $http->OK($resultsInfo, $resultsData);
}