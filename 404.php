<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="content">
    <section class="not-found reveal">
        <p class="code">404</p>
        <p><?php _e('页面走丢了，也许它正漂在星海里 ✦'); ?></p>
        <a class="read-more" href="<?php $this->options->siteUrl(); ?>"><?php _e('返回首页'); ?> →</a>
    </section>
</div><!-- end .content -->

<?php $this->need('footer.php'); ?>
