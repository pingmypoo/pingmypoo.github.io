<?php
require_once __DIR__ . '/../includes/config.php'; need('employee'); check_csrf();
function rc($pid) { return 'RC' . date('ymd') . substr($pid, 2); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) q('UPDATE tb_payment SET emp_id=?, receipt_no=? WHERE payment_id=? AND receipt_no IS NULL', [$_SESSION['uid'], rc($_POST['confirm']), $_POST['confirm']]);
    elseif (q('SELECT 1 FROM tb_booking WHERE booking_id=?', [$_POST['booking_id']])->fetch() && (float)$_POST['amount'] > 0) {
        $id = next_id('tb_payment', 'payment_id', 'PM', 5);
        q('INSERT INTO tb_payment(payment_id,booking_id,emp_id,payment_date,amount,payment_method,receipt_no) VALUES(?,?,?,NOW(),?,?,?)', [$id, $_POST['booking_id'], $_SESSION['uid'], (float)$_POST['amount'], $_POST['method'], rc($id)]);
    }
    header('Location: payments.php'); exit;
}
$rows = q("SELECT p.*, b.total_amount, c.deposit_price, CONCAT(m.fname,' ',m.lname) mname FROM tb_payment p JOIN tb_booking b ON b.booking_id=p.booking_id JOIN tb_member m ON m.member_id=b.member_id JOIN tb_costume c ON c.costume_id=b.costume_id ORDER BY p.payment_id DESC LIMIT 200")->fetchAll();
require __DIR__ . '/../includes/header.php'; ?>
<h2>การชำระเงิน</h2>
<div class="card"><h3>บันทึกการชำระเงิน (ที่ร้าน)</h3><form method="post" class="grid"><?= csrf() ?>
<input name="booking_id" placeholder="รหัสการจอง เช่น BK00001" required><input type="number" step="0.01" name="amount" placeholder="จำนวนเงิน" required>
<select name="method"><option>ชำระที่ร้าน</option><option>โอนผ่านธนาคาร</option><option>พร้อมเพย์/QR</option></select><button>บันทึกและออกใบเสร็จ</button></form></div>
<table><tr><th>รหัส</th><th>การจอง</th><th>ลูกค้า</th><th>วันเวลา</th><th>จำนวนเงิน</th><th>ยอดที่ต้องชำระ (เช่า+มัดจำ)</th><th>ช่องทาง</th><th>ใบเสร็จ</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['payment_id']) ?></td><td><?= e($r['booking_id']) ?></td><td><?= e($r['mname']) ?></td><td><?= e($r['payment_date']) ?></td><td><?= baht($r['amount']) ?></td>
<td><?= baht($r['total_amount'] + $r['deposit_price']) ?></td><td><?= e($r['payment_method']) ?></td>
<td><?php if ($r['receipt_no']): ?><a href="../receipt.php?id=<?= e($r['payment_id']) ?>"><?= e($r['receipt_no']) ?></a>
<?php else: ?><form method="post"><?= csrf() ?><button name="confirm" value="<?= e($r['payment_id']) ?>">ยืนยันการชำระเงิน</button></form><?php endif; ?></td></tr><?php endforeach; ?></table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
