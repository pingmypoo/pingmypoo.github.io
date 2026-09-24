<?php
require 'config.php';
need_login();
$st = $db->prepare('SELECT * FROM dresses WHERE id=?'); $st->execute([(int)($_GET['id'] ?? 0)]); $d = $st->fetch();
if (!$d) { header('Location: search.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $d['available']) {
  $a = $_POST['d1'] ?? ''; $b = $_POST['d2'] ?? '';
  $ta = strtotime($a); $tb = strtotime($b);
  if (!$ta || !$tb || $tb < $ta || $ta < strtotime('today')) $err = 'กรุณาเลือกวันรับและวันคืนชุดให้ถูกต้อง';
  else {
    $days = max(1, (int)round(($tb - $ta) / 86400));
    $db->prepare('INSERT INTO bookings (user_id,dress_id,renter_name,contact,pickup_date,return_date,days,rent_total,deposit) VALUES (?,?,?,?,?,?,?,?,?)')
       ->execute([$_SESSION['uid'], $d['id'], trim($_POST['rn']), trim($_POST['ct']), $a, $b, $days, $d['price_per_day'] * $days, $d['deposit']]);
    header('Location: payment.php?id=' . $db->lastInsertId()); exit;
  }
}
$title = 'จองชุด';
include 'header.php';
?>
<section class="page">
  <h1>รายละเอียดชุด / จองและเช่าชุด</h1>
  <div class="two">
    <div class="card">
      <div class="pic big" style="--c:<?= h($d['color_hex']) ?>">👗</div>
      <h2><?= h($d['name']) ?> #<?= h($d['code']) ?></h2>
      <p>ไซซ์: <?= h($d['size']) ?> · สี: <?= h($d['color']) ?> · ประเภท: <?= h($d['category']) ?></p>
      <p>ราคาเช่า: <?= number_format($d['price_per_day']) ?> บาท/วัน · มัดจำ: <?= number_format($d['deposit']) ?> บาท</p>
      <p>สถานะ: <b class="<?= $d['available'] ? 'g' : 'r' ?>"><?= $d['available'] ? 'ว่าง' : 'ถูกเช่าแล้ว' ?></b></p>
    </div>
    <form class="card" method="post">
      <h2>แบบฟอร์มการจองและเช่าชุด</h2>
      <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
      <div class="dates">
        <label>วันที่รับชุด<input type="date" name="d1" id="d1" required></label>
        <label>วันที่คืนชุด<input type="date" name="d2" id="d2" required></label>
      </div>
      <input name="rn" placeholder="ชื่อ-นามสกุลผู้เช่า" required>
      <input name="ct" placeholder="ที่อยู่ / ช่องทางติดต่อ / รับที่ร้าน" required>
      <div class="sum" id="sum"></div>
      <button class="btn main"<?= $d['available'] ? '' : ' disabled' ?>>ยืนยันการจอง/เช่าชุด</button>
    </form>
  </div>
</section>
<script>
const $ = i => document.getElementById(i), f = n => n.toLocaleString('th-TH');
const price = <?= (int)$d['price_per_day'] ?>, dep = <?= (int)$d['deposit'] ?>;
function calc() {
  const a = $('d1').value, b = $('d2').value;
  const days = Math.max(1, Math.round((new Date(b) - new Date(a)) / 864e5));
  $('sum').innerHTML = a && b
    ? `สรุปค่าใช้จ่าย: ค่าเช่า ${f(price)} × ${days} วัน + มัดจำ ${f(dep)} = <b>${f(price * days + dep)} บาท</b>`
    : 'เลือกวันรับและวันคืนชุด เพื่อดูสรุปค่าใช้จ่าย';
}
$('d1').min = new Date().toISOString().slice(0, 10);
['d1', 'd2'].forEach(i => $(i).oninput = () => { $('d2').min = $('d1').value; calc(); });
calc();
</script>
</main></body></html>
