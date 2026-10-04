<?php
require_once __DIR__ . '/../includes/config.php'; need('employee'); check_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b = q('SELECT * FROM tb_booking WHERE booking_id=?', [$_POST['id']])->fetch();
    if ($b && $b['status'] === 'จอง') {
        if ($_POST['act'] === 'confirm') q("UPDATE tb_booking SET status='กำลังเช่า', emp_id=? WHERE booking_id=?", [$_SESSION['uid'], $b['booking_id']]);
        if ($_POST['act'] === 'cancel') { q("UPDATE tb_booking SET status='ยกเลิก', emp_id=? WHERE booking_id=?", [$_SESSION['uid'], $b['booking_id']]); refresh_costume($b['costume_id']); }
    }
    header('Location: bookings.php'); exit;
}
$kw = '%' . ($_GET['q'] ?? '') . '%';
$rows = q("SELECT b.*, c.costume_name, CONCAT(m.fname,' ',m.lname) mname FROM tb_booking b JOIN tb_costume c ON c.costume_id=b.costume_id JOIN tb_member m ON m.member_id=b.member_id
 WHERE b.booking_id LIKE ? OR m.fname LIKE ? OR m.lname LIKE ? OR c.costume_name LIKE ? ORDER BY b.booking_id DESC LIMIT 200", [$kw, $kw, $kw, $kw])->fetchAll();
require __DIR__ . '/../includes/header.php'; ?>
<h2>การจอง/เช่า</h2>
<form><input name="q" placeholder="ค้นหารหัสการจอง / ชื่อลูกค้า / ชื่อชุด" value="<?= e($_GET['q'] ?? '') ?>" style="width:320px"> <button>ค้นหา</button></form><br>
<table><tr><th>รหัส</th><th>ลูกค้า</th><th>ชุด</th><th>เช่า - คืน</th><th>ยอดค่าเช่า</th><th>ชำระแล้ว</th><th>สถานะ</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['booking_id']) ?></td><td><?= e($r['mname']) ?></td><td><?= e($r['costume_name']) ?></td>
<td><?= e($r['rent_date']) ?> → <?= e($r['due_return_date']) ?></td><td><?= baht($r['total_amount']) ?></td><td><?= baht(paid_sum($r['booking_id'])) ?></td><td><?= e($r['status']) ?></td>
<td><?php if ($r['status'] === 'จอง'): ?><form method="post" style="display:inline"><?= csrf() ?><input type="hidden" name="id" value="<?= e($r['booking_id']) ?>">
<button name="act" value="confirm">ยืนยันการเช่า</button> <button class="red" name="act" value="cancel" onclick="return confirm('ยกเลิกรายการ?')">ยกเลิก</button></form><?php endif; ?></td></tr><?php endforeach; ?></table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
