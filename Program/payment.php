<?php
require 'config.php';
need_login();
$uid = $_SESSION['uid'];
$pays = ['bank_transfer' => '🏦 โอนผ่านธนาคาร', 'promptpay' => '📱 พร้อมเพย์ / QR', 'pay_at_shop' => '🏪 ชำระที่ร้าน'];
$id = (int)($_GET['id'] ?? 0);
if (!$id) { // "ตะกร้า" link: latest unpaid booking
  $s = $db->prepare("SELECT MAX(id) FROM bookings WHERE user_id=? AND status='pending'"); $s->execute([$uid]); $id = (int)$s->fetchColumn();
}
$s = $db->prepare('SELECT b.*, d.name, d.code FROM bookings b JOIN dresses d ON d.id=b.dress_id WHERE b.id=? AND b.user_id=?');
$s->execute([$id, $uid]); $b = $s->fetch();
$err = '';
if ($b && $b['status'] === 'pending' && isset($pays[$_POST['method'] ?? ''])) {
  $db->beginTransaction();
  $u = $db->prepare('UPDATE dresses SET available=0 WHERE id=? AND available=1'); $u->execute([$b['dress_id']]);
  if ($u->rowCount()) {
    $db->prepare('INSERT INTO payments (booking_id,method,amount) VALUES (?,?,?)')->execute([$id, $_POST['method'], $b['rent_total'] + $b['deposit']]);
    $db->prepare("UPDATE bookings SET status='paid' WHERE id=?")->execute([$id]);
    $db->commit(); header("Location: payment.php?id=$id"); exit;
  }
  $db->rollBack(); $err = 'ขออภัย ชุดนี้ถูกเช่าไปแล้ว';
}
$method = null;
if ($b && $b['status'] === 'paid') { $s = $db->prepare('SELECT method FROM payments WHERE booking_id=?'); $s->execute([$id]); $method = $s->fetchColumn(); }
$title = 'ชำระเงิน';
include 'header.php';
?>
<section class="page">
  <h1>ชำระเงินและใบเสร็จ</h1>
  <?php if (!$b): ?>
  <div class="card box">ยังไม่มีรายการจอง <a href="search.php">เลือกชุดที่ต้องการ</a></div>
  <?php else: $total = $b['rent_total'] + $b['deposit']; ?>
  <div class="card box">
    <h2>รายละเอียดค่าเช่า</h2>
    <div class="row"><span>รหัสการจอง</span><b><?= sprintf('BK%05d', $id) ?></b></div>
    <div class="row"><span>ชุด</span><span><?= h($b['name']) ?> #<?= h($b['code']) ?> × <?= $b['days'] ?> วัน</span></div>
    <div class="row"><span>ค่าเช่า</span><span><?= number_format($b['rent_total']) ?> บาท</span></div>
    <div class="row"><span>ค่ามัดจำ</span><span><?= number_format($b['deposit']) ?> บาท</span></div>
    <div class="row"><b>รวมทั้งสิ้น</b><b><?= number_format($total) ?> บาท</b></div>
  </div>
  <?php if ($method): ?>
  <div class="card box receipt">
    <h2>✅ ชำระเงินสำเร็จ</h2>
    <div class="row"><span>เลขที่ใบเสร็จ</span><b><?= sprintf('RC%05d', $id) ?></b></div>
    <div class="row"><span>รหัสการจอง</span><span><?= sprintf('BK%05d', $id) ?></span></div>
    <div class="row"><span>ช่องทางชำระเงิน</span><span><?= h($pays[$method]) ?></span></div>
    <div class="row"><b>ยอดชำระ</b><b><?= number_format($total) ?> บาท</b></div>
    <a class="btn main" style="margin-top:16px" href="search.php">กลับไปหน้าค้นหาชุด</a>
  </div>
  <?php else: ?>
  <form class="card box" method="post">
    <h2>ช่องทางการชำระเงิน</h2>
    <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
    <div class="pay">
      <?php foreach ($pays as $k => $l): ?><label class="btn"><input type="radio" name="method" value="<?= $k ?>" required><?= $l ?></label><?php endforeach; ?>
    </div>
    <button class="btn main">ยืนยันการชำระเงิน</button>
  </form>
  <?php endif; endif; ?>
</section>
</main></body></html>
