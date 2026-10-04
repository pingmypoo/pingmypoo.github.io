<?php
require_once __DIR__ . '/includes/config.php';   // หน้าหลัก: ดูชุดได้โดยไม่ต้องเข้าสู่ระบบ (การจองต้องเข้าสู่ระบบ)

$cats = ['ชุดไทย', 'ชุดราตรี', 'ชุดสูท', 'ชุดแฟนซี'];
$selCat = $_GET['category'] ?? '';
$w = ['1=1']; $a = [];
if (($_GET['q'] ?? '') !== '')      { $w[] = 'costume_name LIKE ?'; $a[] = '%' . $_GET['q'] . '%'; }
if ($selCat !== '')                 { $w[] = 'category=?';          $a[] = $selCat; }
if (($_GET['size'] ?? '') !== '')   { $w[] = 'size=?';              $a[] = $_GET['size']; }
if (($_GET['avail'] ?? '') === '1') { $w[] = 'status=?';            $a[] = 'ว่าง'; }
$rows = q('SELECT * FROM tb_costume WHERE ' . implode(' AND ', $w) . ' ORDER BY costume_id DESC', $a)->fetchAll();

$groups = array_fill_keys($cats, []);                       // แบ่งกลุ่มตามประเภทชุด
foreach ($rows as $r) { $k = in_array($r['category'], $cats) ? $r['category'] : 'อื่น ๆ'; $groups[$k][] = $r; }
$sizes = q('SELECT DISTINCT size FROM tb_costume WHERE size<>"" ORDER BY size')->fetchAll(PDO::FETCH_COLUMN);
$isFiltered = $selCat !== '' || ($_GET['q'] ?? '') !== '' || ($_GET['size'] ?? '') !== '' || ($_GET['avail'] ?? '') === '1';
$openFinder = $isFiltered || ($_GET['find'] ?? '') === '1';   // กดไอคอนค้นหาจากหน้าอื่นแล้วเปิดแผงค้นหาทันที

require __DIR__ . '/includes/header.php';   // header.php จะเรียกแถบบน (includes/topbar.php) ให้เอง
?>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Thai:wght@400;600;700&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--ink:#24101f;--plum:#6b2d5c;--plum-d:#3b1a33;--plum2:#5a2a4f;--gold:#c5a15a;--gold2:#e6cf9f;--ivory:#faf6ef;--sand:#f0e8da;--mute:#7d6b76}
body{background:var(--ivory)}
.lux{font-family:'Sarabun',sans-serif;color:var(--ink);width:100vw;position:relative;left:50%;margin-left:-50vw;max-width:none}
html,body{overflow-x:hidden}
.lux h1,.lux h2{font-family:'Noto Serif Thai',serif}
.lux a{color:inherit;text-decoration:none}

