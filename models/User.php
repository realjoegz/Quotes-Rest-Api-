<?php
class User {
  private $db;
  public function __construct(Database $db) {
    $this->db = $db;
  }
  public function insertUser($query, $parameters, $id) {
    if (isset($parameters->firstName) && isset($parameters->lastName) && isset($parameters->password)) {
      $firstName = $parameters->firstName;
      $lastName = $parameters->lastName;
      $password = $parameters->password;
      $username =  strtolower($firstName). strtolower($lastName) . $id;
      $this->db->insertUser($query, $firstName, $lastName, $password, $username);
      return [
        "firstName" => $firstName,
        "lastName" => $lastName,
        "username" => $username,
        "api_key" => base64_encode("$username:$password"),
      ];
    }else {
      return -1;
    }
  }
}