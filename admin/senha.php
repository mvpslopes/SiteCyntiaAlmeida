<?php
require __DIR__ . '/auth.php';
admin_require_login();
$ok = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $atual = $_POST['atual'] ?? '';
  $nova = $_POST['nova'] ?? '';
  $conf = $_POST['conf'] ?? '';
  if (!hash_equals((string) admin_password(), $atual)) {
    $error = 'Senha atual incorreta.';
  } elseif (strlen($nova) < 6) {
    $error = 'A nova senha deve ter pelo menos 6 caracteres.';
  } elseif ($nova !== $conf) {
    $error = 'A confirmação não confere.';
  } else {
    file_put_contents(__DIR__ . '/password.php', "<?php\nreturn " . var_export($nova, true) . ";\n");
    $ok = 'Senha atualizada com sucesso.';
  }
}
$cfg = admin_config();
$page = 'senha';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trocar senha | Área interna</title>
  <link rel="icon" href="../public/favicon.png" type="image/png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="dash-body">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="dash-main">
    <p class="dash-kicker">Conta</p>
    <h1 class="dash-title">Trocar senha</h1>
    <?php if ($ok): ?><p class="flash ok"><?= htmlspecialchars($ok) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="flash err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form class="card form-card" method="post">
      <label>Senha atual</label>
      <input type="password" name="atual" required />
      <label>Nova senha</label>
      <input type="password" name="nova" required />
      <label>Confirmar nova senha</label>
      <input type="password" name="conf" required />
      <button class="btn-enter" type="submit">Salvar</button>
    </form>
  </main>
</body>
</html>
