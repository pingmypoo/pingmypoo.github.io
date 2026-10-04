<?php
require_once __DIR__ . '/includes/config.php'; check_csrf();
if (empty($_SESSION['role'])) { header('Location: login.php'); exit; }
$p = q('SELECT p.*, b.*, p.amount, p.booking_id, m.fname, m.lname, c.costume_name FROM tb_payment p JOIN tb_booking b ON b.booking_id=p.booking_id JOIN tb_member m ON m.member_id=b.member_id JOIN tb_costume c ON c.costume_id=b.costume_id WHERE p.payment_id=?', [$_GET['id'] ?? ''])->fetch();
if (!$p || !$p['receipt_no'] || ($_SESSION['role'] === 'member' && $p['member_id'] !== $_SESSION['uid'])) die('ไม่พบใบเสร็จ');
require __DIR__ . '/includes/header.php'; ?>
<div class="card" style="max-width:520px;margin:auto"><h2>ใบเสร็จรับเงิน</h2>
เลขที่: <?= e($p['receipt_no']) ?><br>วันที่: <?= e($p['payment_date']) ?><br>ลูกค้า: <?= e($p['fname'] . ' ' . $p['lname']) ?><br>
รายการ: ค่าเช่า/มัดจำ ชุด <?= e($p['costume_name']) ?> (<?= e($p['booking_id']) ?>)<br>ช่องทาง: <?= e($p['payment_method']) ?><br>
<h3>จำนวนเงิน <?= baht($p['amount']) ?> บาท</h3><button class="noprint" onclick="print()">พิมพ์</button></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
