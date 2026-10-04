<?php
require_once __DIR__ . '/includes/config.php'; need('member'); check_csrf();
$b = q('SELECT b.*, c.costume_name, c.deposit_price FROM tb_booking b JOIN tb_costume c ON c.costume_id=b.costume_id WHERE b.booking_id=? AND b.member_id=?', [$_GET['id'] ?? '', $_SESSION['uid']])->fetch() or die('ไม่พบรายการ');
$due = $b['total_amount'] + $b['deposit_price']; $paid = paid_sum($b['booking_id']); $left = max(0, $due - $paid);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $left > 0 && $b['status'] !== 'ยกเลิก') {
    $amt = min((float)$_POST['amount'], $left);
    if ($amt > 0 && in_array($_POST['method'], ['โอนผ่านธนาคาร', 'พร้อมเพย์/QR', 'ชำระที่ร้าน'])) {
        q('INSERT INTO tb_payment(payment_id,booking_id,payment_date,amount,payment_method) VALUES(?,?,NOW(),?,?)',
          [next_id('tb_payment', 'payment_id', 'PM', 5), $b['booking_id'], $amt, $_POST['method']]);
        header('Location: mybookings.php'); exit;
    }
}
require __DIR__ . '/includes/header.php'; ?>
<h2>ชำระเงิน</h2><div class="card">
รหัสการจอง: <?= e($b['booking_id']) ?><br>ชุด: <?= e($b['costume_name']) ?> (<?= e($b['rent_date']) ?> ถึง <?= e($b['due_return_date']) ?>)<br>
ค่าเช่า: <?= baht($b['total_amount']) ?> บาท ค่ามัดจำ: <?= baht($b['deposit_price']) ?> บาท<br><b>รวมทั้งสิ้น: <?= baht($due) ?> บาท</b><br>
ชำระแล้ว: <?= baht($paid) ?> บาท | คงเหลือ: <?= baht($left) ?> บาท</div>
<?php if ($left > 0 && $b['status'] !== 'ยกเลิก'): ?>
<div class="card"><form method="post"><?= csrf() ?><label>ช่องทางการชำระเงิน<select name="method"><option>โอนผ่านธนาคาร</option><option>พร้อมเพย์/QR</option><option>ชำระที่ร้าน</option></select></label>
<label>จำนวนเงิน (บาท)<input type="number" step="0.01" min="1" max="<?= $left ?>" name="amount" value="<?= $left ?>" required></label>
<button>ยืนยันการชำระเงิน</button> <small>ใบเสร็จรับเงินจะออกเมื่อพนักงานยืนยันการชำระเงิน</small></form></div>
<?php endif; require __DIR__ . '/includes/footer.php'; ?>
