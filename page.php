<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="content">
    <?php if (Helper::options()->enableToc != '0'): ?>
    <!-- 页面目录（长页面可用，由 nebula.js 自动生成） -->
    <nav class="toc" id="toc" aria-label="页面目录">
        <button class="toc-title" id="tocToggle" type="button" aria-expanded="true">
            <span class="toc-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <?php _e('目录'); ?>
            </span>
            <svg class="toc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="toc-body"><ol class="toc-list" id="tocList"></ol></div>
    </nav>
    <?php endif; ?>

    <article class="post-card page-card" itemscope itemtype="http://schema.org/WebPage">
        <header class="post-header">
            <span class="page-badge"><?php _e('页面'); ?></span>
            <h1 itemprop="name headline"><?php $this->title(); ?></h1>
        </header>
        <div class="post-content" itemprop="articleBody">
            <?php $this->content(); ?>
        </div>
    </article>

    <?php $this->need('comments.php'); ?>
</div><!-- end .content -->

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
