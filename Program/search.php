<?php
require 'config.php';
need_login();
$cats = ['ทั้งหมด', 'ชุดไทย', 'ชุดราตรี', 'ชุดสูท', 'ชุดแฟนซี'];
$q = trim($_GET['q'] ?? ''); $sz = $_GET['sz'] ?? ''; $cat = $_GET['cat'] ?? 'ทั้งหมด';
$sql = 'SELECT * FROM dresses WHERE 1'; $args = [];
if ($cat !== 'ทั้งหมด') { $sql .= ' AND category=?'; $args[] = $cat; }
if ($sz !== '') { $sql .= ' AND size=?'; $args[] = $sz; }
if ($q !== '') { $sql .= ' AND CONCAT(name,category,size,color,code) LIKE ?'; $args[] = "%$q%"; }
$st = $db->prepare($sql . ' ORDER BY id'); $st->execute($args); $list = $st->fetchAll();
$title = 'ค้นหาชุด';
include 'header.php';
?>
<section class="page">
  <h1>ค้นหาและรายการชุด</h1>
  <form class="bar" method="get">
    <input type="hidden" name="cat" value="<?= h($cat) ?>">
    <input name="q" value="<?= h($q) ?>" placeholder="ค้นหาชุด (ชื่อ / ประเภท / ไซซ์)">
    <button class="btn main">ค้นหา</button>
    <select name="sz" onchange="this.form.submit()">
      <option value="">ตัวกรอง: ทุกไซซ์</option>
      <?php foreach (['S', 'M', 'L', 'XL'] as $s): ?><option<?= $sz === $s ? ' selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
    </select>
  </form>
  <div class="cats">
    <?php foreach ($cats as $c): ?>
      <a class="btn<?= $c === $cat ? ' on' : '' ?>" href="?<?= h(http_build_query(['cat' => $c, 'q' => $q, 'sz' => $sz])) ?>"><?= h($c) ?></a>
    <?php endforeach; ?>
  </div>
  <div class="grid">
    <?php foreach ($list as $d): ?>
    <a class="card dress" href="booking.php?id=<?= $d['id'] ?>">
      <span class="tag <?= $d['available'] ? 'ok' : 'no' ?>"><?= $d['available'] ? 'ว่าง' : 'ถูกเช่า' ?></span>
      <div class="pic" style="--c:<?= h($d['color_hex']) ?>">👗</div>
      <div class="info"><b><?= h($d['name']) ?></b> #<?= h($d['code']) ?><br>ไซซ์ <?= h($d['size']) ?> · <?= h($d['category']) ?><br>ราคาเช่า <?= number_format($d['price_per_day']) ?> บาท/วัน</div>
    </a>
    <?php endforeach; ?>
    <?php if (!$list): ?><p>ไม่พบชุดที่ค้นหา ลองเปลี่ยนคำค้นหรือตัวกรอง</p><?php endif; ?>
  </div>
</section>
</main></body></html>
