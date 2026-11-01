<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<aside class="sidebar sidebar-right" role="complementary">
    <?php foreach (nebulaSidebarItems('right') as $sbKey): ?>
        <?php nebulaSidebarWidget($this, $sbKey); ?>
    <?php endforeach; ?>
</aside><!-- end .sidebar-right -->
