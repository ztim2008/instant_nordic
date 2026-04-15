<?php
$this->setPageTitle($page['title']);
$this->addTplCSSName('nordicblocks_tokens');
$this->addTplCSSName('nordicblocks_blocks');
?>
<style><?= $inline_css ?></style>
<?= $blocks_html ?>
