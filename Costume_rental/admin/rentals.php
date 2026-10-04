<?php require '../includes/header.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        if (isset($_POST['return_id'])) {                       // รับคืนชุด
            $r = $pdo->prepare("SELECT * FROM rentals WHERE id=? AND status='renting' FOR UPDATE");
            $r->execute([$_POST['return_id']]); $rent = $r->fetch();
            if ($rent) {
                $items = $pdo->prepare('SELECT * FROM rental_items WHERE rental_id=?'); $items->execute([$rent['id']]);
                $daily = 0;
                foreach ($items->fetchAll() as $it) {
                    $daily += $it['price_per_day'] * $it['qty'];
                    $pdo->prepare('UPDATE costumes SET stock=stock+? WHERE id=?')->execute([$it['qty'], $it['costume_id']]);
                }
                $lateDays = max(0, (int)floor((strtotime(date('Y-m-d')) - strtotime($rent['due_date'])) / 86400));
                $pdo->prepare("UPDATE rentals SET status='returned', return_date=CURDATE(), late_fee=? WHERE id=?")->execute([$daily * $lateDays, $rent['id']]);
            }
        } else {                                                // เปิดรายการเช่าใหม่
            $days = max(1, (int)((strtotime($_POST['due_date']) - strtotime($_POST['rent_date'])) / 86400));
            $total = 0; $deposit = 0; $lines = [];
            foreach ($_POST['costume_id'] as $i => $cid) {
                $qty = (int)($_POST['qty'][$i] ?? 0);
                if (!$cid || $qty < 1) continue;
                $c = $pdo->prepare('SELECT * FROM costumes WHERE id=? AND active=1 FOR UPDATE'); $c->execute([$cid]); $c = $c->fetch();
                if (!$c || $c['stock'] < $qty) throw new Exception('สต็อกไม่พอ: ' . ($c['name'] ?? $cid));
                $total += $c['price_per_day'] * $qty * $days; $deposit += $c['deposit'] * $qty;
                $lines[] = [$c, $qty];
            }
            if (!$lines) throw new Exception('กรุณาเลือกชุดอย่างน้อย 1 รายการ');
            $pdo->prepare('INSERT INTO rentals(customer_id,rent_date,due_date,total,deposit) VALUES(?,?,?,?,?)')
                ->execute([$_POST['customer_id'], $_POST['rent_date'], $_POST['due_date'], $total, $deposit]);
            $rid = $pdo->lastInsertId();
            foreach ($lines as [$c, $qty]) {
                $pdo->prepare('INSERT INTO rental_items(rental_id,costume_id,qty,price_per_day) VALUES(?,?,?,?)')->execute([$rid, $c['id'], $qty, $c['price_per_day']]);
                $pdo->prepare('UPDATE costumes SET stock=stock-? WHERE id=?')->execute([$qty, $c['id']]);
            }
        }
        $pdo->commit(); header('Location: rentals.php'); exit;
    } catch (Exception $x) { $pdo->rollBack(); $msg = $x->getMessage(); }
}
$custs = $pdo->query('SELECT id,name,phone FROM customers ORDER BY name')->fetchAll();
$costs = $pdo->query('SELECT id,code,name,stock,price_per_day FROM costumes WHERE active=1 AND stock>0 ORDER BY name')->fetchAll();
$rows = $pdo->query("SELECT r.*, c.name,
  (SELECT GROUP_CONCAT(CONCAT(k.name,' x',i.qty) SEPARATOR ', ') FROM rental_items i JOIN costumes k ON k.id=i.costume_id WHERE i.rental_id=r.id) items
  FROM rentals r JOIN customers c ON c.id=r.customer_id ORDER BY r.id DESC LIMIT 100")->fetchAll();
?>
<h2>การเช่า</h2>
<?php if ($msg) echo '<p class="warn">' . e($msg) . '</p>'; ?>
<div class="card"><h3>เปิดรายการเช่าใหม่</h3><form method="post"><?= csrf() ?>
<div class="grid">
<label>ลูกค้า<select name="customer_id" required><?php foreach ($custs as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name'] . ' (' . $c['phone'] . ')') ?></option><?php endforeach; ?></select></label>
<label>วันรับชุด<input type="date" name="rent_date" value="<?= date('Y-m-d') ?>" required></label>
<label>กำหนดคืน<input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+3 day')) ?>" required></label></div>
<?php for ($i = 0; $i < 3; $i++): ?>
<div class="grid"><select name="costume_id[]"><option value="">-- เลือกชุด --</option>
<?php foreach ($costs as $c): ?><option value="<?= $c['id'] ?>"><?= e("{$c['code']} {$c['name']} (เหลือ {$c['stock']}, {$c['price_per_day']}/วัน)") ?></option><?php endforeach; ?></select>
<input type="number" name="qty[]" value="1" min="1"></div>
<?php endfor; ?>
<button>บันทึกการเช่า</button></form></div>
<table><tr><th>#</th><th>ลูกค้า</th><th>ชุด</th><th>รับ - คืน</th><th>ค่าเช่า</th><th>มัดจำ</th><th>ค่าปรับ</th><th>สถานะ</th></tr>
<?php foreach ($rows as $r): $late = $r['status'] === 'renting' && $r['due_date'] < date('Y-m-d'); ?>
<tr><td><?= $r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['items']) ?></td>
<td><?= e($r['rent_date']) ?> → <?= e($r['due_date']) ?></td>
<td><?= number_format($r['total'],2) ?></td><td><?= number_format($r['deposit'],2) ?></td><td><?= number_format($r['late_fee'],2) ?></td>
<td><?php if ($r['status'] === 'renting'): ?>
<span class="<?= $late ? 'warn' : '' ?>"><?= $late ? 'เกินกำหนด' : 'กำลังเช่า' ?></span>
<form method="post" style="display:inline" onsubmit="return confirm('ยืนยันรับคืน?')"><?= csrf() ?><button name="return_id" value="<?= $r['id'] ?>">รับคืน</button></form>
<?php else: ?><span class="ok">คืนแล้ว <?= e($r['return_date']) ?></span><?php endif; ?></td></tr>
<?php endforeach; ?></table>
<?php require '../includes/footer.php'; ?>
