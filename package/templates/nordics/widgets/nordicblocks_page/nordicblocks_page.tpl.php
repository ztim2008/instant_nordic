<?php
$this->addTplCSSName("nordicblocks_tokens");
$this->addTplCSSName("nordicblocks_blocks");
?>
<?php if (!empty($html)): ?>
<style><?= $inline_css ?></style>
<?php if (!empty($blocks_css)): ?><style><?= $blocks_css ?></style><?php endif; ?>
<div class="nb-page-widget">
    <?= $html ?>
</div>
<?php endif; ?>
