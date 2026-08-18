<?php
class PlaylistController {
    private $db;
    private $playlist;

    public function __construct($db) {
        $this->db = $db;
        $this->playlist = new Playlist($db);
    }

    // liat semua playlist milik user yang login
    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Harus login dulu."]);
            return;
        }

        $data = $this->playlist->bacaByUser($_SESSION['user_id']);
        echo json_encode(["success" => true, "data" => $data]);
    }

    // liat playlist publik (buat explore / dilihat orang lain)
    public function publik() {
        $data = $this->playlist->bacaPublic();
        echo json_encode(["success" => true, "data" => $data]);
    }

    // liat detail 1 playlist + isi lagunya
    public function detail($id) {
        $data = $this->playlist->cariById($id);

        if (!$data) {
            echo json_encode(["success" => false, "message" => "Playlist tidak ditemukan."]);
            return;
        }

        // kalau playlist private, cuma pemiliknya yang boleh liat
        if (!$data['is_public']) {
            session_start();
            if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $data['user_id']) {
                echo json_encode(["success" => false, "message" => "Playlist ini private."]);
                return;
            }
        }

        $data['songs'] = $this->playlist->bacaLagu($id);
        echo json_encode(["success" => true, "data" => $data]);
    }

    // bikin playlist baru
    public function tambah($input) {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Harus login dulu."]);
            return;
        }

        $name = trim($input['name'] ?? '');
        $cover = trim($input['cover_url'] ?? '');
        $isPublic = isset($input['is_public']) ? (int) $input['is_public'] : 1;

        if ($name === '') {
            echo json_encode(["success" => false, "message" => "Nama playlist wajib diisi."]);
            return;
        }

        $this->playlist->user_id = $_SESSION['user_id'];
        $this->playlist->name = $name;
        $this->playlist->cover_url = $cover !== '' ? $cover : null;
        $this->playlist->is_public = $isPublic;

        if ($this->playlist->buat()) {
            echo json_encode(["success" => true, "message" => "Playlist dibuat.", "id_playlist" => $this->playlist->id_playlist]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal membuat playlist."]);
        }
    }

    // hapus playlist
    public function hapus($id) {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Harus login dulu."]);
            return;
        }

        if ($this->playlist->hapus($id, $_SESSION['user_id'])) {
            echo json_encode(["success" => true, "message" => "Playlist dihapus."]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal menghapus (bukan playlist kamu?)."]);
        }
    }

    // tambah lagu ke playlist
    public function tambahLagu($input) {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Harus login dulu."]);
            return;
        }

        $playlist_id = $input['playlist_id'] ?? null;
        $song_id = $input['song_id'] ?? null;

        if (!$playlist_id || !$song_id) {
            echo json_encode(["success" => false, "message" => "playlist_id dan song_id wajib diisi."]);
            return;
        }

        // cek dulu playlist ini emang punya user yang login
        $data = $this->playlist->cariById($playlist_id);
        if (!$data || $data['user_id'] != $_SESSION['user_id']) {
            echo json_encode(["success" => false, "message" => "Playlist tidak ditemukan / bukan milikmu."]);
            return;
        }

        $hasil = $this->playlist->tambahLagu($playlist_id, $song_id);

        if ($hasil === "sudah_ada") {
            echo json_encode(["success" => false, "message" => "Lagu sudah ada di playlist ini."]);
        } elseif ($hasil === true) {
            echo json_encode(["success" => true, "message" => "Lagu ditambahkan ke playlist."]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal menambahkan lagu."]);
        }
    }

    // hapus lagu dari playlist
    public function hapusLagu($input) {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Harus login dulu."]);
            return;
        }

        $playlist_id = $input['playlist_id'] ?? null;
        $song_id = $input['song_id'] ?? null;

        if (!$playlist_id || !$song_id) {
            echo json_encode(["success" => false, "message" => "playlist_id dan song_id wajib diisi."]);
            return;
        }

        $data = $this->playlist->cariById($playlist_id);
        if (!$data || $data['user_id'] != $_SESSION['user_id']) {
            echo json_encode(["success" => false, "message" => "Playlist tidak ditemukan / bukan milikmu."]);
            return;
        }

        if ($this->playlist->hapusLagu($playlist_id, $song_id)) {
            echo json_encode(["success" => true, "message" => "Lagu dihapus dari playlist."]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal menghapus lagu dari playlist."]);
        }
    }
}
?>