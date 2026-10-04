<?php
require_once __DIR__ . '/../includes/config.php'; need('employee'); check_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fine_paid'])) q("UPDATE tb_fine SET paid_status='ชำระแล้ว', paid_date=CURDATE() WHERE fine_id=?", [$_POST['fine_paid']]);
    else {
        $b = q("SELECT b.*, c.rental_price FROM tb_booking b JOIN tb_costume c ON c.costume_id=b.costume_id WHERE booking_id=? AND b.status='กำลังเช่า'", [$_POST['booking_id']])->fetch();
        if ($b) {
            $rid = next_id('tb_return', 'return_id', 'RT', 5); $cond = $_POST['condition'];
            q('INSERT INTO tb_return(return_id,booking_id,emp_id,return_date,`condition`,remark) VALUES(?,?,?,?,?,?)', [$rid, $b['booking_id'], $_SESSION['uid'], $_POST['return_date'], $cond, $_POST['remark']]);
            q("UPDATE tb_booking SET status='คืนแล้ว' WHERE booking_id=?", [$b['booking_id']]);
            $late = max(0, (int)floor((strtotime($_POST['return_date']) - strtotime($b['due_return_date'])) / 86400));
            $fines = [];
            if ($late > 0) $fines[] = ['คืนล่าช้า', $late * $b['rental_price']];
            if ($cond !== 'ปกติ' && (float)$_POST['damage_fine'] > 0) $fines[] = [$cond === 'สูญหาย' ? 'สูญหาย' : 'ชุดชำรุด', (float)$_POST['damage_fine']];
            foreach ($fines as [$why, $amt]) q('INSERT INTO tb_fine(fine_id,return_id,fine_reason,fine_amount) VALUES(?,?,?,?)', [next_id('tb_fine', 'fine_id', 'FN', 5), $rid, $why, $amt]);
            refresh_costume($b['costume_id']);
            if ($cond !== 'ปกติ') q("UPDATE tb_costume SET status='ซ่อมแซม' WHERE costume_id=?", [$b['costume_id']]);
        }
    }
    header('Location: returns.php'); exit;
}
$kw = '%' . ($_GET['q'] ?? '') . '%';
$rows = q("SELECT b.*, c.costume_name, c.rental_price, CONCAT(m.fname,' ',m.lname) mname FROM tb_booking b JOIN tb_costume c ON c.costume_id=b.costume_id JOIN tb_member m ON m.member_id=b.member_id
 WHERE b.status='กำลังเช่า' AND (b.booking_id LIKE ? OR m.fname LIKE ? OR m.lname LIKE ?) ORDER BY b.due_return_date", [$kw, $kw, $kw])->fetchAll();
$fines = q("SELECT f.*, r.booking_id FROM tb_fine f JOIN tb_return r ON r.return_id=f.return_id ORDER BY f.fine_id DESC LIMIT 100")->fetchAll();
require __DIR__ . '/../includes/header.php'; ?>
<h2>จัดการคืนชุดและค่าปรับ</h2>
<form><input name="q" placeholder="ค้นหารายการเช่า (รหัสการจอง/ชื่อลูกค้า)" value="<?= e($_GET['q'] ?? '') ?>" style="width:320px"> <button>ค้นหา</button></form><br>
<?php foreach ($rows as $r): $late = max(0, (int)floor((time() - strtotime($r['due_return_date'])) / 86400)); ?>
<div class="card"><b><?= e($r['booking_id']) ?></b> ลูกค้า: <?= e($r['mname']) ?> | ชุด: <?= e($r['costume_name']) ?> | กำหนดคืน: <?= e($r['due_return_date']) ?>
<span class="<?= $late ? 'warn' : '' ?>"> เกินกำหนด <?= $late ?> วัน (ค่าปรับล่าช้า <?= baht($late * $r['rental_price']) ?> บาท)</span>
<form method="post" class="grid"><?= csrf() ?><input type="hidden" name="booking_id" value="<?= e($r['booking_id']) ?>">
<label>วันที่คืนจริง<input type="date" name="return_date" value="<?= date('Y-m-d') ?>" required></label>
<label>สภาพชุด<select name="condition"><option>ปกติ</option><option>ชำรุด</option><option>สูญหาย</option></select></label>
<label>ค่าปรับชำรุด/สูญหาย (บาท)<input type="number" step="0.01" min="0" name="damage_fine" value="0"></label>
<label>หมายเหตุ<input name="remark" maxlength="255"></label><button>บันทึกการคืนชุด / คำนวณค่าปรับ</button></form></div>
<?php endforeach; if (!$rows) echo '<p>ไม่มีรายการที่กำลังเช่า</p>'; ?>
<h3>รายการค่าปรับ</h3><table><tr><th>รหัส</th><th>การจอง</th><th>สาเหตุ</th><th>จำนวนเงิน</th><th>สถานะ</th><th></th></tr>
<?php foreach ($fines as $f): ?><tr><td><?= e($f['fine_id']) ?></td><td><?= e($f['booking_id']) ?></td><td><?= e($f['fine_reason']) ?></td><td><?= baht($f['fine_amount']) ?></td>
<td><?= e($f['paid_status']) ?> <?= e($f['paid_date']) ?></td><td><?php if ($f['paid_status'] !== 'ชำระแล้ว'): ?><form method="post"><?= csrf() ?><button name="fine_paid" value="<?= e($f['fine_id']) ?>">บันทึกชำระค่าปรับ</button></form><?php endif; ?></td></tr><?php endforeach; ?></table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
