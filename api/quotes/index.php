<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
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
// CHECK INCOMING GET REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id']) && !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        // ERROR ONLY INTEGER IS ALLOWED
        $http->badRequest("Only a valid integer is allowed to fetch a single quote");
        die();
    }
    // FETCH ONE QUOTE IF ID EXISTS OR ALL IF ID DOESN'T EXIST
    $resultsData = isset($_GET['id']) ? $quote->fetchOneQuote($_GET['id']) : $quote->fetchAllQuotes();
    $resultsInfo = $db->executeCall($username, 1000, 86400);

    if ($resultsData === 0) {
        $message = "No quote ";
        $message .= isset($_GET['id']) ? "with the id " . $_GET['id'] : "";
        $message .= " was found";
        $http->notFound($message);
    } else if ($resultsInfo === -1) {
      $http->paymentRequired();
    }else {
      $http->OK($resultsInfo, $resultsData);
    }
} else if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $quoteReceived = json_decode(file_get_contents("php://input"));
    $results = $quote->insertQuote($quoteReceived, $user_id);
    $resultsInfo = $db->executeCall($username, 1000, 86400);
    if ($results === -1) {
      $http->badRequest("A valid JSON of body and category_id fields is required");
    }else if($resultsInfo === -1) {
      $http->paymentRequired();
    }else {
      $http->OK($resultsInfo, $results);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $quoteReceived = json_decode(file_get_contents("php://input"));
  if (!$quoteReceived->id) {
    // POST ID NOT PROVIDED BAD REQUEST
    $http->badRequest("Please an id is required to make a PUT request");
    exit();
  }
  $query = "SELECT * FROM quotes WHERE id = ?";
  $results = $db->fetchOne($query, $quoteReceived->id);
  if ($results === 0) {
    // Post NOT Found
    $http->notFound("Quote with the id $quoteReceived->id was not found");
  }else if($results['user_id'] !== $user_id) {
    $http->notAuthorized("You are not authorized to update this quote");
  }else {
    // USER CAN UPDATE
    $parameters = [
      'id' => $quoteReceived->id,
      'body' => isset($quoteReceived->body) ? $quoteReceived->body : $results['body'],
      'category_id' =>isset($quoteReceived->category_id) ? $quoteReceived->category_id : $results['category_id'],
    ];

    $resultsData = $quote->updateQuote($parameters);
    $resultsInfo = $db->executeCall($username, 1000, 86400);

    if ($resultsInfo === -1)  {
      $http->paymentRequired();
    }else {
      $http->OK($resultsInfo, $resultsData);
    }
  }
} else if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
  $idReceived = json_decode(file_get_contents("php://input"));
  if (!$idReceived->id) {
    $http->badRequest("No id was provided");
    exit();
  }
  $query = "SELECT * FROM quotes WHERE id = ?";
  $results = $db->fetchOne($query, $idReceived->id);

  if ($results === 0) {
    // POST NOT FOUND
    $http->notFound("Quote with the id $idReceived->id was not found");
    exit();
  }
  if ($results['user_id'] !== $user_id) {
    // NOT AUTHORIZED TO DELETE
    $http->notAuthorized("You are not authorized to delete this quote");
  }else {
    // User CAN NOW DELETE QUOTE
    $resultsData = $quote->deleteQuote($idReceived->id);
    $resultsInfo = $db->executeCall($username, 1000, 86400);

    if ($resultsInfo === -1) {
      $http->paymentRequired();
    }else {
      $http->OK($resultsInfo, $resultsData);
    }
  }


}