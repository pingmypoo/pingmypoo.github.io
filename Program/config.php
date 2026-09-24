<?php
// ---- Fill in the details from your hosting.udru.ac.th database ----
$DB = ['host' => 'localhost', 'name' => 'it67040233104', 'user' => 'it67040233104', 'pass' => 'S3F7J4X4'];

session_start();
try {
  $db = new PDO("mysql:host={$DB['host']};dbname={$DB['name']};charset=utf8mb4", $DB['user'], $DB['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  exit('เชื่อมต่อฐานข้อมูลไม่ได้ กรุณาตรวจสอบค่าใน config.php');
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function need_login() { if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; } }
function login_as($id, $name) { session_regenerate_id(true); $_SESSION['uid'] = $id; $_SESSION['uname'] = $name; }
