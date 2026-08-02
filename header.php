<?php
/**
 * 头部模板文件 - 复刻faxianai.com样式
 *
 * @package NavAi
 * @author 老九
 * @version 1.29.22
 */

if ( ! defined('ABSPATH')) {
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

<body <?php body_class(); ?> <?php echo is_singular('ai_tool') ? 'data-post-id="' . esc_attr(get_queried_object_id()) . '"' : ''; ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( '跳至内容', 'navai' ); ?></a>

<div id="page" class="site">

	<!-- 顶部导航栏 -->
	<header class="site-header">
		<div class="header-container">
			<!-- 移动端：菜单按钮 + Logo + 工具图标 -->
			<div class="header-mobile-top">
				<!-- 左侧菜单按钮 -->
				<button class="mobile-menu-btn" aria-label="<?php esc_attr_e('菜单', 'navai'); ?>">
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
				<button class="tool-btn search-toggle" aria-label="<?php esc_attr_e('搜索', 'navai'); ?>">
					<i data-lucide="search"></i>
				</button>
			</div>

			<!-- 桌面端：左侧Logo + 中间导航 + 右侧搜索 -->
			<div class="header-desktop">
				<!-- 左侧：汉堡菜单 + Logo + 统计 -->
				<div class="header-brand">
					<button class="mobile-menu-btn desktop-menu-toggle" aria-label="<?php esc_attr_e('菜单', 'navai'); ?>">
						<i data-lucide="menu"></i>
					</button>
					<a href="<?php echo esc_url(home_url('/')); ?>" class="header-logo" rel="home">
						<span class="logo-text"><?php echo esc_html(navai_get_logo_text()); ?></span>
					</a>
					<span class="header-stats"><?php printf(esc_html__('已收录网站：%s个', 'navai'), number_format($ai_count)); ?></span>
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
						<input type="search" name="s" placeholder="<?php echo esc_attr__('搜索AI工具...', 'navai'); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
						<button type="submit" aria-label="<?php esc_attr_e('搜索', 'navai'); ?>">
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
			<button type="button" class="search-tab active" data-mode="search"><?php esc_html_e('搜索', 'navai'); ?></button>
			<button type="button" class="search-tab" data-mode="image"><?php esc_html_e('图片', 'navai'); ?></button>
			<button type="button" class="search-tab" data-mode="site"><?php esc_html_e('站内', 'navai'); ?></button>
			<button type="button" class="search-tab" data-mode="deepseek"><?php esc_html_e('DeepSeek搜索', 'navai'); ?></button>
		</div>

		<!-- 搜索框 -->
		<div class="mobile-search-box">
			<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-box" id="search-form">
				<input type="search" name="s" class="search-input" id="search-input" placeholder="<?php echo esc_attr__('百度一下', 'navai'); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
				<button type="submit" aria-label="<?php esc_attr_e('搜索', 'navai'); ?>" id="search-submit">
					<i data-lucide="search"></i>
				</button>
			</form>
		</div>

		<!-- 搜索引擎选择 -->
		<div class="search-engines" id="search-engines-container">
			<a href="https://www.baidu.com/s?wd=" class="search-engine active" data-placeholder="<?php echo esc_attr__('百度一下', 'navai'); ?>"><?php esc_html_e('百度', 'navai'); ?></a>
			<a href="https://www.bing.com/search?q=" class="search-engine" data-placeholder="<?php echo esc_attr__('必应搜索', 'navai'); ?>">Bing</a>
			<a href="https://www.google.com/search?q=" class="search-engine" data-placeholder="<?php echo esc_attr__('Google一下', 'navai'); ?>">Google</a>
			<a href="https://so.toutiao.com/search?keyword=" class="search-engine" data-placeholder="<?php echo esc_attr__('头条搜索', 'navai'); ?>"><?php esc_html_e('头条', 'navai'); ?></a>
		</div>
	</div>

	<!-- 主内容区 -->
	<main id="main" class="site-main">
		<div class="container">
			<div class="main-wrapper">
