<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$user = 'root';
$pass = 'Stego';
$db   = 'db_lms';
$port = 3306;

try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Koneksi ke database LMS gagal. Pastikan layanan MySQL aktif dan database db_lms sudah dibuat.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function validasiId($nilai): ?int {
    if ($nilai === null || $nilai === '' || !ctype_digit((string)$nilai)) {
        return null;
    }
    $id = (int)$nilai;
    return $id > 0 ? $id : null;
}

function cekTokenCsrf(?string $token): bool {
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function setPesan(string $tipe, string $teks): void {
    $_SESSION['flash_pesan'] = ['tipe' => $tipe, 'teks' => $teks];
}

function ambilPesan(): ?array {
    if (isset($_SESSION['flash_pesan'])) {
        $pesan = $_SESSION['flash_pesan'];
        unset($_SESSION['flash_pesan']);
        return $pesan;
    }
    return null;
}

function hitungData(mysqli $conn, string $namaTabel): int {
    $tabelDiizinkan = ['mata_kuliah', 'materi', 'tugas'];
    if (!in_array($namaTabel, $tabelDiizinkan, true)) {
        return 0;
    }
    $sql = "SELECT COUNT(*) AS total FROM {$namaTabel}";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_assoc();
    return (int)($hasil['total'] ?? 0);
}