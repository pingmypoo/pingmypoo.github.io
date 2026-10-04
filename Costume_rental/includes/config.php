<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
$pdo = new PDO('mysql:host=localhost;dbname=costume_rental_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$BASE = basename(dirname($_SERVER['SCRIPT_NAME'])) === 'admin' ? '../' : '';
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function need($role) { global $BASE; if (($_SESSION['role'] ?? '') !== $role) { header("Location: {$BASE}login.php"); exit; } }
function csrf() { if (empty($_SESSION['t'])) $_SESSION['t'] = bin2hex(random_bytes(16)); return '<input type="hidden" name="t" value="' . $_SESSION['t'] . '">'; }
function check_csrf() { if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['t'] ?? '', $_POST['t'] ?? '')) die('Invalid token'); }
function next_id($t, $c, $p, $n) {
    global $pdo; $m = $pdo->query("SELECT MAX($c) m FROM $t")->fetch()['m'];
    return $p . str_pad($m ? (int)substr($m, strlen($p)) + 1 : 1, $n, '0', STR_PAD_LEFT);
}
function days($a, $b) { return max(1, (int)round((strtotime($b) - strtotime($a)) / 86400)); }
function q($sql, $args = []) { global $pdo; $s = $pdo->prepare($sql); $s->execute($args); return $s; }
function refresh_costume($cid) {   // ว่าง/ถูกจอง ตามรายการจอง-เช่าที่ยังไม่จบ (ไม่แตะสถานะ ซ่อมแซม)
    $n = q("SELECT COUNT(*) c FROM tb_booking WHERE costume_id=? AND status IN('จอง','กำลังเช่า')", [$cid])->fetch()['c'];
    q("UPDATE tb_costume SET status=? WHERE costume_id=? AND status<>'ซ่อมแซม'", [$n > 0 ? 'ถูกจอง' : 'ว่าง', $cid]);
}
function paid_sum($bid) { return (float)q('SELECT COALESCE(SUM(amount),0) s FROM tb_payment WHERE booking_id=?', [$bid])->fetch()['s']; }
function baht($n) { return number_format((float)$n, 2); }
