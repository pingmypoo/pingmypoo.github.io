<?php require_once __DIR__ . '/config.php'; check_csrf(); $r = $_SESSION['role'] ?? ''; $A = $BASE === '' ? 'admin/' : ''; ?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ระบบร้านเช่าชุดและเครื่องแต่งกาย</title>
<style>
body{font-family:sans-serif;margin:0;background:#f5f5f7;color:#222}
nav{background:#6b2d5c;padding:12px 20px}nav a{color:#fff;margin-right:16px;text-decoration:none}nav b{color:#fff;margin-right:24px}
main{max-width:1000px;margin:20px auto;padding:0 16px}
table{width:100%;border-collapse:collapse;background:#fff}th,td{padding:7px;border-bottom:1px solid #ddd;text-align:left;vertical-align:top}
input,select,textarea{padding:6px;margin:3px 0;width:100%;box-sizing:border-box}
.card{background:#fff;padding:16px;border-radius:8px;margin-bottom:16px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
button,.btn{background:#6b2d5c;color:#fff;border:0;padding:7px 14px;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block}
.red{background:#c0392b}.ok{color:green}.warn{color:#c0392b}img.th{width:100%;height:160px;object-fit:cover;background:#eee}
@media print{nav,.noprint{display:none}}
</style></head><body>
<nav><b>ร้านเช่าชุดและเครื่องแต่งกาย</b>
<?php if ($r === 'member'): ?>
<a href="<?= $BASE ?>index.php">ค้นหาชุด</a><a href="<?= $BASE ?>mybookings.php">การจอง/เช่าของฉัน</a><a href="<?= $BASE ?>profile.php">ข้อมูลส่วนตัว</a>
<?php elseif ($r === 'employee'): ?>
<a href="<?= $A ?>costumes.php">ข้อมูลชุด</a><a href="<?= $A ?>bookings.php">การจอง/เช่า</a><a href="<?= $A ?>payments.php">การชำระเงิน</a><a href="<?= $A ?>returns.php">คืนชุด/ค่าปรับ</a><a href="<?= $A ?>members.php">สมาชิก</a><a href="<?= $A ?>reports.php">รายงาน</a>
<?php endif; ?>
<?php if ($r): ?><a href="<?= $BASE ?>logout.php" style="float:right">ออกจากระบบ (<?= e($_SESSION['name']) ?>)</a><?php endif; ?>
</nav><main>
