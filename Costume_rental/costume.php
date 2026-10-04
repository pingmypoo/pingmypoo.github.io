<?php
require_once __DIR__ . '/includes/config.php'; need('member'); check_csrf();
$c = q('SELECT * FROM tb_costume WHERE costume_id=?', [$_GET['id'] ?? ''])->fetch() or die('ไม่พบชุด');
$err = '';

// ตรวจรูปแบบวันที่ให้เป็น Y-m-d จริง ๆ
$validDate = function ($d) {
    $x = DateTime::createFromFormat('Y-m-d', $d);
    return $x && $x->format('Y-m-d') === $d;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rent = $_POST['rent_date'] ?? '';
    $due  = $_POST['due_return_date'] ?? '';
    $size = trim($_POST['size'] ?? '');   // ไซซ์ที่ลูกค้าเลือก/พิมพ์เอง

    if (!$validDate($rent) || !$validDate($due) || $rent < date('Y-m-d') || $due < $rent) {
        $err = 'วันที่ไม่ถูกต้อง';
    } elseif ($size === '' || mb_strlen($size) > 30) {
        $err = 'กรุณาระบุไซซ์ (ไม่เกิน 30 ตัวอักษร)';
    } elseif (in_array($c['status'], ['ซ่อมแซม', 'ตัดจำหน่าย'], true)) {
        // ปรับรายชื่อสถานะที่เช่าไม่ได้ให้ตรงกับระบบของคุณ
        $err = 'ชุดนี้ไม่พร้อมให้เช่า (' . $c['status'] . ')';
    } elseif (q("SELECT COUNT(*) n FROM tb_booking WHERE costume_id=? AND status IN('จอง','กำลังเช่า') AND rent_date<=? AND due_return_date>=?", [$c['costume_id'], $due, $rent])->fetch()['n'] > 0) {
        $err = 'ชุดนี้ถูกจองในช่วงวันที่ที่เลือกแล้ว';
    } else {
        $id = next_id('tb_booking', 'booking_id', 'BK', 5);
        // total_amount = ค่าเช่า + มัดจำ (ให้ตรงกับยอดที่แสดงในหน้าเว็บ)
        // ถ้า pay.php คิดมัดจำแยกอยู่แล้ว ให้ลบ + $c['deposit_price'] ออก และแก้ JS แทน
        $total = $c['rental_price'] * days($rent, $due) + $c['deposit_price'];
        q("INSERT INTO tb_booking(booking_id,member_id,costume_id,size,booking_date,rent_date,due_return_date,status,total_amount) VALUES(?,?,?,?,CURDATE(),?,?,'จอง',?)",
          [$id, $_SESSION['uid'], $c['costume_id'], $size, $rent, $due, $total]);
        refresh_costume($c['costume_id']);
        header("Location: pay.php?id=$id"); exit;
    }
}
require __DIR__ . '/includes/header.php'; ?>
<h2>รายละเอียดชุด / จองและเช่าชุด</h2>
<div class="grid"><div class="card"><?php if ($c['image_path']): ?><img class="th" src="<?= e($c['image_path']) ?>" alt=""><?php endif; ?>
<b><?= e($c['costume_name']) ?></b> (<?= e($c['costume_id']) ?>)<br>ไซซ์: <?= e($c['size']) ?> สี: <?= e($c['color']) ?> ประเภท: <?= e($c['category']) ?><br>
ราคาเช่า: <?= baht($c['rental_price']) ?> บาท/วัน มัดจำ: <?= baht($c['deposit_price']) ?> บาท<br>
สถานะ: <b class="<?= $c['status'] === 'ว่าง' ? 'ok' : 'warn' ?>"><?= e($c['status']) ?></b><p><?= nl2br(e($c['description'])) ?></p></div>
<div class="card"><h3>แบบฟอร์มการจองและเช่าชุด</h3><?php if ($err) echo '<p class="warn">' . e($err) . '</p>'; ?>
<form method="post"><?= csrf() ?>
<?php $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'Free Size']; ?>
<label>ไซซ์ที่ต้องการ (เลือกหรือพิมพ์เองได้)
<input type="text" name="size" list="size_list" maxlength="30" required
       value="<?= e($_POST['size'] ?? $c['size']) ?>" placeholder=""></label>
<datalist id="size_list"><?php foreach ($sizes as $s): ?><option value="<?= e($s) ?>"><?php endforeach; ?></datalist>
<label>วันที่เริ่มเช่า<input type="date" id="d1" name="rent_date" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required></label>
<label>วันที่คืนชุด<input type="date" id="d2" name="due_return_date" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+3 day')) ?>" required></label>
<p id="sum"></p><button>ยืนยันการจอง/เช่าชุด</button></form></div></div>
<script>
const P=<?= (float)$c['rental_price'] ?>,D=<?= (float)$c['deposit_price'] ?>;
function calc(){
  const a=new Date(d1.value),b=new Date(d2.value);
  // +1 = นับรวมวันแรกและวันสุดท้าย (เช่าและคืนวันเดียวกัน = 1 วัน)
  // ต้องให้ตรงกับฟังก์ชัน days() ฝั่ง PHP
  let n=Math.round((b-a)/864e5)+1;
  if(isNaN(n)||n<1)n=1;
  sum.textContent='ค่าเช่า '+(P*n).toLocaleString()+' + มัดจำ '+D.toLocaleString()+' = '+(P*n+D).toLocaleString()+' บาท ('+n+' วัน)';
}
d1.onchange=d2.onchange=calc;
// ถ้าวันเริ่มเช่าเลยวันคืน ให้ดันวันคืนตาม
d1.addEventListener('change',()=>{d2.min=d1.value;if(d2.value<d1.value)d2.value=d1.value;calc();});
calc();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>