/* ===== แผงค้นหา (เปิดจากไอคอนในแถบบน) ===== */
.finder-wrap{background:#fff;border-bottom:1px solid var(--gold2)}
.finder-wrap[hidden]{display:none}
.finder{max-width:none;margin:0;padding:12px 40px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.finder input,.finder select{font:inherit;border:1px solid #e3d9c8;border-radius:10px;padding:10px 14px;background:var(--ivory);min-width:0}
.finder input[name=q]{flex:1 1 240px}
.finder select{flex:0 1 130px}
.finder label{display:flex;align-items:center;gap:6px;font-size:14px;color:var(--mute)}
.finder button{font:600 15px 'Sarabun',sans-serif;border:0;border-radius:10px;padding:10px 26px;color:var(--plum-d);cursor:pointer;background:linear-gradient(135deg,var(--gold2),var(--gold))}

/* ===== ส่วนหัวหน้า ===== */
.wrap{max-width:none;margin:0;padding:0 40px 40px}
.hero{text-align:center;padding:38px 0 6px}
.hero h1{margin:0 0 8px;font-size:clamp(24px,4vw,34px);font-weight:600;color:var(--plum-d)}
.hero p{margin:0 auto;max-width:520px;color:var(--mute);line-height:1.7}

/* ===== รายการชุด ===== */
.cat{display:flex;align-items:baseline;gap:12px;margin:38px 0 16px;padding-bottom:10px;border-bottom:1px solid var(--gold2);font-size:24px;font-weight:600}
.cat small{font:400 14px 'Sarabun',sans-serif;color:var(--mute)}
.pgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,220px));gap:22px;justify-content:start}
.pc{background:#fff;border:1px solid #eadfca;border-radius:14px;overflow:hidden;display:flex;flex-direction:column;transition:box-shadow .2s,border-color .2s}
.pc:hover{border-color:var(--gold);box-shadow:0 10px 26px rgba(90,42,79,.16)}
.pimg{position:relative;display:block;aspect-ratio:3/4;background:var(--sand)}
.pimg img{width:100%;height:100%;object-fit:contain;display:block}   /* อยากให้รูปเต็มกรอบ เปลี่ยนเป็น cover */
.pimg .none{height:100%;display:flex;align-items:center;justify-content:center;color:#b3a58f}
.badge{position:absolute;top:10px;left:10px;padding:3px 12px;border-radius:12px;font-size:12px;font-weight:500;color:#fff}
.b-ok{background:rgba(46,125,90,.92)}.b-no{background:rgba(160,52,52,.92)}
.pb{padding:12px 14px 14px;display:flex;flex-direction:column;gap:4px;flex:1}
.pb .name{font:600 16px/1.35 'Noto Serif Thai',serif}
.pb small{color:var(--mute)}
.price{color:#8f6a22;font-weight:600;font-size:18px;margin-top:6px}
.price small{font-weight:400}
.pb .btn{margin-top:auto;display:block;text-align:center;padding:9px;border-radius:10px;font-weight:500;color:#fff!important;background:var(--plum);transition:background .2s}
.pb .btn:hover{background:var(--plum2)}
.empty{text-align:center;padding:60px 0;color:var(--mute)}
:focus-visible{outline:2px solid var(--gold);outline-offset:2px}
@media(max-width:640px){.finder,.wrap{padding-left:16px;padding-right:16px}.pgrid{grid-template-columns:repeat(2,1fr);gap:12px}}
</style>

<div class="lux">
<div class="finder-wrap" id="finder" <?= $openFinder ? '' : 'hidden' ?>>
<form class="finder" method="get">
  <input type="hidden" name="category" value="<?= e($selCat) ?>">
  <input name="q" placeholder="ค้นหาชื่อชุด" value="<?= e($_GET['q'] ?? '') ?>" aria-label="ค้นหาชื่อชุด">
  <select name="size" aria-label="ไซซ์">
    <option value="">ทุกไซซ์</option>
    <?php foreach ($sizes as $s): ?><option value="<?= e($s) ?>" <?= ($_GET['size'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
  </select>
  <label><input type="checkbox" name="avail" value="1" <?= ($_GET['avail'] ?? '') === '1' ? 'checked' : '' ?>> เฉพาะชุดที่ว่าง</label>
  <button>ค้นหา</button>
</form>
</div>

<div class="wrap">
<?php if (!$isFiltered): ?>
<div class="hero"><h1>เช่าชุดสวย ในทุกโอกาสสำคัญ</h1><p>ชุดไทย ชุดราตรี ชุดสูท ชุดแฟนซี ตรวจสอบสถานะและจองได้ง่าย ๆ</p></div>
<?php endif; ?>

<?php foreach ($groups as $name => $items): if (!$items) continue; ?>
<h2 class="cat"><?= e($name) ?> <small><?= count($items) ?> ชุด</small></h2>
<div class="pgrid">
<?php foreach ($items as $r): $ok = $r['status'] === 'ว่าง'; ?>
<div class="pc">
  <a class="pimg" href="costume.php?id=<?= e($r['costume_id']) ?>">
    <?php if ($r['image_path']): ?><img src="<?= e(ltrim($r['image_path'], './')) ?>" alt="<?= e($r['costume_name']) ?>" loading="lazy"><?php else: ?><div class="none">ไม่มีรูป</div><?php endif; ?>
    <span class="badge <?= $ok ? 'b-ok' : 'b-no' ?>"><?= e($r['status']) ?></span>
  </a>
  <div class="pb">
    <div class="name"><?= e($r['costume_name']) ?></div>
    <small>ไซซ์ <?= e($r['size']) ?> · สี <?= e($r['color']) ?></small>
    <div class="price"><?= number_format($r['rental_price']) ?> บาท <small>/ วัน</small></div>
    <a class="btn" href="costume.php?id=<?= e($r['costume_id']) ?>">ดูรายละเอียดและจอง</a>
  </div>
</div>
<?php endforeach; ?></div>
<?php endforeach; if (!$rows) echo '<div class="empty">ไม่พบชุดที่ตรงกับการค้นหา ลองเปลี่ยนคำค้นหรือเลือกประเภทอื่น</div>'; ?>
</div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>