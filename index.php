<?php

$host = "localhost";
$dbname = "db_musik";
$dbuser = "root";
$dbpass = "";

$db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass);

require_once "User.php";
require_once "Artist.php";
require_once "Song.php";
require_once "Playlist.php";
require_once "Authcontrollers.php";
require_once "Songcontroller.php";
require_once "PlaylistController.php";


$url = $_GET['url'] ?? '';
$url = trim($url, '/');
$parts = explode('/', $url); 
$resource = $parts[0] ?? '';
$action = $parts[1] ?? '';
$id = $parts[2] ?? null;

$input = json_decode(file_get_contents("php://input"), true) ?? [];

switch ($resource) {

    case 'auth':
        $controller = new AuthController($db);
        if ($action === 'register') {
            $controller->register($input);
        } elseif ($action === 'login') {
            $controller->login($input);
        } elseif ($action === 'logout') {
            $controller->logout();
        } else {
            echo json_encode(["success" => false, "message" => "Action tidak dikenali."]);
        }
        break;

    case 'songs':
        $controller = new SongController($db);
        if ($action === 'tambah') {
            $controller->tambah($input);
        } elseif ($action === 'hapus') {
            $controller->hapus($id);
        } else {
            $controller->index(); // default: GET /songs -> liat semua lagu
        }
        break;

    case 'playlists':
        $controller = new PlaylistController($db);
        if ($action === 'tambah') {
            $controller->tambah($input);
        } elseif ($action === 'hapus') {
            $controller->hapus($id);
        } elseif ($action === 'tambah-lagu') {
            $controller->tambahLagu($input);
        } elseif ($action === 'hapus-lagu') {
            $controller->hapusLagu($input);
        } elseif ($action === 'publik') {
            $controller->publik();
        } elseif ($action !== '' && is_numeric($action)) {
            $controller->detail($action); 
        } else {
            $controller->index(); 
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Endpoint tidak ditemukan."]);
        break;
}
?>