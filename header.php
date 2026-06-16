<?php
/**
 * 头部模板文件 - 复刻faxianai.com样式
 *
 * @package NavAi
 * @author 老九
 * @version 1.26.90
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_url = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
$is_home = is_front_page() || is_home();

// 获取已收录的AI数量
$ai_count = wp_count_posts('ai_tool')->publish;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

    <!-- 顶部导航栏 -->
    <header class="site-header">
        <div class="header-container">
            <!-- 移动端：菜单按钮 + Logo + 工具图标 -->
            <div class="header-mobile-top">
                <!-- 左侧菜单按钮 -->
                <button class="mobile-menu-btn" aria-label="菜单">
                    <i data-lucide="menu"></i>
                </button>

                <!-- 中间Logo -->
                <a href="<?php echo esc_url(home_url('/')); ?>" class="header-logo" rel="home">
                    <?php echo navai_get_logo(); ?>
                    <div class="logo-text-wrap">
                        <span class="logo-text"><?php echo esc_html(navai_get_logo_text()); ?></span>
                        <span class="logo-domain"><?php echo esc_html(navai_get_logo_domain()); ?></span>
                    </div>
                </a>

                <!-- 右侧搜索图标 -->
                <button class="tool-btn search-toggle" aria-label="搜索">
                    <i data-lucide="search"></i>
                </button>
            </div>

            <!-- 桌面端：左侧Logo + 中间导航 + 右侧搜索 -->
            <div class="header-desktop">
                <!-- 左侧：汉堡菜单 + Logo + 统计 -->
                <div class="header-brand">
                    <button class="mobile-menu-btn desktop-menu-toggle" aria-label="菜单">
                        <i data-lucide="menu"></i>
                    </button>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="header-logo" rel="home">
                        <span class="logo-text"><?php echo esc_html(navai_get_logo_text()); ?></span>
                    </a>
                    <span class="header-stats">已收录网站：<?php echo number_format($ai_count); ?>个</span>
                </div>

                <!-- 中间：导航菜单 -->
                <nav class="header-nav">
                    <?php
                    if (has_nav_menu('primary')) :
                        $menu_items = wp_get_nav_menu_items(wp_get_nav_menu_name('primary'));
                        if ($menu_items) :
                            foreach ($menu_items as $item) :
                                $is_current = (get_permalink() == $item->url) ? ' nav-item-current' : '';
                    ?>
                    <a href="<?php echo esc_url($item->url); ?>" class="nav-item<?php echo $is_current; ?>"><?php echo esc_html($item->title); ?></a>
                    <?php
                            endforeach;
                        endif;
                    endif;
                    ?>
                </nav>

                <!-- 右侧：搜索 -->
                <div class="header-search">
                    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="search" name="s" placeholder="搜索AI工具..." value="<?php echo get_search_query(); ?>">
                        <button type="submit" aria-label="搜索">
                            <i data-lucide="search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- 移动端搜索区域 -->
    <div class="mobile-search-section">
        <!-- 搜索类型标签 -->
        <div class="search-tabs">
            <button type="button" class="search-tab active" data-mode="search">搜索</button>
            <button type="button" class="search-tab" data-mode="image">图片</button>
            <button type="button" class="search-tab" data-mode="site">站内</button>
            <button type="button" class="search-tab" data-mode="deepseek">DeepSeek搜索</button>
        </div>

        <!-- 搜索框 -->
        <div class="mobile-search-box">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-box" id="search-form">
                <input type="search" name="s" class="search-input" id="search-input" placeholder="百度一下" value="<?php echo get_search_query(); ?>">
                <button type="submit" aria-label="搜索" id="search-submit">
                    <i data-lucide="search"></i>
                </button>
            </form>
        </div>

        <!-- 搜索引擎选择 -->
        <div class="search-engines" id="search-engines-container">
            <a href="https://www.baidu.com/s?wd=" class="search-engine active" data-placeholder="百度一下">百度</a>
            <a href="https://www.bing.com/search?q=" class="search-engine" data-placeholder="必应搜索">Bing</a>
            <a href="https://www.google.com/search?q=" class="search-engine" data-placeholder="Google一下">Google</a>
            <a href="https://so.toutiao.com/search?keyword=" class="search-engine" data-placeholder="头条搜索">头条</a>
        </div>
    </div>

    <!-- 主内容区 -->
    <main id="main" class="site-main">
        <div class="container">
            <div class="main-wrapper">
