<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php $this->archiveTitle(array(
            'category'  =>  _t('分类 %s'),
            'search'    =>  _t('搜索 %s'),
            'tag'       =>  _t('标签 %s'),
            'author'    =>  _t('%s 的文章')
        ), '', ' - '); ?><?php $this->options->title(); ?></title>
    <meta name="description" content="<?php $this->options->description(); ?>">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('style.css'); ?>?v=7">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
    <!-- 在首屏绘制前确定主题，避免闪烁 -->
    <script>
        (function () {
            try {
                document.documentElement.classList.add('js');
                // 在首屏绘制前确定配色方案，避免闪烁
                var s = localStorage.getItem('nebula-scheme');
                if (!s) s = '<?php echo htmlspecialchars(Helper::options()->colorScheme ?: 'aurora'); ?>';
                document.documentElement.setAttribute('data-scheme', s);
                var t = localStorage.getItem('nebula-theme');
                var def = '<?php echo htmlspecialchars(Helper::options()->defaultTheme ?: 'dark'); ?>';
                var light = t === 'light' || (!t && def === 'light') ||
                    (!t && def === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches);
                if (light) document.documentElement.classList.add('light');
                // 内容宽度：首屏前预置，避免宽度跳变（缓动曲线需与 nebula.js 保持一致）
                var lw = parseFloat(localStorage.getItem('nebula-layout-width')) || 0;
                if (lw > 0 && window.innerWidth >= 1200) {
                    var cw = 1180 + (window.innerWidth - 1180) * Math.pow(Math.min(lw, 100) / 100, 1.5);
                    document.documentElement.style.setProperty('--layout-w', cw.toFixed(1) + 'px');
                }
                // 安全兜底：若外部脚本加载失败，2 秒后强制显示内容，避免永久隐藏
                setTimeout(function () {
                    var els = document.querySelectorAll('.reveal:not(.in)');
                    for (var i = 0; i < els.length; i++) els[i].classList.add('in');
                }, 2000);
            } catch (e) {}
        })();
    </script>
    <?php $this->header(); ?>
