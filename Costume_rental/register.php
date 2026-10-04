<?php
require_once __DIR__ . '/includes/config.php'; check_csrf();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = array_map('trim', $_POST);
    if (!filter_var($p['email'], FILTER_VALIDATE_EMAIL) || strlen($_POST['password']) < 6 || $p['fname'] === '' || $p['lname'] === '') $err = 'กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง (รหัสผ่านอย่างน้อย 6 ตัวอักษร)';
    elseif (q('SELECT 1 FROM tb_member WHERE email=?', [$p['email']])->fetch()) $err = 'อีเมลนี้ถูกใช้แล้ว';
    else {
        q('INSERT INTO tb_member(member_id,fname,lname,address,phone,email,password,register_date,status) VALUES(?,?,?,?,?,?,?,CURDATE(),?)',
          [next_id('tb_member', 'member_id', 'M', 4), $p['fname'], $p['lname'], $p['address'], $p['phone'], $p['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), 'ใช้งาน']);
        header('Location: login.php'); exit;
    }
}
require __DIR__ . '/includes/header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,500;1,300&family=Jost:wght@300;400;500&family=Noto+Sans+Thai:wght@300;400;500&family=Noto+Serif+Thai:wght@300;500&display=swap" rel="stylesheet">

<style>
.lx{--wine:#3a0f1c;--wine-2:#5a1a2c;--gold:#c8a96a;--gold-d:#a88946;--ink:#1d1618;--mute:#8a7f7c;--paper:#fdfbf8;
  display:grid;grid-template-columns:1.05fr 1fr;max-width:920px;min-height:560px;margin:32px auto;
  background:var(--paper);box-shadow:0 30px 70px -30px rgba(58,15,28,.55);font-family:'Jost','Noto Sans Thai',sans-serif;color:var(--ink);overflow:hidden}
.lx *{box-sizing:border-box}
.lx-art{position:relative;background:
  radial-gradient(120% 80% at 0% 0%,var(--wine-2) 0%,transparent 60%),
  linear-gradient(160deg,var(--wine) 0%,#22080f 100%);
  color:#f3e9d8;padding:48px 44px;display:flex;flex-direction:column;justify-content:space-between}
.lx-art::before{content:"";position:absolute;inset:18px;border:1px solid rgba(200,169,106,.55);pointer-events:none}
.lx-art::after{content:"";position:absolute;right:-90px;bottom:-90px;width:300px;height:300px;border-radius:50%;
  border:1px solid rgba(200,169,106,.28);box-shadow:0 0 0 26px rgba(200,169,106,.06),0 0 0 27px rgba(200,169,106,.22)}
.lx-brand{font-family:'Cormorant Garamond','Noto Serif Thai',serif;font-weight:300;font-size:2.9rem;line-height:1.02;letter-spacing:.02em}
.lx-brand em{display:block;font-style:italic;color:var(--gold);font-size:1.5rem;margin-top:10px;letter-spacing:.06em}
.lx-quote{font-family:'Cormorant Garamond','Noto Serif Thai',serif;font-style:italic;font-weight:300;font-size:1.25rem;line-height:1.5;max-width:270px;color:#e6d7bd;position:relative;z-index:1}
.lx-form{padding:44px 52px;display:flex;flex-direction:column;justify-content:center}
.lx-form h3{font-family:'Cormorant Garamond','Noto Serif Thai',serif;font-weight:500;font-size:2.1rem;margin:0 0 6px;letter-spacing:.02em}
.lx-form .sub{color:var(--mute);font-weight:300;font-size:.92rem;margin:0 0 26px}
.lx-row{display:grid;grid-template-columns:1fr 1fr;gap:0 22px}
.lx-form input{width:100%;border:0;border-bottom:1px solid #d8cfc8;background:transparent;padding:14px 2px 10px;
  font:400 1rem 'Jost','Noto Sans Thai',sans-serif;color:var(--ink);outline:0;border-radius:0;margin:0 0 18px;transition:border-color .2s}
.lx-form input::placeholder{color:#b3a9a5;font-weight:300}
.lx-form input:focus{border-bottom-color:var(--gold-d);box-shadow:0 1px 0 var(--gold-d)}
.lx-form button{width:100%;margin-top:6px;padding:15px 18px;border:1px solid var(--wine);background:var(--wine);color:#f6ecd9;
  font:500 .95rem 'Jost','Noto Sans Thai',sans-serif;letter-spacing:.12em;cursor:pointer;border-radius:0;transition:background .25s,color .25s}
.lx-form button:hover{background:transparent;color:var(--wine)}
.lx-form button:focus-visible{outline:2px solid var(--gold-d);outline-offset:3px}
.lx-err{border-left:3px solid #9b2c3d;background:#f8ecee;color:#7a1f2e;padding:10px 14px;margin:0 0 20px;font-size:.9rem}
.lx-foot{margin:24px 0 0;font-size:.9rem;color:var(--mute);font-weight:300}
.lx-foot a{color:var(--wine);font-weight:500;text-decoration:none;border-bottom:1px solid var(--gold);padding-bottom:1px}
.lx-foot a:hover{color:var(--gold-d)}
@media (max-width:760px){
  .lx{grid-template-columns:1fr;margin:0 auto;min-height:0}
  .lx-art{padding:32px 28px 36px;gap:28px}.lx-brand{font-size:2.2rem}.lx-quote{font-size:1.05rem}
  .lx-form{padding:36px 28px 44px}
  .lx-row{grid-template-columns:1fr}}
</style>

<div class="lx">
  <aside class="lx-art">
    <div class="lx-brand">Maison<em>Atelier Booking</em></div>
    <p class="lx-quote">เริ่มต้นประสบการณ์การเช่าชุดที่พิเศษ ด้วยการเป็นสมาชิกของเรา</p>
  </aside>

  <section class="lx-form">
    <h3>สมัครสมาชิก</h3>
    <p class="sub">กรอกข้อมูลเพียงไม่กี่ขั้นตอน เพื่อเริ่มจองชุดที่คุณชื่นชอบ</p>
    <?php if ($err) echo '<p class="lx-err" role="alert">' . e($err) . '</p>'; ?>
    <form method="post"><?= csrf() ?>
      <div class="lx-row">
        <input name="fname" placeholder="ชื่อ" autocomplete="given-name" required value="<?= e($_POST['fname'] ?? '') ?>">
        <input name="lname" placeholder="นามสกุล" autocomplete="family-name" required value="<?= e($_POST['lname'] ?? '') ?>">
      </div>
      <input name="phone" placeholder="เบอร์โทรศัพท์" maxlength="15" autocomplete="tel" value="<?= e($_POST['phone'] ?? '') ?>">
      <input name="address" placeholder="ที่อยู่" maxlength="255" autocomplete="street-address" value="<?= e($_POST['address'] ?? '') ?>">
      <input type="email" name="email" placeholder="อีเมล (ใช้เข้าสู่ระบบ)" autocomplete="email" required value="<?= e($_POST['email'] ?? '') ?>">
      <input type="password" name="password" placeholder="รหัสผ่าน (อย่างน้อย 6 ตัวอักษร)" autocomplete="new-password" minlength="6" required>
      <button>สมัครสมาชิก</button>
    </form>
    <p class="lx-foot">มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
  </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>