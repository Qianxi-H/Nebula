<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="content">
    <?php if ($this->is('search')): ?>
    <section class="hero reveal">
        <h1><?php _e('搜索：'); ?><?php echo htmlspecialchars($this->request->s); ?></h1>
        <p><?php _e('共找到 '); ?><?php echo $this->getTotal(); ?><?php _e(' 篇匹配文章（标题与正文）'); ?></p>
    </section>
    <?php else: ?>
    <section class="hero reveal">
        <h1><?php $this->archiveTitle('', '', ''); ?></h1>
        <p><?php _e('共 '); ?><?php echo $this->getTotal(); ?><?php _e(' 篇文章'); ?></p>
    </section>
    <?php endif; ?>

    <?php if ($this->have()): ?>
    <?php while ($this->next()): ?>
    <?php $cover = nebulaCover($this); ?>
    <article class="post-card<?php if ($cover): ?> has-thumb<?php endif; ?> reveal" itemscope itemtype="http://schema.org/BlogPosting">
        <?php if ($cover): ?>
        <a class="pc-thumb" href="<?php $this->permalink(); ?>" aria-label="<?php $this->title(); ?>">
            <img src="<?php echo $cover; ?>" alt="<?php $this->title(); ?>" loading="lazy">
        </a>
        <div class="pc-body">
        <?php endif; ?>
        <div class="pc-meta">
            <span class="m-author"><?php $this->author(); ?></span>
            <span class="m-date"><?php $this->date('Y-m-d'); ?></span>
            <span class="m-cat"><?php $this->category(','); ?></span>
        </div>
        <h2 itemprop="name headline"><a itemprop="url" href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h2>
        <div class="pc-excerpt" itemprop="articleBody"><?php echo nebulaSummary($this); ?></div>
        <div class="pc-foot">
            <a class="read-more" href="<?php $this->permalink(); ?>"><?php _e('阅读全文'); ?> →</a>
            <span class="m-comment"><a href="<?php $this->permalink(); ?>#comments"><?php $this->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></a></span>
        </div>
        <?php if ($cover): ?>
        </div>
        <?php endif; ?>
    </article>
    <?php endwhile; ?>

    <?php $this->pageNav('&laquo;', '&raquo;', 3, '…', array('wrapTag' => 'div', 'wrapClass' => 'page-nav')); ?>
    <?php else: ?>
    <div class="empty-state reveal">
        <div class="empty-emoji">🔍</div>
        <p><?php _e('没有找到相关文章，换个关键词试试？'); ?></p>
    </div>
    <?php endif; ?>
</div><!-- end .content -->

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
