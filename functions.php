<?php
/**
 * Nebula —— 现代化炫酷 Typecho 主题
 *
 * 特性：动态极光背景 / 粒子星空 / 玻璃拟态 / 渐变发光 /
 *      滚动渐显 / 阅读进度条 / 明暗主题切换 / 鼠标光晕
 *
 * @package Nebula
 * @author WorkBuddy
 * @version 1.0.0
 * @link https://www.workbuddy.cn
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 主题设置面板（控制台 -> 外观 -> 设置）
 */
function themeConfig($form)
{
    $enableParticles = new Typecho_Widget_Helper_Form_Element_Radio(
        'enableParticles',
        array('1' => _t('开启'), '0' => _t('关闭')),
        '1',
        _t('粒子星空背景'),
        _t('关闭后页面将只保留极光渐变背景，性能更好。')
    );
    $form->addInput($enableParticles);

    $colorScheme = new Typecho_Widget_Helper_Form_Element_Select(
        'colorScheme',
        array(
            'aurora'  => _t('极光青（默认）'),
            'purple'  => _t('紫梦'),
            'peach'   => _t('蜜桃'),
            'emerald' => _t('翠绿'),
            'lava'    => _t('熔岩'),
            'ocean'   => _t('海洋')
        ),
        'aurora',
        _t('默认配色方案'),
        _t('首次访问时使用的配色；访问者可在前台点击导航栏的调色板图标随时切换并记忆。')
    );
    $form->addInput($colorScheme);

    $defaultTheme = new Typecho_Widget_Helper_Form_Element_Radio(
        'defaultTheme',
        array('dark' => _t('深色（默认）'), 'light' => _t('浅色'), 'auto' => _t('跟随系统')),
        'dark',
        _t('默认明暗主题'),
        _t('首次访问时使用的明暗，之后用户可手动切换并记忆。')
    );
    $form->addInput($defaultTheme);

    /* ---------- 侧边栏组件（每个组件选择：左栏 / 右栏 / 不显示） ----------
     * 注意：不要用 Checkbox（数组型）——Typecho 的 Form::getParams() 在字段首次
     * 未保存（值为 null）时会把数组中转成 null 默认值，导致勾选无法持久化。
     * Select / Radio 为字符串型，可稳定保存。
     */
    $sbDefs = array(
        'ShowCategory'       => array(_t('分类目录'), 'left'),
        'ShowRecentPosts'    => array(_t('最新文章'), 'right'),
        'ShowRecentComments' => array(_t('最近回复'), 'right'),
        'ShowArchive'        => array(_t('归档'), 'right'),
        'ShowOther'          => array(_t('其它（登录 / RSS）'), 'right'),
    );
    foreach ($sbDefs as $sbKey => $sbDef) {
        $defaultPos = $sbDef[1];
        $otherPos   = ($defaultPos === 'left') ? 'right' : 'left';
        // 把默认项放在首位：字段尚未保存时下拉框会默认显示它
        $opts = array(
            $defaultPos => ($defaultPos === 'left') ? _t('左栏') : _t('右栏'),
            $otherPos   => ($otherPos === 'left') ? _t('左栏') : _t('右栏'),
            'hide'      => _t('不显示'),
        );
        $el = new Typecho_Widget_Helper_Form_Element_Select(
            'sb_' . $sbKey,
            $opts,
            $defaultPos,
            $sbDef[0],
            _t('选择该组件显示在左栏、右栏，或隐藏。个人简介卡片固定显示在左栏顶部，不在此列。')
        );
        $form->addInput($el);
    }

    /* ---------- 个人简介栏（左侧栏展示） ---------- */
    $profileName = new Typecho_Widget_Helper_Form_Element_Text(
        'profileName',
        NULL,
        '',
        _t('站长昵称（个人简介栏）'),
        _t('显示在左侧个人简介栏，留空则默认使用站点标题。')
    );
    $form->addInput($profileName);

    $profileBio = new Typecho_Widget_Helper_Form_Element_Textarea(
        'profileBio',
        NULL,
        '',
        _t('个人简介'),
        _t('左侧简介栏的一句话介绍，留空则使用站点描述。')
    );
    $form->addInput($profileBio);

    $profileAvatar = new Typecho_Widget_Helper_Form_Element_Text(
        'profileAvatar',
        NULL,
        '',
        _t('个人头像地址'),
        _t('可选。填写图片 URL 作为简介栏头像；留空则自动生成与主题配色相符的渐变头像。')
    );
    $form->addInput($profileAvatar);

    $profileLinks = new Typecho_Widget_Helper_Form_Element_Textarea(
        'profileLinks',
        NULL,
        '',
        _t('社交链接（每行一个）'),
        _t('格式：名称|链接，例如：GitHub|https://github.com/xxx 。留空则不显示。')
    );
    $form->addInput($profileLinks);

    /* ---------- 页脚备案号 ---------- */
    $icpNumber = new Typecho_Widget_Helper_Form_Element_Text(
        'icpNumber',
        NULL,
        '',
        _t('ICP 备案号'),
        _t('显示在页脚 “Theme Nebula” 之后，并自动链接到 https://beian.miit.gov.cn/ 。留空则不显示。例如：粤ICP备xxxxxxxx号')
    );
    $form->addInput($icpNumber);
}

