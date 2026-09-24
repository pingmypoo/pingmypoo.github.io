<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title ?? 'Elegance') ?> | ร้านเช่าชุดและเครื่องแต่งกาย</title>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="top">
  <a class="logo" href="search.php">👗 <b>Elegance</b> ร้านเช่าชุด</a>
  <?php if (!empty($_SESSION['uid'])): ?>
  <nav>
    <span>👤 <?= h(explode('@', $_SESSION['uname'])[0]) ?></span>
    <a href="payment.php">ตะกร้า</a>
    <a href="logout.php">ออกจากระบบ</a>
  </nav>
  <?php endif; ?>
</header>
<main>
