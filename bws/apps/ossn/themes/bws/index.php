<?php
/**
 * BWS OSSN Theme - Index Override
 */
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>BWS Streets</title>
  <link rel="stylesheet" href="../../web/styles.css">
  <script type="module" src="../../web/rail.js"></script>
  <script type="module" src="../../web/base-card.js"></script>
</head>
<body>
  <header class="topbar">
    <h1>Black Wall Street 2.0</h1>
    <nav class="mainnav">
      <a href="../ossn/themes/bws/profile.php">Profile</a>
      <a href="../../virtual-corp/index.html">Virtual Corp</a>
      <a href="../../web/legal.html">Legal</a>
    </nav>
  </header>
  <main style="padding:1rem">
    <div data-psi="rail" id="ossn-rail">
      <!-- OSSN PHP will loop businesses and render as PSI Cards -->
      <?php
        // Example mock data; replace with DB query results
        $businesses = [
          [ 'name'=>'Onyx Coffee', 'logo'=>'../../web/assets/sample1.png', 'summary'=>'Cafe & Roastery', 'links'=>[['url'=>'#','label'=>'Visit']] ],
          [ 'name'=>'Kemet Books', 'logo'=>'../../web/assets/sample2.png', 'summary'=>'Bookstore', 'links'=>[['url'=>'#','label'=>'Shop']] ],
        ];
        foreach ($businesses as $b) {
          echo "<psi-card data-name='{$b['name']}' data-logo='{$b['logo']}' data-summary='{$b['summary']}' data-links='".json_encode($b['links'])."'></psi-card>";
        }
      ?>
    </div>
  </main>
</body>
</html>