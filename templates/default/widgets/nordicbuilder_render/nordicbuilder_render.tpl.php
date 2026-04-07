<?php if (!empty($html)) { ?>
<div class="nb-runtime"<?php if (!empty($page_key)) { ?> data-nb-page-key="<?php html($page_key); ?>"<?php } ?><?php if (!empty($hash)) { ?> data-nb-render-hash="<?php html($hash); ?>"<?php } ?><?php if (!empty($style_vars)) { ?> style="<?php html($style_vars); ?>"<?php } ?>>
    <?php echo $html; ?>
</div>
<?php } ?>
