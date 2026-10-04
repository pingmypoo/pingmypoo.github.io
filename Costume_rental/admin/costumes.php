<?php
require_once __DIR__ . '/../includes/config.php'; need('employee'); check_csrf();
$msg = $_GET['err'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        try { q('DELETE FROM tb_costume WHERE costume_id=?', [$_POST['delete']]); }
        catch (PDOException $x) { $msg = 'ลบไม่ได้ เนื่องจากชุดนี้มีประวัติการจอง/เช่า (เปลี่ยนสถานะเป็น "ซ่อมแซม" แทนได้)'; }
    } else {
        $img = ltrim($_POST['old_image'] ?? '', './');   // เก็บ path แบบ uploads/xxx.jpg (นับจากโฟลเดอร์หลัก)
        $err = '';
        if (!empty($_FILES['image']['name'])) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $err = 'อัปโหลดรูปไม่สำเร็จ (error ' . $_FILES['image']['error'] . ': 1 = ไฟล์ใหญ่เกิน 2MB)';
            } else {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $dir = __DIR__ . '/../uploads';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $n = 'uploads/' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) $err = 'รองรับเฉพาะไฟล์ jpg, png, webp';
                elseif (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../' . $n)) $img = $n;
                else $err = 'บันทึกไฟล์ไม่ได้ ตรวจสอบว่ามีโฟลเดอร์ uploads';
            }
        }
        $f = [$_POST['costume_name'], $_POST['category'], $_POST['size'], $_POST['color'], (float)$_POST['rental_price'], (float)$_POST['deposit_price'], $_POST['status'], $img, $_POST['description']];
        if ($_POST['id'] !== '') { $f[] = $_POST['id']; q('UPDATE tb_costume SET costume_name=?,category=?,size=?,color=?,rental_price=?,deposit_price=?,status=?,image_path=?,description=? WHERE costume_id=?', $f); }
        else { array_unshift($f, next_id('tb_costume', 'costume_id', 'C', 4)); q('INSERT INTO tb_costume(costume_id,costume_name,category,size,color,rental_price,deposit_price,status,image_path,description) VALUES(?,?,?,?,?,?,?,?,?,?)', $f); }
        header('Location: costumes.php' . ($err ? '?err=' . urlencode($err) : '')); exit;
    }
}
$ed = ['costume_id'=>'','costume_name'=>'','category'=>'','size'=>'','color'=>'','rental_price'=>'','deposit_price'=>0,'status'=>'ว่าง','image_path'=>'','description'=>''];
if (!empty($_GET['edit'])) $ed = q('SELECT * FROM tb_costume WHERE costume_id=?', [$_GET['edit']])->fetch() ?: $ed;
$kw = '%' . ($_GET['q'] ?? '') . '%';
$rows = q('SELECT * FROM tb_costume WHERE costume_name LIKE ? OR costume_id LIKE ? ORDER BY costume_id DESC', [$kw, $kw])->fetchAll();
require __DIR__ . '/../includes/header.php'; ?>
<h2>ข้อมูลชุด (คลังชุด)</h2><?php if ($msg) echo '<p class="warn">' . e($msg) . '</p>'; ?>
<div class="card"><form method="post" enctype="multipart/form-data"><?= csrf() ?><input type="hidden" name="id" value="<?= e($ed['costume_id']) ?>"><input type="hidden" name="old_image" value="<?= e($ed['image_path']) ?>">
<div class="grid"><label>ชื่อชุด<input name="costume_name" maxlength="150" value="<?= e($ed['costume_name']) ?>" required></label>
<label>ประเภทชุด<select name="category"><?php foreach (['ชุดไทย','ชุดราตรี','ชุดสูท','ชุดแฟนซี'] as $c) echo '<option ' . ($ed['category'] === $c ? 'selected' : '') . '>' . $c . '</option>'; ?></select></label>
</label><label>สี<input name="color" maxlength="30" value="<?= e($ed['color']) ?>"></label>
<label>ราคาเช่าต่อวัน<input type="number" step="0.01" name="rental_price" value="<?= e($ed['rental_price']) ?>" required></label>
<label>ค่ามัดจำ<input type="number" step="0.01" name="deposit_price" value="<?= e($ed['deposit_price']) ?>" required></label>
<label>สถานะชุด<select name="status"><?php foreach (['ว่าง','ถูกจอง','ซ่อมแซม'] as $s) echo '<option ' . ($ed['status'] === $s ? 'selected' : '') . '>' . $s . '</option>'; ?></select></label>
<label>รูปภาพชุด (jpg, png, webp ไม่เกิน 2MB)<input type="file" name="image" accept="image/*"></label></div>
<?php if ($ed['image_path']): ?><p>รูปปัจจุบัน:<br><img src="../<?= e(ltrim($ed['image_path'], './')) ?>" alt="" style="height:100px"></p><?php endif; ?>
<label>รายละเอียดชุด<textarea name="description"><?= e($ed['description']) ?></textarea></label><button>บันทึก</button></form></div>
<form><input name="q" placeholder="ค้นหาชื่อ/รหัสชุด" value="<?= e($_GET['q'] ?? '') ?>" style="width:280px"> <button>ค้นหา</button></form><br>
<table><tr><th>รูป</th><th>รหัส</th><th>ชื่อ</th><th>ประเภท</th><th>ไซซ์</th><th>สี</th><th>เช่า/วัน</th><th>มัดจำ</th><th>สถานะ</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><?php if ($r['image_path']): ?><img src="../<?= e(ltrim($r['image_path'], './')) ?>" alt="" style="height:50px"><?php endif; ?></td>
<td><?= e($r['costume_id']) ?></td><td><?= e($r['costume_name']) ?></td><td><?= e($r['category']) ?></td><td><?= e($r['size']) ?></td><td><?= e($r['color']) ?></td>
<td><?= baht($r['rental_price']) ?></td><td><?= baht($r['deposit_price']) ?></td><td><?= e($r['status']) ?></td>
<td><a class="btn" href="?edit=<?= e($r['costume_id']) ?>">แก้ไข</a> <form method="post" style="display:inline" onsubmit="return confirm('ลบชุดนี้?')"><?= csrf() ?><button class="red" name="delete" value="<?= e($r['costume_id']) ?>">ลบ</button></form></td></tr><?php endforeach; ?></table>
<?php require __DIR__ . '/../includes/footer.php'; ?>