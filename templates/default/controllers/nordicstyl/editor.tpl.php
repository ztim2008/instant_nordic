<?php
/** @var cmsTemplate $this */

$builder_state = $builder_state ?? [];
?>

<link rel="stylesheet" href="/templates/default/controllers/nordicstyl/fonts/fonts.css">
<div id="nordic-editor-shell-root"></div>

<script>
window.NORDIC_EDITOR_STATE = <?php echo json_encode($builder_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>