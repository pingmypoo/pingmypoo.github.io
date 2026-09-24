<?php
require 'config.php';
$err = '';
$reg = ($_POST['mode'] ?? '') === 'register';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = trim($_POST['uname'] ?? ''); $p = $_POST['pw'] ?? '';
  if ($reg) {
    $s = $db->prepare('SELECT 1 FROM users WHERE username=?'); $s->execute([$u]);
    if ($u === '' || strlen($p) < 4) $err = 'กรุณากรอกชื่อผู้ใช้ และรหัสผ่านอย่างน้อย 4 ตัวอักษร';
    elseif ($s->fetch()) $err = 'ชื่อผู้ใช้นี้ถูกใช้แล้ว';
    else {
      $db->prepare('INSERT INTO users (username,phone,password_hash) VALUES (?,?,?)')
         ->execute([$u, trim($_POST['tel'] ?? ''), password_hash($p, PASSWORD_DEFAULT)]);
      login_as($db->lastInsertId(), $u); header('Location: search.php'); exit;
    }
  } else {
    $s = $db->prepare('SELECT * FROM users WHERE username=?'); $s->execute([$u]); $r = $s->fetch();
    if ($r && password_verify($p, $r['password_hash'])) { login_as($r['id'], $r['username']); header('Location: search.php'); exit; }
    $err = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
  }
}
$title = 'เข้าสู่ระบบ';
include 'header.php';
?>
<section class="page">
  <h1 style="text-align:center">เข้าสู่ระบบ / สมัครสมาชิก</h1>
  <form class="card narrow" method="post">
    <h2>เข้าสู่ระบบสมาชิก</h2>
    <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
    <input type="hidden" name="mode" id="mode" value="login">
    <input name="uname" placeholder="ชื่อผู้ใช้ / อีเมล" value="<?= h($_POST['uname'] ?? '') ?>" required>
    <input name="tel" id="tel" type="tel" placeholder="เบอร์โทรศัพท์" hidden>
    <input name="pw" type="password" placeholder="รหัสผ่าน" required minlength="4">
    <button class="btn main" id="lbtn">เข้าสู่ระบบ</button>
    <p class="or" id="orTxt">ยังไม่มีบัญชี?</p>
    <button type="button" class="btn main alt" id="rbtn">สมัครสมาชิกใหม่</button>
  </form>
</section>
<script>
const $ = i => document.getElementById(i);
let reg = <?= $reg ? 'true' : 'false' ?>;
function setMode() {
  $('mode').value = reg ? 'register' : 'login';
  $('tel').hidden = !reg; $('tel').required = reg;
  $('lbtn').textContent = reg ? 'สมัครสมาชิก' : 'เข้าสู่ระบบ';
  $('rbtn').textContent = reg ? 'กลับไปเข้าสู่ระบบ' : 'สมัครสมาชิกใหม่';
  $('orTxt').textContent = reg ? 'มีบัญชีอยู่แล้ว?' : 'ยังไม่มีบัญชี?';
}
$('rbtn').onclick = () => { reg = !reg; setMode(); };
setMode();
</script>
</main></body></html>
