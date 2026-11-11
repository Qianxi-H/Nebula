<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="content">
    <section class="not-found reveal">
        <p class="code">404</p>
        <p><?php _e('页面走丢了，也许它正漂在星海里 ✦'); ?></p>
        <div class="nf-actions">
            <a class="read-more" href="<?php $this->options->siteUrl(); ?>"><?php _e('返回首页'); ?> →</a>
            <button class="read-more" type="button" data-open-search><?php _e('搜索文章'); ?></button>
        </div>
    </section>
</div><!-- end .content -->

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
