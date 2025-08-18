<?php
// OSSN Theme Index (BWS) — PSI SSR + Hydration
$businesses = [
  [ 'id'=>1, 'name'=>'Onyx Coffee', 'logo'=>'/apps/web/assets/sample1.png', 'header'=>'/apps/web/assets/header1.jpg', 'desc'=>'Specialty coffee.', 'links'=>['https://example.com'] ],
  [ 'id'=>2, 'name'=>'Kemet Books', 'logo'=>'/apps/web/assets/sample2.png', 'header'=>'/apps/web/assets/header2.jpg', 'desc'=>'Books & culture.', 'links'=>['https://example.com/store'] ],
  [ 'id'=>3, 'name'=>'Umoja Fitness', 'logo'=>'/apps/web/assets/sample3.png', 'header'=>'/apps/web/assets/header3.jpg', 'desc'=>'Community gym.', 'links'=>['https://example.com/join'] ],
];
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Streets — BWS</title>
  <link rel="stylesheet" href="/apps/web/styles.css" />
</head>
<body>
  <header class="topbar"><h1>Streets</h1></header>
  <main style="padding:1rem">
    <?php include __DIR__ . '/views/partials/psi-rail.php'; ?>
  </main>

  <!-- Inject same data for potential client re-render/hydration -->
  <script id="psi-rail-data" type="application/json"><?php echo json_encode($businesses); ?></script>

  <!-- PSI components -->
  <script type="module" src="/packages/psi-components/base-card.js"></script>
  <script type="module">
    import { renderRail, hydrateRail } from '/apps/web/rail.js';
    const dataTag = document.getElementById('psi-rail-data');
    const businesses = JSON.parse(dataTag.textContent);
    const hasSSR = document.querySelectorAll('psi-card').length > 0;
    if (hasSSR) {
      hydrateRail('sample'); // bind behaviors to pre-rendered cards
    } else {
      renderRail('sample', businesses); // fallback to client render
    }
  </script>
</body>
</html>
