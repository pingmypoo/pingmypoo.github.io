<?php require '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        try { $pdo->prepare('DELETE FROM customers WHERE id=?')->execute([$_POST['delete']]); } catch (PDOException $x) {}
    } else {
        $f = [$_POST['name'], $_POST['phone'], $_POST['line_id'], $_POST['address']];
        if (!empty($_POST['id'])) { $f[] = $_POST['id']; $pdo->prepare('UPDATE customers SET name=?,phone=?,line_id=?,address=? WHERE id=?')->execute($f); }
        else $pdo->prepare('INSERT INTO customers(name,phone,line_id,address) VALUES(?,?,?,?)')->execute($f);
    }
    header('Location: customers.php'); exit;
}
$edit = ['id'=>'','name'=>'','phone'=>'','line_id'=>'','address'=>''];
if (!empty($_GET['edit'])) { $s = $pdo->prepare('SELECT * FROM customers WHERE id=?'); $s->execute([$_GET['edit']]); $edit = $s->fetch() ?: $edit; }
$kw = '%' . ($_GET['q'] ?? '') . '%';
$s = $pdo->prepare('SELECT * FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY id DESC'); $s->execute([$kw, $kw]); $rows = $s->fetchAll();
?>
<h2>ลูกค้า</h2>
<div class="card"><form method="post"><?= csrf() ?><input type="hidden" name="id" value="<?= e($edit['id']) ?>">
<div class="grid">
<label>ชื่อ<input name="name" value="<?= e($edit['name']) ?>" required></label>
<label>เบอร์โทร<input name="phone" value="<?= e($edit['phone']) ?>" required></label>
<label>LINE ID<input name="line_id" value="<?= e($edit['line_id']) ?>"></label></div>
<label>ที่อยู่<textarea name="address"><?= e($edit['address']) ?></textarea></label>
<button>บันทึก</button></form></div>
<form><input name="q" placeholder="ค้นหาชื่อ/เบอร์" value="<?= e($_GET['q'] ?? '') ?>" style="width:300px"> <button>ค้นหา</button></form><br>
<table><tr><th>ชื่อ</th><th>โทร</th><th>LINE</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['name']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['line_id']) ?></td>
<td><a class="btn" href="?edit=<?= $r['id'] ?>">แก้ไข</a>
<form method="post" style="display:inline" onsubmit="return confirm('ลบ?')"><?= csrf() ?><button class="red" name="delete" value="<?= $r['id'] ?>">ลบ</button></form></td></tr>
<?php endforeach; ?></table>
<?php require '../includes/footer.php'; ?>
