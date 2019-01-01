<?php
class Quote {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function fetchAllQuotes() {
        $query = "SELECT
    quotes.id, quotes.body, quotes.date, users.firstName, users.lastName, categories.name As categoryName FROM quotes
    LEFT JOIN categories ON quotes.category_id = categories.id
    LEFT JOIN users ON quotes.user_id = users.id";
        return $this->db->fetchAll($query);
    }
    public function fetchOneQuote($parameter) {
        $query = "SELECT
    quotes.id, quotes.body, quotes.date, users.firstName, users.lastName, categories.name As categoryName FROM quotes
    LEFT JOIN categories ON quotes.category_id = categories.id
    LEFT JOIN users ON quotes.user_id = users.id
    WHERE quotes.id = ?";
        return $this->db->fetchOne($query, $parameter);
    }

    public function fetchRandomQuotes($limit) {
        $query = "SELECT
    quotes.id, quotes.body, quotes.date, users.firstName, users.lastName, categories.name As categoryName FROM quotes
    LEFT JOIN categories ON quotes.category_id = categories.id
    LEFT JOIN users ON quotes.user_id = users.id
    ORDER BY RAND()
    LIMIT $limit";
        return $this->db->fetchAll($query);
    }

    public function fetchUsersQuote($id) {
        $query = "SELECT
    quotes.id, quotes.body, quotes.date, users.firstName, users.lastName, categories.name As categoryName FROM quotes
    LEFT JOIN categories ON quotes.category_id = categories.id
    LEFT JOIN users ON quotes.user_id = users.id
    WHERE users.id = '$id'
    ORDER BY quotes.date ";
        return $this->db->fetchAll($query);
    }
    public function insertQuote($parameters, $user_id) {
        $query = "INSERT INTO quotes (body, user_id, category_id, date) VALUES (?, ?, ?,?)";
        if (isset($parameters->body) && isset($parameters->category_id)) {
            $body = $parameters->body;
            $category_id = $parameters->category_id;
            $date = date("d/m/Y");
            $this->db->insertOne($query, $body, $user_id, $category_id, $date);
            return $parameters;
        }else {
          return -1;
        }
    }
    public function updateQuote($parameters) {
        $query = "UPDATE quotes SET
    body = ?, category_id = ? WHERE id = ?";
        if (isset($parameters['body']) && isset($parameters['id']) && isset($parameters['category_id'])) {
            $id = $parameters['id'];
            $body = $parameters['body'];
            $category_id = $parameters['category_id'];
            $results = $this->db->updateOne($query, $body, $category_id, $id);
            return $parameters;
        } else {
            return -1;
        }
    }
    public function deleteQuote($id) {
        $query = "DELETE FROM quotes WHERE id = ?";
        $results = $this->db->deleteOne($query, $id);
        return [
            "message" => "Quote with the id $id was successfully deleted",
        ];
    }

}
