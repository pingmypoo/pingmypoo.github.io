<?php
// ---- Fill in the details from your hosting.udru.ac.th database ----
$DB = ['host' => 'localhost', 'name' => 'YOUR_DB_NAME', 'user' => 'YOUR_DB_USER', 'pass' => 'YOUR_DB_PASSWORD'];

session_start();
try {
  $db = new PDO("mysql:host={$DB['host']};dbname={$DB['it67040233104']};charset=utf8mb4", $DB['it67040233104'], $DB['S3F7J4X4'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  exit('เชื่อมต่อฐานข้อมูลไม่ได้ กรุณาตรวจสอบค่าใน config.php');
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function need_login() { if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; } }
function login_as($id, $name) { session_regenerate_id(true); $_SESSION['uid'] = $id; $_SESSION['uname'] = $name; }
