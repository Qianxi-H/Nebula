<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php if (!function_exists('threadedComments')): ?>
<?php function threadedComments($comments, $options) {
    // 「博主」识别：评论作者就是文章作者（作者 ID 非 0 才成立，避免游客误判）
    $isOwner  = ($comments->authorId && $comments->ownerId && $comments->authorId == $comments->ownerId);
    // 博主评论优先用后台配置的邮箱取头像（QQ 邮箱可显示 QQ 头像）
    $ownerMail = trim((string) Helper::options()->profileEmail);
    $avatarMail = ($isOwner && $ownerMail !== '') ? $ownerMail : $comments->mail;
    ?>
    <li id="<?php $comments->theId(); ?>" class="comment-body<?php if ($isOwner): ?> comment-by-author<?php endif; ?>">
        <div class="comment-author">
            <?php nebulaAvatar($avatarMail, $comments->author, 40); ?>
            <div class="comment-author-meta">
                <cite class="fn"><?php $comments->author(); ?><?php if ($isOwner): ?><span class="op-tag"><?php _e('博主'); ?></span><?php endif; ?></cite>
                <div class="comment-meta">
                    <time datetime="<?php $comments->date('c'); ?>"><?php $comments->date('Y-m-d H:i'); ?></time>
                    <?php if ('waiting' == $comments->status): ?>
                    <span class="comment-waiting"><?php _e('待审核'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="comment-content"><?php $comments->content(); ?></div>
        <div class="comment-reply"><?php $comments->reply(); ?></div>
        <?php if ($comments->children): ?>
        <div class="comment-children"><?php $comments->threadedComments(); ?></div>
        <?php endif; ?>
    </li>
<?php } ?>
<?php endif; ?>

<div class="comments-wrap" id="comments">
    <?php $this->comments()->to($comments); ?>

    <?php if ($comments->have()): ?>
        <h3><?php $this->commentsNum(_t('暂无评论'), _t('仅有一条评论'), _t('已有 %d 条评论')); ?></h3>
        <?php $comments->listComments(); ?>
        <?php nebulaPager($comments); ?>
    <?php endif; ?>

    <?php if ($this->allow('comment')): ?>
    <div id="<?php $this->respondId(); ?>" class="respond">
        <div class="cancel-comment-reply">
            <?php $comments->cancelReply(); ?>
        </div>
        <h3 id="response"><?php _e('发表评论'); ?></h3>
        <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" role="form" class="comment-form">
            <?php if ($this->user->hasLogin()): ?>
                <p class="cf-login"><?php _e('登录身份：'); ?><a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>
                    <a href="<?php $this->options->logoutUrl(); ?>"><?php _e('退出'); ?> &raquo;</a></p>
            <?php else: ?>
                <div class="cf-fields">
                    <p class="cf-item">
                        <label for="author" class="required"><?php _e('称呼'); ?></label>
                        <input type="text" name="author" id="author" class="text" value="<?php $this->remember('author'); ?>" required>
                    </p>
                    <p class="cf-item">
                        <label for="mail"<?php if ($this->options->commentsRequireMail): ?> class="required"<?php endif; ?>><?php _e('邮箱'); ?></label>
                        <input type="email" name="mail" id="mail" class="text" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
                    </p>
                    <p class="cf-item">
                        <label for="url"<?php if ($this->options->commentsRequireURL): ?> class="required"<?php endif; ?>><?php _e('网址'); ?></label>
                        <input type="url" name="url" id="url" class="text" placeholder="https://" value="<?php $this->remember('url'); ?>"<?php if ($this->options->commentsRequireURL): ?> required<?php endif; ?>>
                    </p>
                </div>
            <?php endif; ?>
            <p class="cf-item cf-item-full">
                <label for="textarea" class="required"><?php _e('内容'); ?></label>
                <textarea rows="7" name="text" id="textarea" class="textarea" required><?php $this->remember('text'); ?></textarea>
            </p>
            <p class="cf-submit">
                <button type="submit" class="submit"><?php _e('提交评论'); ?></button>
            </p>
        </form>
    </div>
    <?php else: ?>
        <h3 class="comments-closed"><?php _e('评论已关闭'); ?></h3>
    <?php endif; ?>
</div>
