<?php
class Song {
    private $conn;
    private $table = "songs";

    public $id_songs;
    public $title;
    public $artist_id;
    public $file_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ambil semua lagu, join biar keliatan nama artist-nya
    public function bacaSemua() {
        $query = "SELECT s.id_songs, s.title, s.file_url, s.artist_id, a.NAME AS artist_name
                  FROM {$this->table} s
                  JOIN artists a ON s.artist_id = a.id_artist
                  ORDER BY s.id_songs ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // tambah lagu baru
    public function tambah() {
        $query = "INSERT INTO {$this->table} (title, artist_id, file_url)
                  VALUES (:title, :artist_id, :file_url)";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->file_url = htmlspecialchars(strip_tags($this->file_url));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":artist_id", $this->artist_id);
        $stmt->bindParam(":file_url", $this->file_url);

        if ($stmt->execute()) {
            $this->id_songs = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // hapus lagu, cuma boleh yang punya artist_id sama (biar gak hapus punya orang lain)
    public function hapus($artist_id) {
        $query = "DELETE FROM {$this->table} WHERE id_songs = :id AND artist_id = :artist_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id_songs);
        $stmt->bindParam(":artist_id", $artist_id);
        return $stmt->execute();
    }
}
?>