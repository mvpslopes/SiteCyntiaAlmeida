<?php
function ga_access_token($credentials) {
  $now = time();
  $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
  $claim = rtrim(strtr(base64_encode(json_encode([
    'iss' => $credentials['client_email'],
    'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
    'aud' => 'https://oauth2.googleapis.com/token',
    'iat' => $now,
    'exp' => $now + 3600,
  ])), '+/', '-_'), '=');
  $unsigned = $header . '.' . $claim;
  $key = openssl_pkey_get_private($credentials['private_key']);
  if (!$key) return null;
  openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256);
  $jwt = $unsigned . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

  $ch = curl_init('https://oauth2.googleapis.com/token');
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => http_build_query([
      'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
      'assertion' => $jwt,
    ]),
  ]);
  $res = json_decode(curl_exec($ch), true);
  curl_close($ch);
  return $res['access_token'] ?? null;
}

function ga_run_report($token, $propertyId, $body) {
  $ch = curl_init('https://analyticsdata.googleapis.com/v1beta/properties/' . $propertyId . ':runReport');
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . $token,
      'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($body),
  ]);
  $res = json_decode(curl_exec($ch), true);
  curl_close($ch);
  return $res;
}

function ga_rows($report, $metricCount = 1) {
  $out = [];
  foreach (($report['rows'] ?? []) as $row) {
    $dims = array_map(fn($d) => $d['value'] ?? '', $row['dimensionValues'] ?? []);
    $mets = array_map(fn($m) => $m['value'] ?? '0', $row['metricValues'] ?? []);
    $out[] = array_merge($dims, $mets);
  }
  return $out;
}

function ga_metric($report, $index = 0) {
  return (float) ($report['rows'][0]['metricValues'][$index]['value'] ?? 0);
}

