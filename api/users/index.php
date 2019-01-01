<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");

require_once "../../config/Database.php";
require_once "../../models/Quote.php";
require_once "../../models/HttpResponse.php";
require_once "../../models/User.php";

$db = new Database();
$quote = new Quote($db);
$http = new HttpResponse();
$user = new User($db);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $newUser = json_decode(file_get_contents("php://input"));
  $query = "INSERT INTO users (firstName, lastName, username, password) values (?,?,?,?)";
  $nextId = $db->fetchOne("SELECT count(*) AS lastId FROM users", "");
  $results = $user->insertUser($query, $newUser, $nextId['lastId'] + 1);

  if ($results === -1) {
    $http->badRequest("A valid JSON containing firstName, lastName and password is required");
  }else {
    http_response_code(200);
    echo json_encode([
      "date_time" => date("d/m/Y h:i:s:a"),
      "version" => "1.0.0",
      "data" => $results
    ]);
  }
}