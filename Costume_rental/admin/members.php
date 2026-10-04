<?php
require_once __DIR__ . '/../includes/config.php'; need('employee');
$kw = '%' . ($_GET['q'] ?? '') . '%';
$rows = q('SELECT * FROM tb_member WHERE member_id LIKE ? OR fname LIKE ? OR lname LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY member_id DESC', [$kw, $kw, $kw, $kw, $kw])->fetchAll();
require __DIR__ . '/../includes/header.php'; ?>
<h2>ข้อมูลสมาชิก</h2><form><input name="q" placeholder="ค้นหารหัส/ชื่อ/เบอร์/อีเมล" value="<?= e($_GET['q'] ?? '') ?>" style="width:300px"> <button>ค้นหา</button></form><br>
<table><tr><th>รหัส</th><th>ชื่อ-นามสกุล</th><th>เบอร์โทร</th><th>อีเมล</th><th>ที่อยู่</th><th>วันที่สมัคร</th><th>สถานะ</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['member_id']) ?></td><td><?= e($r['fname'] . ' ' . $r['lname']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['address']) ?></td><td><?= e($r['register_date']) ?></td><td><?= e($r['status']) ?></td></tr><?php endforeach; ?></table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
