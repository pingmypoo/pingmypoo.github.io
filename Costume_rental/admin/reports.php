<?php
require_once __DIR__ . '/../includes/config.php'; need('employee');
$type = $_GET['type'] ?? '1'; $from = $_GET['from'] ?? date('Y-m-01'); $to = $_GET['to'] ?? date('Y-m-d');
$fine = "COALESCE((SELECT SUM(f.fine_amount) FROM tb_return r JOIN tb_fine f ON f.return_id=r.return_id WHERE r.booking_id=b.booking_id),0)";
$W = "b.status<>'ยกเลิก' AND b.rent_date BETWEEN ? AND ?";
if ($type === '1') $data = q("SELECT b.rent_date, b.booking_id, CONCAT(m.fname,' ',m.lname) mname, c.costume_name, b.total_amount, $fine fine FROM tb_booking b JOIN tb_member m ON m.member_id=b.member_id JOIN tb_costume c ON c.costume_id=b.costume_id WHERE $W ORDER BY b.rent_date", [$from, $to])->fetchAll();
elseif ($type === '2') $data = q("SELECT c.costume_id, c.costume_name, COUNT(*) n, SUM(b.total_amount) rev FROM tb_booking b JOIN tb_costume c ON c.costume_id=b.costume_id WHERE $W GROUP BY c.costume_id, c.costume_name ORDER BY n DESC, rev DESC LIMIT 5", [$from, $to])->fetchAll();
else $data = q("SELECT m.member_id, CONCAT(m.fname,' ',m.lname) mname, COUNT(*) n, SUM(b.total_amount + $fine) spend FROM tb_booking b JOIN tb_member m ON m.member_id=b.member_id WHERE $W GROUP BY m.member_id, mname ORDER BY spend DESC", [$from, $to])->fetchAll();
require __DIR__ . '/../includes/header.php'; ?>
<h2>จัดทำรายงานสรุปผลการดำเนินงาน</h2>
<form class="card grid noprint"><select name="type"><option value="1" <?= $type === '1' ? 'selected' : '' ?>>รายงานสรุปยอดเช่าและรายได้</option><option value="2" <?= $type === '2' ? 'selected' : '' ?>>รายงานชุดยอดนิยม</option><option value="3" <?= $type === '3' ? 'selected' : '' ?>>รายงานสรุปข้อมูลสมาชิก</option></select>
<input type="date" name="from" value="<?= e($from) ?>"><input type="date" name="to" value="<?= e($to) ?>"><button>สร้างรายงาน</button></form>
<h3>ช่วงวันที่ <?= e($from) ?> ถึง <?= e($to) ?></h3>
<?php if ($type === '1'):
 $mon = []; $sum = [0, 0, 0]; foreach ($data as $d) { $k = substr($d['rent_date'], 0, 7); $mon[$k] = ($mon[$k] ?? 0) + $d['total_amount'] + $d['fine']; $sum[0] += $d['total_amount']; $sum[1] += $d['fine']; }
 $max = max($mon ?: [1]); ?>
<div class="card"><b>กราฟยอดเช่ารายเดือน (บาท)</b><div style="display:flex;align-items:flex-end;gap:10px;height:150px;margin-top:8px">
<?php foreach ($mon as $k => $v): ?><div style="text-align:center;flex:1"><div style="background:#b98ab0;height:<?= round($v / $max * 110) ?>px"></div><small><?= e($k) ?><br><?= baht($v) ?></small></div><?php endforeach; ?></div></div>
<table><tr><th>วันที่</th><th>รหัสการเช่า</th><th>ชื่อลูกค้า</th><th>ชื่อชุด</th><th>ค่าเช่า (บาท)</th><th>ค่าปรับ (บาท)</th><th>รวม (บาท)</th></tr>
<?php foreach ($data as $d): ?><tr><td><?= e($d['rent_date']) ?></td><td><?= e($d['booking_id']) ?></td><td><?= e($d['mname']) ?></td><td><?= e($d['costume_name']) ?></td><td><?= baht($d['total_amount']) ?></td><td><?= baht($d['fine']) ?></td><td><?= baht($d['total_amount'] + $d['fine']) ?></td></tr><?php endforeach; ?>
<tr><th colspan="4">รวม</th><th><?= baht($sum[0]) ?></th><th><?= baht($sum[1]) ?></th><th><?= baht($sum[0] + $sum[1]) ?></th></tr></table>
<?php elseif ($type === '2'): ?>
<table><tr><th>ลำดับ</th><th>รหัสชุด</th><th>ชื่อชุด</th><th>จำนวนครั้งที่เช่า</th><th>รายได้รวม (บาท)</th></tr>
<?php foreach ($data as $i => $d): ?><tr><td><?= $i + 1 ?></td><td><?= e($d['costume_id']) ?></td><td><?= e($d['costume_name']) ?></td><td><?= $d['n'] ?></td><td><?= baht($d['rev']) ?></td></tr><?php endforeach; ?></table>
<?php else: ?>
<table><tr><th>รหัสสมาชิก</th><th>ชื่อ-นามสกุล</th><th>จำนวนครั้งที่เช่า</th><th>ยอดใช้จ่ายรวม (บาท)</th></tr>
<?php foreach ($data as $d): ?><tr><td><?= e($d['member_id']) ?></td><td><?= e($d['mname']) ?></td><td><?= $d['n'] ?></td><td><?= baht($d['spend']) ?></td></tr><?php endforeach; ?></table>
<?php endif; if (!$data) echo '<p>ไม่มีข้อมูลในช่วงที่เลือก</p>'; ?>
<p><button class="noprint" onclick="print()">พิมพ์ / ส่งออกรายงาน (PDF)</button></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
