<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<aside class="sidebar sidebar-left" role="complementary">
    <!-- 个人简介栏（固定显示在左栏顶部，不参与左右布局配置） -->
    <section class="profile-card reveal">
        <div class="profile-avatar">
            <?php if ($this->options->profileAvatar): ?>
                <img src="<?php $this->options->profileAvatar(); ?>" alt="<?php echo htmlspecialchars($this->options->profileName ?: $this->options->title); ?>">
            <?php else: ?>
                <?php nebulaAvatar($this->options->profileEmail, $this->options->profileName ?: $this->options->title, 84); ?>
            <?php endif; ?>
        </div>
        <h3 class="profile-name"><?php echo htmlspecialchars($this->options->profileName ?: $this->options->title); ?></h3>
        <p class="profile-bio"><?php echo htmlspecialchars($this->options->profileBio ?: $this->options->description); ?></p>
        <?php if (!empty($this->options->profileLinks)): ?>
        <div class="profile-links">
            <?php foreach (explode("\n", $this->options->profileLinks) as $line):
                $line = trim($line);
                if ($line === '') continue;
                $parts = array_map('trim', explode('|', $line));
                if (count($parts) < 2 || $parts[1] === '') continue;
            ?>
            <a href="<?php echo htmlspecialchars($parts[1]); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($parts[0]); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <?php foreach (nebulaSidebarItems('left') as $sbKey): ?>
        <?php nebulaSidebarWidget($this, $sbKey); ?>
    <?php endforeach; ?>
</aside><!-- end .sidebar-left -->