/**
 * 文章自定义字段（编辑文章时可见）
 */
function themeFields($layout)
{
    $cover = new Typecho_Widget_Helper_Form_Element_Text(
        'cover',
        NULL,
        NULL,
        _t('封面图地址'),
        _t('填写图片 URL 作为列表封面；留空则自动使用正文中的第一张图片。')
    );
    $layout->addItem($cover);

    $excerpt = new Typecho_Widget_Helper_Form_Element_Textarea(
        'summary',
        NULL,
        NULL,
        _t('自定义摘要'),
        _t('留空则自动截取正文前 120 字作为列表摘要。')
    );
    $layout->addItem($excerpt);
}

/**
 * 取出文章封面：优先自定义字段，其次正文首图
 */
function nebulaCover($obj)
{
    if (!empty($obj->fields->cover)) return $obj->fields->cover;
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $obj->text, $m)) return $m[1];
    return '';
}

/**
 * 取出文章摘要：优先自定义字段，其次取 <!--more--> 之前的内容，最后自动截取。
 * 说明：Typecho 的 $obj->excerpt 已按 <!--more--> 截取（并处理 Markdown / 自动分段），
 *       故有手动截断标记时原样展示标记前的内容，不额外截断。
 */
function nebulaSummary($obj, $len = 160)
{
    if (!empty($obj->fields->summary)) return $obj->fields->summary;

    $hasMore = (strpos((string) $obj->text, '<!--more-->') !== false);
    $html = (string) $obj->excerpt;

    // excerpt 为空（纯图片/纯标签等）时退回正文，避免卡片空白
    if (trim(strip_tags($html)) === '') $html = (string) $obj->text;

    // 块级标签转空格，避免段落文字粘连；再去掉脚本样式与全部标签
    $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html);
    $html = preg_replace('#</?(p|div|li|ul|ol|h[1-6]|blockquote|pre|tr|td|th|br|hr)\b[^>]*>#i', ' ', $html);
    $raw  = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $html)));
    $raw  = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $raw  = trim(preg_replace('/\s+/u', ' ', $raw));

    if ($raw === '') $raw = trim((string) $obj->title);

    // 统一转义，避免摘要中的 < > & 被当作 HTML
    $raw = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');

    if ($hasMore) return $raw;
    return mb_substr($raw, 0, $len, 'UTF-8') . (mb_strlen($raw, 'UTF-8') > $len ? '…' : '');
}

/**
 * 返回指定侧（left/right）按固定顺序排列的组件名数组。
 * 每个组件通过后台下拉选择「左栏 / 右栏 / 不显示」，天然互斥、不会重复。
 * 字段尚未保存（值为 null）时，使用内置默认布局。
 */
function nebulaSidebarItems($side)
{
    // 组件 => 默认位置（分类在左，其余在右，与旧版一致）
    $defaults = array(
        'ShowCategory'       => 'left',
        'ShowRecentPosts'    => 'right',
        'ShowRecentComments' => 'right',
        'ShowArchive'        => 'right',
        'ShowOther'          => 'right',
    );

    $items   = array();
    $options = Helper::options();

    foreach ($defaults as $key => $def) {
        $pos = $options->{'sb_' . $key};
        if ($pos === null || $pos === '') {
            $pos = $def;              // 未配置时用默认位置
        }
        if ($pos === $side) {
            $items[] = $key;          // 顺序即为 $defaults 的定义顺序
        }
    }

    return $items;
}

/**
 * 渲染单个侧边栏组件（左右两栏共用）。
 *
 * @param object $ctx 模板上下文（即模板中的 $this，提供 widget()/user/options）
 * @param string $key 组件标识（ShowCategory / ShowRecentPosts / ...）
 */
