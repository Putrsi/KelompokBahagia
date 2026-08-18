<?php
class User {
    private $conn;
    private $table = "USER";

    public $id_user;
    public $username;
    public $password_hash;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }


    public function cariByUsername($username) {
        $query = "SELECT id_user, username, password_hash, ROLE
                  FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function register() {
        $query = "INSERT INTO {$this->table} (username, password_hash)
                  VALUES (:username, :password_hash)";
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password_hash", $this->password_hash);

        if ($stmt->execute()) {
            $this->id_user = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
}
?>