<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
        </div><!-- end .layout -->
    </main><!-- end .site-main -->

    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <span class="brand-name"><?php $this->options->title(); ?></span>
                <p class="footer-desc"><?php $this->options->description(); ?></p>
            </div>
            <nav class="footer-links">
                <a href="<?php $this->options->siteUrl(); ?>"><?php _e('首页'); ?></a>
                <a href="<?php $this->options->feedUrl(); ?>"><?php _e('文章 RSS'); ?></a>
                <a href="<?php $this->options->commentsFeedUrl(); ?>"><?php _e('评论 RSS'); ?></a>
                <?php if ($this->user->hasLogin()): ?>
                    <a href="<?php $this->options->adminUrl(); ?>"><?php _e('进入后台'); ?></a>
                <?php else: ?>
                    <a href="<?php $this->options->adminUrl('login.php'); ?>"><?php _e('登录'); ?></a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?>
            <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>
            <span class="dot">·</span> Powered by <a href="http://www.typecho.org" target="_blank" rel="noopener">Typecho</a>
            <span class="dot">·</span> Theme <a href="https://www.workbuddy.cn" target="_blank" rel="noopener">Nebula</a>
            <?php if (!empty(Helper::options()->icpNumber)): ?>
                <span class="dot">·</span>
                <a class="icp-link" href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars(Helper::options()->icpNumber); ?></a>
            <?php endif; ?>
        </div>
    </footer>

    <script>
        // 主题运行时配置（配色由 data-scheme 控制，见 header 预绘制脚本与 style.css）
        window.NEBULA_PARTICLES = <?php echo (Helper::options()->enableParticles == '0') ? 'false' : 'true'; ?>;
        window.NEBULA = {
            codeCopy: <?php echo (Helper::options()->enableCodeCopy == '0') ? 'false' : 'true'; ?>,
            copyText: <?php echo json_encode(_t('复制')); ?>,
            copiedText: <?php echo json_encode(_t('已复制')); ?>
        };
    </script>
    <!-- defer：不阻塞首屏解析，且保证执行顺序（core → 语言包 → 主题脚本） -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <!-- 额外语言包：MATLAB / Verilog / SystemVerilog / Perl（需在 core 之后、nebula.js 之前，确保注册成功） -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/matlab.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/verilog.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/systemverilog.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/perl.min.js"></script>
    <script defer src="<?php echo nebulaAsset('js/nebula.js'); ?>"></script>
    <?php $this->footer(); ?>
</body>
</html>