function ga_fetch($periodDays) {
  $cfg = admin_config();
  $file = $cfg['credentials_file'];
  if (!is_file($file)) {
    return ['ok' => false, 'error' => 'credentials', 'data' => ga_empty()];
  }
  $credentials = json_decode(file_get_contents($file), true);
  $token = ga_access_token($credentials);
  if (!$token) {
    return ['ok' => false, 'error' => 'token', 'data' => ga_empty()];
  }

  $propertyId = $cfg['ga_property_id'];
  $start = $periodDays === 'all' ? '2020-01-01' : date('Y-m-d', strtotime('-' . ((int)$periodDays - 1) . ' days'));
  $end = 'today';
  $range = [['startDate' => $start, 'endDate' => $end]];

  $overview = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'metrics' => [
      ['name' => 'activeUsers'],
      ['name' => 'sessions'],
      ['name' => 'screenPageViews'],
      ['name' => 'averageSessionDuration'],
      ['name' => 'bounceRate'],
      ['name' => 'screenPageViewsPerSession'],
      ['name' => 'eventCount'],
    ],
  ]);

  $wa = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'eventName']],
    'metrics' => [['name' => 'eventCount']],
    'dimensionFilter' => [
      'filter' => [
        'fieldName' => 'eventName',
        'stringFilter' => ['value' => 'whatsapp_click'],
      ],
    ],
  ]);

  $hours = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'hour']],
    'metrics' => [['name' => 'sessions']],
    'orderBys' => [['dimension' => ['dimensionName' => 'hour']]],
    'limit' => 24,
  ]);

  $week = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'dayOfWeek']],
    'metrics' => [['name' => 'sessions']],
    'limit' => 7,
  ]);

  $timeline = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'date']],
    'metrics' => [['name' => 'activeUsers'], ['name' => 'sessions']],
    'orderBys' => [['dimension' => ['dimensionName' => 'date']]],
    'limit' => 90,
  ]);

  $pages = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'pagePath']],
    'metrics' => [['name' => 'screenPageViews'], ['name' => 'averageSessionDuration']],
    'orderBys' => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]],
    'limit' => 10,
  ]);

  $devices = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'deviceCategory']],
    'metrics' => [['name' => 'sessions']],
    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
  ]);

  $browsers = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'browser']],
    'metrics' => [['name' => 'sessions']],
    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
    'limit' => 8,
  ]);

  $os = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'operatingSystem']],
    'metrics' => [['name' => 'sessions']],
    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
    'limit' => 8,
  ]);

  $source = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'sessionDefaultChannelGroup']],
    'metrics' => [['name' => 'sessions']],
    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
    'limit' => 8,
  ]);

  $landing = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'landingPage']],
    'metrics' => [['name' => 'sessions']],
    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
    'limit' => 8,
  ]);

  $exits = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'pagePath']],
    'metrics' => [['name' => 'sessions'], ['name' => 'bounceRate']],
    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
    'limit' => 8,
  ]);

  $countries = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'country']],
    'metrics' => [['name' => 'sessions'], ['name' => 'screenPageViews']],
    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
    'limit' => 10,
  ]);

  $cities = ga_run_report($token, $propertyId, [
    'dateRanges' => $range,
    'dimensions' => [['name' => 'city'], ['name' => 'country']],
    'metrics' => [['name' => 'sessions'], ['name' => 'screenPageViews']],
    'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
    'limit' => 20,
  ]);

  $users = ga_metric($overview, 0);
  $sessions = ga_metric($overview, 1);
  $views = ga_metric($overview, 2);
  $avgDuration = ga_metric($overview, 3);
  $bounce = ga_metric($overview, 4);
  $pagesPer = ga_metric($overview, 5);
  $events = ga_metric($overview, 6);
  $waClicks = ga_metric($wa, 0);
  $conversion = $sessions > 0 ? ($waClicks / $sessions) * 100 : 0;

  $weekMap = ['0' => 'Dom', '1' => 'Seg', '2' => 'Ter', '3' => 'Qua', '4' => 'Qui', '5' => 'Sex', '6' => 'Sáb'];
  $weekData = array_fill_keys(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'], 0);
  foreach (ga_rows($week) as $row) {
    $label = $weekMap[$row[0]] ?? $row[0];
    $weekData[$label] = (int) $row[1];
  }

  $hourData = array_fill(0, 24, 0);
  foreach (ga_rows($hours) as $row) {
    $hourData[(int)$row[0]] = (int)$row[1];
  }

  $deviceTotal = array_sum(array_map(fn($r) => (int)$r[1], ga_rows($devices))) ?: 1;

  return [
    'ok' => true,
    'error' => null,
    'data' => [
      'users' => round($users),
      'sessions' => round($sessions),
      'views' => round($views),
      'pagesPerVisit' => round($pagesPer, 1),
      'whatsapp' => round($waClicks),
      'avgSession' => $avgDuration,
      'bounce' => $bounce * ($bounce <= 1 ? 100 : 1),
      'conversion' => $conversion,
      'pagesPerSession' => round($pagesPer, 1),
      'events' => round($events),
      'hours' => $hourData,
      'week' => array_values($weekData),
      'weekLabels' => array_keys($weekData),
      'timeline' => array_map(function ($r) {
        $d = $r[0];
        return [
          'date' => substr($d, 6, 2) . '/' . substr($d, 4, 2),
          'users' => (int)$r[1],
          'sessions' => (int)$r[2],
        ];
      }, ga_rows($timeline)),
      'pages' => array_map(fn($r) => ['path' => $r[0], 'views' => (int)$r[1], 'avg' => (float)$r[2]], ga_rows($pages)),
      'devices' => array_map(fn($r) => ['name' => $r[0], 'value' => (int)$r[1], 'pct' => round(((int)$r[1] / $deviceTotal) * 100)], ga_rows($devices)),
      'browsers' => array_map(fn($r) => ['name' => $r[0], 'value' => (int)$r[1]], ga_rows($browsers)),
      'os' => array_map(fn($r) => ['name' => $r[0], 'value' => (int)$r[1]], ga_rows($os)),
      'source' => array_map(fn($r) => ['name' => $r[0], 'value' => (int)$r[1]], ga_rows($source)),
      'landing' => array_map(fn($r) => ['path' => $r[0], 'value' => (int)$r[1]], ga_rows($landing)),
      'exits' => array_map(fn($r) => ['path' => $r[0], 'value' => (int)$r[1], 'bounce' => ((float)$r[2]) * (((float)$r[2]) <= 1 ? 100 : 1)], ga_rows($exits)),
      'countries' => array_map(fn($r) => ['name' => $r[0], 'sessions' => (int)$r[1], 'views' => (int)$r[2]], ga_rows($countries)),
      'cities' => array_map(fn($r) => ['name' => $r[0], 'country' => $r[1], 'sessions' => (int)$r[2], 'views' => (int)$r[3]], ga_rows($cities)),
    ],
  ];
}

function ga_empty() {
  return [
    'users' => 0, 'sessions' => 0, 'views' => 0, 'pagesPerVisit' => 0,
    'whatsapp' => 0, 'avgSession' => 0, 'bounce' => 0, 'conversion' => 0,
    'pagesPerSession' => 0, 'events' => 0,
    'hours' => array_fill(0, 24, 0),
    'week' => [0,0,0,0,0,0,0],
    'weekLabels' => ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'],
    'timeline' => [], 'pages' => [], 'devices' => [], 'browsers' => [],
    'os' => [], 'source' => [], 'landing' => [], 'exits' => [],
    'countries' => [], 'cities' => [],
  ];
}
