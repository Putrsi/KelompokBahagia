<?php
class AuthController {
    private $db;
    private $user;
    private $artist;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->artist = new Artist($db);
    }

    public function register($input) {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if ($username === '' || $password === '') {
            echo json_encode(["success" => false, "message" => "Username dan password wajib diisi."]);
            return;
        }

        if (strlen($password) < 6) {
            echo json_encode(["success" => false, "message" => "Password minimal 6 karakter."]);
            return;
        }

        if ($this->user->cariByUsername($username)) {
            echo json_encode(["success" => false, "message" => "Username sudah dipakai."]);
            return;
        }

        $this->user->username = $username;
        $this->user->password_hash = password_hash($password, PASSWORD_DEFAULT);

        if (!$this->user->register()) {
            echo json_encode(["success" => false, "message" => "Gagal mendaftar."]);
            return;
        }

        $this->artist->name = $username;
        $this->artist->user_id = $this->user->id_user;
        $this->artist->buat();

        echo json_encode(["success" => true, "message" => "Berhasil daftar! Silakan login."]);
    }


    public function login($input) {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        $data = $this->user->cariByUsername($username);

        if (!$data || !password_verify($password, $data['password_hash'])) {
            echo json_encode(["success" => false, "message" => "Username atau password salah."]);
            return;
        }

        $artistData = $this->artist->cariByUserId($data['id_user']);

        session_start();
        $_SESSION['user_id'] = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['artist_id'] = $artistData['id_artist'];

        echo json_encode([
            "success" => true,
            "message" => "Login berhasil.",
            "username" => $data['username']
        ]);
    }


    public function logout() {
        session_start();
        session_destroy();
        echo json_encode(["success" => true, "message" => "Logout berhasil."]);
    }
}
?>