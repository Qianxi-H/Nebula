<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<?php $nebula_modified = nebulaModified($this); ?>
<div class="content">
    <?php if (Helper::options()->enableToc != '0'): ?>
    <!-- 文章目录（由 nebula.js 自动生成，无 JS 或标题不足两个时自动隐藏） -->
    <nav class="toc" id="toc" aria-label="文章目录">
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

    <!-- 正文不做渐显：它是首屏最大元素（LCP），延迟显示只会让人以为页面空白 -->
    <article class="post-card" itemscope itemtype="http://schema.org/BlogPosting">
        <header class="post-header">
            <h1 itemprop="name headline"><?php $this->title(); ?></h1>
            <div class="post-meta">
                <span class="pm-item pm-author"><?php nebulaAvatar($this->author->mail, $this->author->screenName, 30); ?><a itemprop="author" href="<?php $this->author->permalink(); ?>" rel="author"><?php $this->author(); ?></a></span>
                <span class="pm-item pm-date">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <time datetime="<?php $this->date('c'); ?>" itemprop="datePublished"><?php $this->date('Y-m-d'); ?></time>
                    <?php if ($nebula_modified): ?>
                    <span class="pm-updated"><?php _e('更新于'); ?> <time datetime="<?php echo (new \Typecho\Date((int) $this->modified))->format('c'); ?>" itemprop="dateModified"><?php echo $nebula_modified; ?></time></span>
                    <?php endif; ?>
                </span>
                <span class="pm-item pm-cat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-7l-2-2H5a2 2 0 0 0-2 2z"/></svg>
                    <?php $this->category(','); ?>
                </span>
                <span class="pm-item pm-read">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    <?php echo nebulaReadingTime($this); ?><?php _e(' 分钟阅读'); ?>
                </span>
                <span class="pm-item pm-words">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>
                    <?php echo number_format(nebulaWordCount($this)); ?><?php _e(' 字'); ?>
                </span>
                <span class="pm-item pm-comment">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.5 8.5 8.5 0 0 1-3.8-.9L3 21l1.9-5.7A8.5 8.5 0 1 1 21 11.5z"/></svg>
                    <a href="#comments"><?php $this->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></a>
                </span>
            </div>
        </header>

        <div class="post-content" itemprop="articleBody">
            <?php $this->content(); ?>
        </div>

        <div class="tags" itemprop="keywords">
            <?php $this->tags(' ', true, ''); ?>
        </div>
    </article>

    <nav class="post-near" aria-label="相邻文章">
        <div class="pn-item prev">
            <span class="label"><?php _e('上一篇'); ?></span>
            <span class="title"><?php $this->thePrev('%s', _t('没有了')); ?></span>
        </div>
        <div class="pn-item next">
            <span class="label"><?php _e('下一篇'); ?></span>
            <span class="title"><?php $this->theNext('%s', _t('没有了')); ?></span>
        </div>
    </nav>

    <?php $this->need('comments.php'); ?>
</div><!-- end .content -->

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
