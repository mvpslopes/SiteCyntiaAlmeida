<?php
$cfg = $cfg ?? admin_config();
$page = $page ?? 'stats';
?>
<aside class="sidebar">
  <a class="side-brand" href="index.php">
    <img src="../public/logo-icone.png" alt="CA" />
    <strong>Cyntia Almeida</strong>
    <span>Área interna</span>
  </a>
  <p class="side-label">Menu</p>
  <nav>
    <a class="<?= $page === 'stats' ? 'active' : '' ?>" href="index.php">Estatísticas</a>
    <a class="<?= $page === 'senha' ? 'active' : '' ?>" href="senha.php">Trocar senha</a>
    <a href="../index.html" target="_blank" rel="noopener">Ver site</a>
  </nav>
  <div class="side-user">
    <div class="avatar">C</div>
    <div>
      <strong><?= htmlspecialchars($cfg['name']) ?></strong>
      <span><?= htmlspecialchars($cfg['role']) ?></span>
    </div>
    <a class="btn-out" href="logout.php">Sair</a>
  </div>
</aside>
