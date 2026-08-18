<?php
class Artist {
    private $conn;
    private $table = "artists";

    public $id_artist;
    public $name;
    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }


    public function buat() {
        $query = "INSERT INTO {$this->table} (NAME, user_id) VALUES (:name, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            $this->id_artist = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // cari id_artist berdasarkan user yang login
    public function cariByUserId($user_id) {
        $query = "SELECT id_artist, NAME FROM {$this->table} WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>