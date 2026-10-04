<?php
require_once __DIR__ . '/includes/config.php'; need('member'); check_csrf();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = array_map('trim', $_POST);
    if (!filter_var($p['email'], FILTER_VALIDATE_EMAIL)) $msg = 'อีเมลไม่ถูกต้อง';
    elseif (q('SELECT 1 FROM tb_member WHERE email=? AND member_id<>?', [$p['email'], $_SESSION['uid']])->fetch()) $msg = 'อีเมลนี้ถูกใช้แล้ว';
    else {
        q('UPDATE tb_member SET fname=?,lname=?,address=?,phone=?,email=? WHERE member_id=?', [$p['fname'], $p['lname'], $p['address'], $p['phone'], $p['email'], $_SESSION['uid']]);
        if ($_POST['password'] !== '') q('UPDATE tb_member SET password=? WHERE member_id=?', [password_hash($_POST['password'], PASSWORD_DEFAULT), $_SESSION['uid']]);
        $_SESSION['name'] = $p['fname']; $msg = 'บันทึกแล้ว';
    }
}
$m = q('SELECT * FROM tb_member WHERE member_id=?', [$_SESSION['uid']])->fetch();
require __DIR__ . '/includes/header.php'; ?>
<h2>ข้อมูลส่วนตัว</h2><div class="card" style="max-width:480px"><?php if ($msg) echo '<p>' . e($msg) . '</p>'; ?>
รหัสสมาชิก: <?= e($m['member_id']) ?> (สมัครเมื่อ <?= e($m['register_date']) ?>)
<form method="post"><?= csrf() ?><input name="fname" value="<?= e($m['fname']) ?>" required><input name="lname" value="<?= e($m['lname']) ?>" required>
<input name="phone" value="<?= e($m['phone']) ?>" maxlength="15"><input name="address" value="<?= e($m['address']) ?>" maxlength="255">
<input type="email" name="email" value="<?= e($m['email']) ?>" required><input type="password" name="password" placeholder="รหัสผ่านใหม่ (เว้นว่างหากไม่เปลี่ยน)"><button>บันทึก</button></form></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
