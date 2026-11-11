<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $nebula_right_items = nebulaSidebarItems('right'); ?>
<?php if ($nebula_right_items): ?>
<aside class="sidebar sidebar-right" role="complementary">
    <?php foreach ($nebula_right_items as $sbKey): ?>
        <?php nebulaSidebarWidget($this, $sbKey); ?>
    <?php endforeach; ?>
</aside><!-- end .sidebar-right -->
<?php endif; ?>
