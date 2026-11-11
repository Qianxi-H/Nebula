/* =========================================================
   Nebula —— 交互脚本
   功能：明暗主题 / 配色方案 / 阅读进度 / 滚动渐显 / 粒子星空 /
        鼠标光晕 / 回到顶部 / 导航收缩 / 移动端菜单 /
        文章目录 / 搜索浮层 / 宽度调节 / 代码复制 / 图片放大
   ========================================================= */
(function () {
    'use strict';

    var root = document.documentElement;
    var CFG = window.NEBULA || {};
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // 标记脚本已接管渐显，header 里的兜底计时器据此判断是否需要强行显示
    root.classList.add('reveal-ready');

    /* ---------- 滚动锁定（多个浮层共用，计数避免互相解锁） ---------- */
    var lockCount = 0;
    function lockScroll(on) {
        lockCount = Math.max(0, lockCount + (on ? 1 : -1));
        root.classList.toggle('scroll-locked', lockCount > 0);
    }

    /* ---------- 浏览器地址栏配色跟随明暗 ---------- */
    function syncMetaColor() {
        var m = document.getElementById('metaThemeColor');
        if (m) m.setAttribute('content', root.classList.contains('light') ? '#eef1f8' : '#07070f');
    }

    /* ---------- 代码高亮 ---------- */
    if (window.hljs) {
        try { hljs.highlightAll(); } catch (e) {}
    }

    /* ---------- 明暗主题切换 ---------- */
    var toggle = document.getElementById('themeToggle');
    function applyTheme(theme) {
        if (theme === 'light') root.classList.add('light');
        else root.classList.remove('light');
        try { localStorage.setItem('nebula-theme', theme); } catch (e) {}
        if (toggle) toggle.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
        syncMetaColor();
    }
    if (toggle) {
        toggle.setAttribute('aria-pressed', root.classList.contains('light') ? 'true' : 'false');
        toggle.addEventListener('click', function () {
            applyTheme(root.classList.contains('light') ? 'dark' : 'light');
        });
    }
    syncMetaColor();

    /* ---------- 配色方案切换（前台自选） ---------- */
    var schemeToggle = document.getElementById('schemeToggle');
    var schemePop = document.getElementById('schemePop');
    var schemeSwitcher = document.getElementById('schemeSwitcher');
    var swatches = schemePop ? [].slice.call(schemePop.querySelectorAll('.scheme-swatch')) : [];
    var schemeTimer;

    function applyScheme(name) {
        // 仅切换瞬间开启过渡，0.6s 后移除，避免影响正常 hover 与加载性能
        root.classList.add('scheme-transition');
        clearTimeout(schemeTimer);
        schemeTimer = setTimeout(function () { root.classList.remove('scheme-transition'); }, 650);
        root.setAttribute('data-scheme', name);
        try { localStorage.setItem('nebula-scheme', name); } catch (e) {}
        swatches.forEach(function (s) {
            s.classList.toggle('active', s.getAttribute('data-scheme') === name);
        });
        window.dispatchEvent(new Event('nebula:scheme'));
    }
    function closeScheme() {
        if (!schemePop) return;
        schemePop.classList.remove('open');
        schemePop.setAttribute('aria-hidden', 'true');
        if (schemeToggle) schemeToggle.setAttribute('aria-expanded', 'false');
    }

    if (schemeToggle && schemePop) {
        schemeToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var willOpen = !schemePop.classList.contains('open');
            if (searchOverlay && searchOverlay.classList.contains('open')) closeSearch();
            schemePop.classList.toggle('open', willOpen);
            schemePop.setAttribute('aria-hidden', willOpen ? 'false' : 'true');
            schemeToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
        swatches.forEach(function (s) {
            s.addEventListener('click', function () {
                applyScheme(s.getAttribute('data-scheme'));
                closeScheme();
            });
        });
        document.addEventListener('click', function (e) {
            if (schemeSwitcher && !schemeSwitcher.contains(e.target)) closeScheme();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && schemePop.classList.contains('open')) closeScheme();
        });
    }
    // 初始化高亮当前方案
    (function () {
        var cur = root.getAttribute('data-scheme') || 'aurora';
        swatches.forEach(function (s) { s.classList.toggle('active', s.getAttribute('data-scheme') === cur); });
    })();

    /* ---------- 内容宽度调节（桌面端，控制中间栏宽度） ---------- */
    var widthRange = document.getElementById('widthRange');
    var widthVal = document.getElementById('widthVal');
    var widthBtns = document.querySelectorAll('.wc-btn');
    var MIN_CONTAINER = 1180;  // 默认容器宽度（--maxw）
    var SIDE_TOTAL = 624;      // 左 230 + 右 290 + 两道 gap 60 + 容器左右 padding 44
    var WIDTH_EASE = 1.5;      // 缓动指数：低段更细腻，避免轻轻一拖就拉到满屏
    var WIDE_RATIO = 0.5;      // 「宽」预设：占可扩展空间的 50%

    function nebulaClamp(v, a, b) { return v < a ? a : (v > b ? b : v); }
    // 滑块百分比 -> 扩展比例（幂函数缓动）
    function nebulaPctToT(p) { return Math.pow(nebulaClamp(p, 0, 100) / 100, WIDTH_EASE); }
    function nebulaTToPct(t) { return 100 * Math.pow(nebulaClamp(t, 0, 1), 1 / WIDTH_EASE); }

    var WIDTH_NAMES = { 'default': '默认', wide: '宽', full: '布满' };

    // 当前滑块位置对应哪个预设（容差 2%，拖动到其它位置则都不选中）
    function nebulaPresetMode(pct) {
        var t = nebulaPctToT(pct);
        if (t <= 0.004) return 'default';
        if (t >= 0.996) return 'full';
        return (Math.abs(t - WIDE_RATIO) < 0.02) ? 'wide' : '';
    }

    function nebulaMarkPreset(mode) {
        for (var bi = 0; bi < widthBtns.length; bi++) {
            var on = widthBtns[bi].getAttribute('data-preset') === mode;
            widthBtns[bi].classList.toggle('active', on);
            widthBtns[bi].setAttribute('aria-pressed', on ? 'true' : 'false');
        }
    }

    function nebulaApplyWidth(pct) {
        if (!widthRange || !widthVal) return;
        // 用 clientWidth（不含滚动条）计算，保证「布满」正好等于可用宽度
        var vw = document.documentElement.clientWidth || window.innerWidth;
        if (vw < 1200) {
            // 视口不足 1200 时容器本已铺满，无可扩展空间，恢复默认
            root.style.removeProperty('--layout-w');
            return;
        }
        pct = nebulaClamp(parseFloat(pct) || 0, 0, 100);
        var containerW = MIN_CONTAINER + (vw - MIN_CONTAINER) * nebulaPctToT(pct);
        root.style.setProperty('--layout-w', containerW.toFixed(1) + 'px');

        var mode = nebulaPresetMode(pct);
        nebulaMarkPreset(mode);
        widthVal.textContent = WIDTH_NAMES[mode] || (Math.round(containerW - SIDE_TOTAL) + 'px');
    }

    if (widthRange) {
        var savedWidth = 0;
        try { savedWidth = parseFloat(localStorage.getItem('nebula-layout-width')) || 0; } catch (e) {}
        widthRange.value = savedWidth;
        nebulaApplyWidth(savedWidth);

        function saveWidth(p) { try { localStorage.setItem('nebula-layout-width', p); } catch (e) {} }

        widthRange.addEventListener('input', function () {
            var p = parseFloat(widthRange.value) || 0;
            nebulaApplyWidth(p);
            saveWidth(p);
        });

        for (var wi = 0; wi < widthBtns.length; wi++) {
            widthBtns[wi].addEventListener('click', function () {
                var mode = this.getAttribute('data-preset');
                var p = mode === 'default' ? 0 : (mode === 'full' ? 100 : Math.round(nebulaTToPct(WIDE_RATIO)));
                widthRange.value = p;
                nebulaApplyWidth(p);
                saveWidth(p);
            });
        }

        var widthRaf;
        window.addEventListener('resize', function () {
            cancelAnimationFrame(widthRaf);
            widthRaf = requestAnimationFrame(function () {
                nebulaApplyWidth(parseFloat(widthRange.value) || 0);
            });
        });
    }

    /* ---------- 导航滚动收缩 ---------- */
    var header = document.getElementById('siteHeader');
    var progress = document.getElementById('progressBar');
    var toTop = document.getElementById('toTop');

    function onScroll() {
        var y = window.pageYOffset || document.documentElement.scrollTop;
        if (header) header.classList.toggle('scrolled', y > 20);
        if (progress) {
            var h = document.documentElement.scrollHeight - window.innerHeight;
            progress.style.width = (h > 0 ? (y / h) * 100 : 0) + '%';
        }
        if (toTop) toTop.classList.toggle('show', y > 400);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---------- 回到顶部 ---------- */
    if (toTop) {
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        });
    }

    /* ---------- 移动端菜单 ---------- */
    var menuToggle = document.getElementById('menuToggle');
    var nav = document.getElementById('siteNav');
    if (menuToggle && nav) {
        menuToggle.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                nav.classList.remove('open');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ---------- 滚动渐显 ---------- */
    var revealEls = [].slice.call(document.querySelectorAll('.reveal'));
    if (revealEls.length) {
        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealEls.forEach(function (el) { el.classList.add('in'); });
        } else {
            // threshold 用 0：只要露出一点就触发。若用百分比阈值，高于视口的长文章
            // （比如手机上几千像素的正文）永远达不到阈值，会一直停留在隐藏状态。
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
                });
            }, { threshold: 0, rootMargin: '0px 0px -40px 0px' });
            revealEls.forEach(function (el) { io.observe(el); });
        }
    }

    /* ---------- 文章目录 TOC ---------- */
    var toc = document.getElementById('toc');
    var tocList = document.getElementById('tocList');
    if (toc && tocList) {
        var content = document.querySelector('.post-content');
        if (content) {
            var heads = [].slice.call(content.querySelectorAll('h2, h3'));
            if (heads.length >= 2) {
                // 1) 为标题补上 id（供锚点跳转）
                heads.forEach(function (h, i) {
                    if (!h.id) h.id = 'toc-h-' + i;
                });

                // 2) 生成目录列表（h3 作为子级缩进）
                var html = '';
                heads.forEach(function (h) {
                    var cls = h.tagName === 'H3' ? ' class="toc-sub"' : '';
                    var text = (h.textContent || '').replace(/\s+/g, ' ').trim();
                    html += '<li><a href="#' + h.id + '" data-target="' + h.id + '"' + cls + '>' + text + '</a></li>';
                });
                tocList.innerHTML = html;
                // 桌面端把目录挪进右栏：右栏是悬浮列，目录跟着一起跟随阅读，
                // 而且不会像「粘在正文顶部的整块卡片」那样盖住正文。
                var tocHome = toc.parentNode;
                var tocMq = window.matchMedia('(min-width: 1080px)');
                function placeToc() {
                    var right = document.querySelector('.sidebar-right');
                    if (tocMq.matches && right) {
                        if (toc.parentNode !== right) right.insertBefore(toc, right.firstChild);
                    } else if (tocHome && toc.parentNode !== tocHome) {
                        tocHome.insertBefore(toc, tocHome.firstChild);
                    }
                }
                placeToc();
                var tocPlaceRaf;
                window.addEventListener('resize', function () {
                    cancelAnimationFrame(tocPlaceRaf);
                    tocPlaceRaf = requestAnimationFrame(placeToc);
                });
                toc.style.display = 'block';
                // 兜底：万一列表为空（异常情况下）则整体隐藏，避免出现空白卡片
                if (!tocList.children.length) toc.style.display = 'none';

                var links = [].slice.call(tocList.querySelectorAll('a'));

                // 3) 滚动时高亮当前章节
                var headerH = header ? header.offsetHeight : 66;
                function setActive() {
                    var offset = headerH + 22;
                    var current = 0;
                    for (var i = 0; i < heads.length; i++) {
                        if (heads[i].getBoundingClientRect().top <= offset) current = i;
                        else break;
                    }
                    links.forEach(function (l, i) { l.classList.toggle('active', i === current); });
                }
                window.addEventListener('scroll', setActive, { passive: true });
                window.addEventListener('resize', function () {
                    headerH = header ? header.offsetHeight : 66;
                    setActive();
                });
                setActive();

                // 4) 移动端默认折叠目录
                if (window.innerWidth < 720) toc.classList.add('collapsed');

                // 5) 折叠按钮
                var tocToggle = document.getElementById('tocToggle');
                var tocUserToggled = false;
                if (tocToggle) {
                    tocToggle.addEventListener('click', function () {
                        tocUserToggled = true;
                        var collapsed = toc.classList.toggle('collapsed');
                        tocToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    });
                }

                // 5b) 窗口宽度跨越断点时自动跟随（用户手动操作过则不再干预）
                var tocRaf;
                window.addEventListener('resize', function () {
                    if (tocUserToggled) return;
                    cancelAnimationFrame(tocRaf);
                    tocRaf = requestAnimationFrame(function () {
                        toc.classList.toggle('collapsed', window.innerWidth < 720);
                    });
                });

                // 6) 点击目录项：即时高亮 + 平滑滚动（全局 scroll-behavior 已开启）
                links.forEach(function (l) {
                    l.addEventListener('click', function () {
                        links.forEach(function (x) { x.classList.remove('active'); });
                        l.classList.add('active');
                    });
                });
            } else {
                // 标题不足，直接隐藏目录
                toc.style.display = 'none';
            }
        }
    }

    /* ---------- 全局搜索浮层 ---------- */
    var searchOpenBtn = document.getElementById('searchOpen');
    var searchOverlay = document.getElementById('searchOverlay');
    var searchCloseBtn = document.getElementById('searchClose');
    var searchBackdrop = document.getElementById('searchBackdrop');
    var searchInput = document.getElementById('searchInput');
    var searchOpen = false;

    function openSearch() {
        if (!searchOverlay || searchOpen) return;
        searchOpen = true;
        if (nav && nav.classList.contains('open')) {
            nav.classList.remove('open');
            if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
        }
        searchOverlay.classList.add('open');
        searchOverlay.setAttribute('aria-hidden', 'false');
        lockScroll(true);
        setTimeout(function () { if (searchInput) searchInput.focus(); }, 70);
    }
    function closeSearch() {
        if (!searchOverlay || !searchOpen) return;
        searchOpen = false;
        searchOverlay.classList.remove('open');
        searchOverlay.setAttribute('aria-hidden', 'true');
        lockScroll(false);
        if (searchOpenBtn) searchOpenBtn.focus();
    }
    if (searchOpenBtn) searchOpenBtn.addEventListener('click', openSearch);
    if (searchCloseBtn) searchCloseBtn.addEventListener('click', closeSearch);
    if (searchBackdrop) searchBackdrop.addEventListener('click', closeSearch);
    // 页面内任意「搜索」按钮（如 404 页）
    [].forEach.call(document.querySelectorAll('[data-open-search]'), function (b) {
        b.addEventListener('click', openSearch);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && searchOpen) closeSearch();
        // Ctrl / ⌘ + K 快速唤起搜索
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            if (searchOpen) closeSearch(); else openSearch();
        }
    });

    /* ---------- 鼠标光晕 ---------- */
    var glow = document.getElementById('cursorGlow');
    if (glow && !reduceMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        var gx = 0, gy = 0, cx = 0, cy = 0, raf;
        window.addEventListener('mousemove', function (e) { gx = e.clientX; gy = e.clientY; if (!raf) loop(); });
        function loop() {
            cx += (gx - cx) * 0.18; cy += (gy - cy) * 0.18;
            glow.style.left = cx + 'px'; glow.style.top = cy + 'px';
            if (Math.abs(gx - cx) > 0.5 || Math.abs(gy - cy) > 0.5) raf = requestAnimationFrame(loop);
            else raf = null;
        }
    } else if (glow) {
        glow.style.display = 'none';
    }

    /* ---------- 粒子星空 ---------- */
    var canvas = document.getElementById('nebula-particles');
    if (canvas && window.NEBULA_PARTICLES !== false && !reduceMotion) {
        var ctx = canvas.getContext('2d');
        var dpr = Math.min(window.devicePixelRatio || 1, 2);
        var w, h, particles = [], rafId = null;
        var accent = getComputedStyle(root).getPropertyValue('--accent').trim() || '#22d3ee';
        window.addEventListener('nebula:scheme', function () {
            var a = getComputedStyle(root).getPropertyValue('--accent').trim();
            if (a) accent = a;
        });

        function resize() {
            w = canvas.width = window.innerWidth * dpr;
            h = canvas.height = window.innerHeight * dpr;
            canvas.style.width = window.innerWidth + 'px';
            canvas.style.height = window.innerHeight + 'px';
        }
        function init() {
            // 小屏减少粒子数量，降低绘制开销
            var count = Math.min(90, Math.floor(window.innerWidth / 16));
            particles = [];
            for (var i = 0; i < count; i++) {
                particles.push({
                    x: Math.random() * w, y: Math.random() * h,
                    vx: (Math.random() - 0.5) * 0.25 * dpr,
                    vy: (Math.random() - 0.5) * 0.25 * dpr,
                    r: (Math.random() * 1.6 + 0.6) * dpr
                });
            }
        }
        function draw() {
            ctx.clearRect(0, 0, w, h);
            for (var i = 0; i < particles.length; i++) {
                var p = particles[i];
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > w) p.vx *= -1;
                if (p.y < 0 || p.y > h) p.vy *= -1;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = accent;
                ctx.globalAlpha = 0.55;
                ctx.fill();
                for (var j = i + 1; j < particles.length; j++) {
                    var q = particles[j];
                    var dx = p.x - q.x, dy = p.y - q.y;
                    var dist = Math.sqrt(dx * dx + dy * dy);
                    var max = 130 * dpr;
                    if (dist < max) {
                        ctx.globalAlpha = (1 - dist / max) * 0.22;
                        ctx.strokeStyle = accent;
                        ctx.lineWidth = dpr * 0.6;
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y); ctx.stroke();
                    }
                }
            }
            ctx.globalAlpha = 1;
            rafId = requestAnimationFrame(draw);
        }
        function stop() { if (rafId) { cancelAnimationFrame(rafId); rafId = null; } }
        function start() { if (!rafId) draw(); }

        resize(); init(); draw();

        var rt;
        window.addEventListener('resize', function () {
            clearTimeout(rt);
            rt = setTimeout(function () { stop(); resize(); init(); start(); }, 200);
        });
        // 切到后台标签页时暂停动画，省电、避免无谓占用
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) stop(); else start();
        });
    } else if (canvas) {
        canvas.style.display = 'none';
    }

    /* ---------- 代码块：语言标签 + 复制按钮 ---------- */
    (function initCodeTools() {
        if (CFG.codeCopy === false) return;
        var blocks = document.querySelectorAll('.post-content pre');
        if (!blocks.length) return;

        var copyIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>';
        var copyWord = CFG.copyText || '复制';
        var doneWord = CFG.copiedText || '已复制';

        function bindCopy(codeEl, btn) {
            btn.addEventListener('click', function () {
                var text = codeEl ? (codeEl.innerText || codeEl.textContent || '') : '';
                var label = btn.querySelector('span');

                function done() {
                    btn.classList.add('done');
                    if (label) label.textContent = doneWord;
                    setTimeout(function () {
                        btn.classList.remove('done');
                        if (label) label.textContent = copyWord;
                    }, 1600);
                }
                function fallback() {
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.top = '-1000px';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    document.body.removeChild(ta);
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done, fallback);
                } else {
                    fallback();
                }
            });
        }

        for (var i = 0; i < blocks.length; i++) {
            var pre = blocks[i];
            if (pre.parentNode && pre.parentNode.className === 'pre-wrap') continue;

            var code = pre.querySelector('code');
            var wrap = document.createElement('div');
            wrap.className = 'pre-wrap';
            pre.parentNode.insertBefore(wrap, pre);
            wrap.appendChild(pre);

            var lang = '';
            if (code && code.className) {
                var m = /(?:^|\s)language-([\w+#.-]+)/.exec(code.className);
                if (m) lang = m[1];
            }

            var tools = document.createElement('div');
            tools.className = 'pre-tools';

            var tag = document.createElement('span');
            tag.className = 'code-lang';
            tag.textContent = lang || 'code';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'code-copy';
            btn.setAttribute('aria-label', copyWord + '代码');
            btn.innerHTML = copyIcon + '<span aria-live="polite">' + copyWord + '</span>';

            tools.appendChild(tag);
            tools.appendChild(btn);
            wrap.appendChild(tools);
            bindCopy(code, btn);
        }
    })();

    /* ---------- 图片点击放大 ---------- */
    (function initImgZoom() {
        var imgs = document.querySelectorAll('.post-content img');
        if (!imgs.length) return;

        var overlay = null;
        var opened = false;

        function build() {
            overlay = document.createElement('div');
            overlay.className = 'img-zoom';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-label', '图片预览');
            overlay.addEventListener('click', close);
            document.body.appendChild(overlay);
        }
        function open(src, alt) {
            if (!overlay) build();
            overlay.innerHTML = '';
            var im = document.createElement('img');
            im.src = src;
            im.alt = alt || '';
            overlay.appendChild(im);
            overlay.classList.add('open');
            opened = true;
            lockScroll(true);
        }
        function close() {
            if (!overlay || !opened) return;
            opened = false;
            overlay.classList.remove('open');
            lockScroll(false);
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && opened) close();
        });

        [].forEach.call(imgs, function (img) {
            // 已经被链接包裹的图片不接管点击，避免抢走原本的跳转
            if (img.closest && img.closest('a')) return;
            img.addEventListener('click', function () {
                open(img.getAttribute('data-src') || img.currentSrc || img.src, img.alt);
            });
        });
    })();
})();
