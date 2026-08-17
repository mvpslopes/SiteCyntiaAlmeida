<?php
require __DIR__ . '/auth.php';
if (admin_logged_in()) {
  header('Location: index.php');
  exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = trim($_POST['user'] ?? '');
  $pass = $_POST['pass'] ?? '';
  if (admin_attempt_login($user, $pass)) {
    header('Location: index.php');
    exit;
  }
  $error = 'Usuário ou senha inválidos.';
}
$cfg = admin_config();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Área interna | Dr.ª Cyntia Almeida</title>
  <link rel="icon" href="../public/favicon.png" type="image/png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="login-body">
  <div class="login-card">
    <img class="login-mark" src="../public/logo-icone.png" alt="CA" />
    <img class="login-brand" src="../public/logo-completa.png" alt="Cyntia Almeida" />
    <p class="login-kicker">Área interna</p>
    <h1>Estatísticas do site</h1>
    <p class="login-desc">Acompanhe visitas, origens de tráfego e cliques no WhatsApp da Dr.ª Cyntia Almeida.</p>
    <?php if ($error): ?><p class="login-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" autocomplete="off">
      <label for="user">Usuário</label>
      <input type="text" id="user" name="user" required />
      <label for="pass">Senha</label>
      <input type="password" id="pass" name="pass" required />
      <button type="submit" class="btn-enter">Entrar</button>
    </form>
  </div>
</body>
</html>