</head>
<body class="<?php echo $this->is('index') ? 'nebula-home' : 'nebula-page'; ?>">
    <!-- 动态极光背景 -->
    <div class="nebula-bg" aria-hidden="true">
        <span class="aurora a1"></span>
        <span class="aurora a2"></span>
        <span class="aurora a3"></span>
        <span class="aurora a4"></span>
    </div>

    <!-- 粒子星空 -->
    <canvas id="nebula-particles" aria-hidden="true"></canvas>

    <!-- 鼠标光晕 -->
    <div class="cursor-glow" id="cursorGlow" aria-hidden="true"></div>

    <!-- 顶部阅读进度条 -->
    <div class="progress-bar" id="progressBar" aria-hidden="true"></div>

    <!-- 回到顶部 -->
    <button class="to-top" id="toTop" aria-label="回到顶部" title="回到顶部">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </button>

    <!-- 顶部导航 -->
    <header class="site-header" id="siteHeader">
        <div class="container nav-wrap">
            <a class="brand" href="<?php $this->options->siteUrl(); ?>">
                <?php if ($this->options->logoUrl): ?>
                    <img class="brand-logo" src="<?php $this->options->logoUrl(); ?>" alt="<?php $this->options->title(); ?>">
                <?php endif; ?>
                <span class="brand-name"><?php $this->options->title(); ?></span>
            </a>

            <nav class="site-nav" id="siteNav">
                <a<?php if ($this->is('index')): ?> class="active"<?php endif; ?> href="<?php $this->options->siteUrl(); ?>"><?php _e('首页'); ?></a>
                <?php $this->widget('Widget_Contents_Page_List')->to($pages); ?>
                <?php while ($pages->next()): ?>
                <a<?php if ($this->is('page', $pages->slug)): ?> class="active"<?php endif; ?> href="<?php $pages->permalink(); ?>" title="<?php $pages->title(); ?>"><?php $pages->title(); ?></a>
                <?php endwhile; ?>
            </nav>

            <div class="nav-actions">
                <div class="scheme-switcher" id="schemeSwitcher">
                    <button class="icon-btn" id="schemeToggle" aria-label="切换配色方案" title="配色方案">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="2.2"/><circle cx="6.5" cy="11" r="2.2"/><circle cx="17.5" cy="13" r="2.2"/><circle cx="9.5" cy="17" r="2.2"/><path d="M12 3a9 9 0 1 0 0 18 4.5 4.5 0 0 0 0-9c1.7 0 3-1.3 3-3a3 3 0 0 0-3-3z"/></svg>
                    </button>
                    <div class="scheme-pop" id="schemePop" role="menu" aria-label="配色方案">
                        <p class="scheme-pop-title">配色方案</p>
                        <div class="scheme-grid">
                            <button class="scheme-swatch" data-scheme="aurora"  type="button" role="menuitem" style="--s1:#22d3ee;--s2:#818cf8;--s3:#f0abfc"><span class="sw"></span><span>极光青</span></button>
                            <button class="scheme-swatch" data-scheme="purple"  type="button" role="menuitem" style="--s1:#a855f7;--s2:#6366f1;--s3:#ec4899"><span class="sw"></span><span>紫梦</span></button>
                            <button class="scheme-swatch" data-scheme="peach"   type="button" role="menuitem" style="--s1:#fb7185;--s2:#f59e0b;--s3:#f472b6"><span class="sw"></span><span>蜜桃</span></button>
                            <button class="scheme-swatch" data-scheme="emerald" type="button" role="menuitem" style="--s1:#34d399;--s2:#10b981;--s3:#a3e635"><span class="sw"></span><span>翠绿</span></button>
                            <button class="scheme-swatch" data-scheme="lava"    type="button" role="menuitem" style="--s1:#f97316;--s2:#ef4444;--s3:#fbbf24"><span class="sw"></span><span>熔岩</span></button>
                            <button class="scheme-swatch" data-scheme="ocean"   type="button" role="menuitem" style="--s1:#38bdf8;--s2:#2563eb;--s3:#818cf8"><span class="sw"></span><span>海洋</span></button>
                        </div>
                    </div>
                </div>
                <button class="icon-btn" id="searchOpen" aria-label="搜索" title="全局搜索">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
                <button class="icon-btn" id="themeToggle" aria-label="切换明暗主题" title="切换主题">
                    <svg class="icon-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"/></svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/></svg>
                </button>
            </div>

            <button class="menu-toggle" id="menuToggle" aria-label="菜单">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <!-- 全局搜索浮层（搜索标题与正文） -->
    <div class="search-overlay" id="searchOverlay" aria-hidden="true">
        <div class="search-backdrop" id="searchBackdrop"></div>
        <div class="search-panel" role="dialog" aria-modal="true" aria-label="搜索">
            <form class="search-form" method="get" action="<?php $this->options->siteUrl(); ?>" role="search">
                <svg class="sf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" name="s" id="searchInput" placeholder="<?php _e('搜索文章标题与正文…'); ?>" autocomplete="off" aria-label="搜索关键词">
                <button type="button" class="sf-close" id="searchClose" aria-label="关闭搜索">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </form>
            <p class="search-hint"><?php _e('回车搜索 · Esc 关闭 · 同时匹配文章标题与正文'); ?></p>
        </div>
    </div>

    <main class="container site-main">
        <!-- 内容宽度调节（仅桌面端显示，控制中间栏宽度） -->
        <div class="width-ctl" id="widthCtl" role="group" aria-label="内容宽度调节">
            <span class="wc-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16M15 4v16"/></svg>
                <?php _e('内容宽度'); ?>
            </span>
            <div class="wc-presets" role="group" aria-label="宽度预设">
                <button type="button" class="wc-btn active" data-preset="default" aria-pressed="true"><?php _e('默认'); ?></button>
                <button type="button" class="wc-btn" data-preset="wide" aria-pressed="false"><?php _e('宽'); ?></button>
                <button type="button" class="wc-btn" data-preset="full" aria-pressed="false"><?php _e('布满'); ?></button>
            </div>
            <input type="range" id="widthRange" min="0" max="100" step="1" value="0" aria-label="调整中间栏宽度">
            <span class="wc-val" id="widthVal"><?php _e('默认'); ?></span>
        </div>
        <div class="layout">
            <!-- 左侧栏（个人简介 + 分类），桌面端置左、移动端置于正文之后 -->
            <?php $this->need('sidebar-left.php'); ?>
            <!-- 页面主体内容由各模板注入 -->
