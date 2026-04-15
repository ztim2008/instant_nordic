<?php
$this->addTplCSSName("nordicblocks_tokens");
$this->addTplCSSName("nordicblocks_blocks");
?>
<?php if (!empty($html)): ?>
<style><?= $inline_css ?></style>
<div class="nb-block-widget">
    <?= $html ?>
</div>
<?php endif; ?>