function nebulaSidebarWidget($ctx, $key)
{
    switch ($key) {
        case 'ShowCategory': ?>
    <section class="widget reveal">
        <h3 class="widget-title"><?php _e('分类'); ?></h3>
        <?php $ctx->widget('Widget_Metas_Category_List')->listCategories('wrapClass=widget-list'); ?>
    </section>
        <?php break;

        case 'ShowRecentPosts': ?>
    <section class="widget reveal">
        <h3 class="widget-title"><?php _e('最新文章'); ?></h3>
        <ul class="widget-list">
            <?php $ctx->widget('Widget_Contents_Post_Recent')->parse('<li><a href="{permalink}">{title}</a></li>'); ?>
        </ul>
    </section>
        <?php break;

        case 'ShowRecentComments': ?>
    <section class="widget reveal">
        <h3 class="widget-title"><?php _e('最近回复'); ?></h3>
        <ul class="widget-list">
            <?php $ctx->widget('Widget_Comments_Recent')->to($comments); ?>
            <?php while ($comments->next()): ?>
            <li><a href="<?php $comments->permalink(); ?>"><?php $comments->author(false); ?></a>：<?php $comments->excerpt(30, '…'); ?></li>
            <?php endwhile; ?>
        </ul>
    </section>
        <?php break;

        case 'ShowArchive': ?>
    <section class="widget reveal">
        <h3 class="widget-title"><?php _e('归档'); ?></h3>
        <ul class="widget-list">
            <?php $ctx->widget('Widget_Contents_Post_Date', 'type=month&format=F Y')->parse('<li><a href="{permalink}">{date}</a></li>'); ?>
        </ul>
    </section>
        <?php break;

        case 'ShowOther':
            // 注意：$user / $options 是 Widget\Base 的 protected 属性，只在模板文件的类作用域内可直接访问。
            // 本函数是全局函数（不在类作用域），必须通过 alloc()/Helper::options() 取，否则魔术取值返回 null。
            $sbUser    = \Widget\User::alloc();
            $sbOptions = Helper::options();
            ?>
    <section class="widget reveal">
        <h3 class="widget-title"><?php _e('其它'); ?></h3>
        <ul class="widget-list">
            <?php if ($sbUser->hasLogin()): ?>
                <li><a href="<?php $sbOptions->adminUrl(); ?>"><?php _e('进入后台'); ?> (<?php $sbUser->screenName(); ?>)</a></li>
                <li><a href="<?php $sbOptions->logoutUrl(); ?>"><?php _e('退出'); ?></a></li>
            <?php else: ?>
                <li><a href="<?php $sbOptions->adminUrl('login.php'); ?>"><?php _e('登录'); ?></a></li>
            <?php endif; ?>
            <li><a href="<?php $sbOptions->feedUrl(); ?>"><?php _e('文章 RSS'); ?></a></li>
            <li><a href="<?php $sbOptions->commentsFeedUrl(); ?>"><?php _e('评论 RSS'); ?></a></li>
        </ul>
    </section>
        <?php break;
    }
}

/**
 * 统一头像：QQ 邮箱显示 QQ 头像，其余使用与主题配色一致的渐变圆底 + 人形图标
 * @param string|null $email 邮箱（QQ 号邮箱可识别）
 * @param string|null $name  显示名称（仅用于生成稳定的渐变色相，不显示文字）
 * @param int         $size  像素尺寸
 */
function nebulaAvatar($email, $name, $size = 40)
{
    $email = strtolower(trim((string) ($email ?? '')));
    $name  = trim((string) ($name ?? ''));
    $h   = hexdec(substr(md5($name . '|' . $email), 0, 6)) % 360;
    $rot = $h . 'deg';
    $style = '--av-size:' . (int) $size . 'px;--rot:' . $rot . ';';
    // 中性人形图标作为无图时的占位（不再显示姓名首字，避免与昵称重复）
    $icon = '<svg class="avatar-initial" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
          . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
          . '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>';

    if (preg_match('/^(\d{4,})@qq\.com$/i', $email, $m)) {
        // QQ 邮箱：使用 QQ 头像，加载失败时回退到主题渐变 + 人形图标
        $url = 'https://q.qlogo.cn/headimg_dl?dst_uin=' . $m[1] . '&spec=100';
        echo '<span class="avatar avatar-qq" style="' . $style . '">' . $icon
           . '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($name) . '" loading="lazy" onerror="this.remove()">'
           . '</span>';
    } else {
        // 其它：主题强调色渐变圆底 + 人形图标（按名称哈希得到不同色相，既随机又贴合主题）
        echo '<span class="avatar" style="' . $style . '">' . $icon . '</span>';
    }
}

/**
 * 估算阅读时长（分钟），中文按约 350 字/分钟，英文单词另计
 */
function nebulaReadingTime($obj)
{
    $text = preg_replace('/<[^>]+>/', '', $obj->text);
    $text = preg_replace('/\s+/', '', $text);
    $len = mb_strlen($text, 'UTF-8');
    return max(1, ceil($len / 350));
}
