<?php
// PSI Rail partial with server-side pre-render of <psi-card> elements.
// Expects $businesses array defined by parent template.
if (!isset($businesses) || !is_array($businesses)) { $businesses = []; }

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<section class="psi-rail" data-psi="rail" data-category="sample">
  <h2>Featured Businesses</h2>
  <div class="rail-tiles">
    <?php foreach ($businesses as $b): 
      $linksJson = h(json_encode($b['links'] ?? []));
    ?>
      <psi-card
        data-id="<?php echo h($b['id'] ?? ''); ?>"
        data-name="<?php echo h($b['name'] ?? ''); ?>"
        data-logo="<?php echo h($b['logo'] ?? ''); ?>"
        data-desc="<?php echo h($b['desc'] ?? ''); ?>"
        data-header="<?php echo h($b['header'] ?? ''); ?>"
        data-links="<?php echo $linksJson; ?>"
      ></psi-card>
    <?php endforeach; ?>
  </div>
</section>
