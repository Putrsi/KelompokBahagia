<?php
class Playlist {
    private $conn;
    private $table = "playlists";
    private $tablePivot = "playlist_songs";

    public $id_playlist;
    public $user_id;
    public $name;
    public $cover_url;
    public $is_public;

    public function __construct($db) {
        $this->conn = $db;
    }

    // buat playlist baru
    public function buat() {
        $query = "INSERT INTO {$this->table} (user_id, NAME, cover_url, is_public)
                  VALUES (:user_id, :name, :cover_url, :is_public)";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":cover_url", $this->cover_url);
        $stmt->bindParam(":is_public", $this->is_public);

        if ($stmt->execute()) {
            $this->id_playlist = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // ambil semua playlist milik user yang login
    public function bacaByUser($user_id) {
        $query = "SELECT id_playlist, NAME, cover_url, is_public, created_at
                  FROM {$this->table}
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ambil semua playlist yang is_public = 1 (buat dilihat orang lain)
    public function bacaPublic() {
        $query = "SELECT p.id_playlist, p.NAME, p.cover_url, p.created_at, u.username
                  FROM {$this->table} p
                  JOIN USER u ON p.user_id = u.id_user
                  WHERE p.is_public = 1
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // cari 1 playlist berdasarkan id (buat cek kepemilikan & detail)
    public function cariById($id) {
        $query = "SELECT id_playlist, user_id, NAME, cover_url, is_public
                  FROM {$this->table} WHERE id_playlist = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // hapus playlist, cuma boleh yang punya user_id sama (biar gak hapus punya orang lain)
    public function hapus($id, $user_id) {
        $query = "DELETE FROM {$this->table} WHERE id_playlist = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }


    public function bacaLagu($playlist_id) {
        $query = "SELECT s.id_songs, s.title, s.file_url, a.NAME AS artist_name, ps.POSITION, ps.added_at
                  FROM {$this->tablePivot} ps
                  JOIN songs s ON ps.song_id = s.id_songs
                  JOIN artists a ON s.artist_id = a.id_artist
                  WHERE ps.playlist_id = :playlist_id
                  ORDER BY ps.POSITION ASC, ps.added_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":playlist_id", $playlist_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function tambahLagu($playlist_id, $song_id) {
        // cek biar gak dobel (unique key di DB juga udah jaga ini, tapi biar pesannya rapi)
        $cek = $this->conn->prepare("SELECT id_playlist_songs FROM {$this->tablePivot}
                                      WHERE playlist_id = :playlist_id AND song_id = :song_id LIMIT 1");
        $cek->bindParam(":playlist_id", $playlist_id);
        $cek->bindParam(":song_id", $song_id);
        $cek->execute();
        if ($cek->fetch()) {
            return "sudah_ada";
        }


        $posQuery = $this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->tablePivot} WHERE playlist_id = :playlist_id");
        $posQuery->bindParam(":playlist_id", $playlist_id);
        $posQuery->execute();
        $posisi = (int) $posQuery->fetch(PDO::FETCH_ASSOC)['total'] + 1;

        $query = "INSERT INTO {$this->tablePivot} (playlist_id, song_id, POSITION)
                  VALUES (:playlist_id, :song_id, :posisi)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":playlist_id", $playlist_id);
        $stmt->bindParam(":song_id", $song_id);
        $stmt->bindParam(":posisi", $posisi);

        return $stmt->execute() ? true : false;
    }


    public function hapusLagu($playlist_id, $song_id) {
        $query = "DELETE FROM {$this->tablePivot} WHERE playlist_id = :playlist_id AND song_id = :song_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":playlist_id", $playlist_id);
        $stmt->bindParam(":song_id", $song_id);
        return $stmt->execute();
    }
}
?>