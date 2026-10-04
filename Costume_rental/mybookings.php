<?php
require_once __DIR__ . '/includes/config.php'; need('member');
$rows = q('SELECT b.*, c.costume_name, c.deposit_price FROM tb_booking b JOIN tb_costume c ON c.costume_id=b.costume_id WHERE b.member_id=? ORDER BY b.booking_id DESC', [$_SESSION['uid']])->fetchAll();
require __DIR__ . '/includes/header.php'; ?>
<h2>การจอง/เช่าของฉัน</h2>
<?php foreach ($rows as $b): $due = $b['total_amount'] + $b['deposit_price']; $paid = paid_sum($b['booking_id']);
$pays = q('SELECT * FROM tb_payment WHERE booking_id=? ORDER BY payment_date', [$b['booking_id']])->fetchAll();
$fines = q('SELECT f.* FROM tb_fine f JOIN tb_return r ON r.return_id=f.return_id WHERE r.booking_id=?', [$b['booking_id']])->fetchAll(); ?>
<div class="card"><b><?= e($b['booking_id']) ?></b> — <?= e($b['costume_name']) ?> | สถานะ: <b><?= e($b['status']) ?></b><br>
เช่า <?= e($b['rent_date']) ?> ถึง <?= e($b['due_return_date']) ?> | ค่าเช่า <?= baht($b['total_amount']) ?> + มัดจำ <?= baht($b['deposit_price']) ?> | ชำระแล้ว <?= baht($paid) ?> / <?= baht($due) ?> บาท
<?php if ($paid < $due && $b['status'] !== 'ยกเลิก'): ?> <a class="btn" href="pay.php?id=<?= e($b['booking_id']) ?>">ชำระเงิน</a><?php endif; ?>
<?php foreach ($pays as $p): ?><br>• ชำระ <?= baht($p['amount']) ?> บาท (<?= e($p['payment_method']) ?>) <?= $p['receipt_no'] ? '<a href="receipt.php?id=' . e($p['payment_id']) . '">ใบเสร็จ ' . e($p['receipt_no']) . '</a>' : '<i>รอพนักงานยืนยัน</i>' ?><?php endforeach; ?>
<?php foreach ($fines as $f): ?><br><span class="warn">• ค่าปรับ: <?= e($f['fine_reason']) ?> <?= baht($f['fine_amount']) ?> บาท (<?= e($f['paid_status']) ?>)</span><?php endforeach; ?></div>
<?php endforeach; if (!$rows) echo '<p>ยังไม่มีรายการ</p>'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
