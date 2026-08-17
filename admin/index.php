<?php
require __DIR__ . '/auth.php';
admin_require_login();
$cfg = admin_config();
$page = 'stats';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estatísticas | Área interna</title>
  <link rel="icon" href="../public/favicon.png" type="image/png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="admin.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="dash-body">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="dash-main">
    <div class="dash-head">
      <div>
        <p class="dash-kicker">Estatísticas</p>
        <h1 class="dash-title">Bem-vinda de volta, <?= htmlspecialchars($cfg['name']) ?></h1>
      </div>
      <div class="period" id="period">
        <button data-p="1">Hoje</button>
        <button data-p="7">7 dias</button>
        <button data-p="30" class="on">30 dias</button>
        <button data-p="90">90 dias</button>
        <button data-p="all">Todo período</button>
      </div>
    </div>
    <p class="banner" id="banner" hidden></p>
    <section class="metrics" id="metrics"></section>
    <section class="charts-row">
      <div class="card"><h2>Horários de Pico</h2><canvas id="chartHours"></canvas></div>
      <div class="card"><h2>Atividade por Dia da Semana</h2><canvas id="chartWeek"></canvas></div>
    </section>
    <section class="card"><h2>Visitantes ao Longo do Tempo</h2><canvas id="chartTime"></canvas></section>
    <section class="grid-2">
      <div class="card" id="pagesCard"></div>
      <div class="card" id="devicesCard"></div>
    </section>
    <section class="grid-3">
      <div class="card" id="browsersCard"></div>
      <div class="card" id="osCard"></div>
      <div class="card" id="sourceCard"></div>
    </section>
    <section class="grid-2">
      <div class="card" id="landingCard"></div>
      <div class="card" id="exitsCard"></div>
    </section>
    <section class="grid-2">
      <div class="card" id="countriesCard"></div>
      <div class="card" id="citiesCard"></div>
    </section>
    <p class="note">IP individual não está disponível na API do GA4 (privacidade do Google). Cliques de WhatsApp usam o evento <code>whatsapp_click</code>. Propriedade GA4: <?= htmlspecialchars($cfg['ga_property_id']) ?>.</p>
  </main>
  <script src="dashboard.js"></script>
</body>
</html>
