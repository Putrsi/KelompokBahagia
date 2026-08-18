<?php
class SongController {
    private $db;
    private $song;

    public function __construct($db) {
        $this->db = $db;
        $this->song = new Song($db);
    }
    
    public function index() {
        $data = $this->song->bacaSemua();
        echo json_encode(["success" => true, "data" => $data]);
    }

    public function tambah($input) {
        session_start();
        if (!isset($_SESSION['artist_id'])) {
            echo json_encode(["success" => false, "message" => "Harus login dulu."]);
            return;
        }

        $judul = trim($input['judul'] ?? '');
        $url = trim($input['link'] ?? '');
        $artist_id = $input['artist_id'] ?? null;
        $genre_id = $input['genre_id'] ?? null;

        if ($judul === '' || $url === '') {
            echo json_encode(["success" => false, "message" => "Judul dan link wajib diisi."]);
            return;
        }

        if (!$artist_id || !ctype_digit((string)$artist_id)) {
            echo json_encode(["success" => false, "message" => "Artist wajib diisi (id angka)."]);
            return;
        }

        if (!$this->linkValid($url)) {
            echo json_encode(["success" => false, "message" => "Link tidak dikenali. Pakai link YouTube atau link audio (.mp3)."]);
            return;
        }

        // genre_id opsional, tapi kalau diisi harus angka valid
        if ($genre_id !== null && $genre_id !== '' && !ctype_digit((string)$genre_id)) {
            echo json_encode(["success" => false, "message" => "Genre tidak valid."]);
            return;
        }

        $this->song->title = $judul;
        $this->song->file_url = $url;
        $this->song->artist_id = (int) $artist_id;
        $this->song->genre_id = ($genre_id !== null && $genre_id !== '') ? (int) $genre_id : null;

        if ($this->song->tambah()) {
            echo json_encode(["success" => true, "message" => "Lagu ditambahkan."]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal menambahkan lagu."]);
        }
    }

    public function hapus($id) {
        session_start();
        if (!isset($_SESSION['artist_id'])) {
            echo json_encode(["success" => false, "message" => "Harus login dulu."]);
            return;
        }

        $this->song->id_songs = $id;
        if ($this->song->hapus($_SESSION['artist_id'])) {
            echo json_encode(["success" => true, "message" => "Lagu dihapus."]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal menghapus (bukan lagu kamu?)."]);
        }
    }

    private function linkValid($url) {
        if (preg_match('/(youtu\.be\/|youtube\.com\/(watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $url)) {
            return true;
        }
        if (preg_match('/\.(mp3|wav|ogg|m4a)(\?.*)?$/i', $url)) {
            return true;
        }
        return false;
    }
}
?>