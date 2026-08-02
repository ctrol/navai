<?php
/**
 * NavAi 主题功能文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.29.23
 * @license GPL-2.0+
 */

// 防止直接访问
if ( ! defined('ABSPATH')) {
	exit;
}

// ===== NavAi - 私有主题更新集成 =====
// 更新服务器：https://plugsrv.easynas.eu.org/api.php

define('NAVAI_UPDATE_SERVER', 'https://plugsrv.easynas.eu.org/api.php');
define('NAVAI_UPDATE_TOKEN',  '019425d25fa7414364efc049a6af3f825ad96362dd8badd1e3c4b5d314fab0ee');
define('NAVAI_UPDATE_SLUG',   'navai');
// License Key（留空则使用免费版更新）
define('NAVAI_UPDATE_LICENSE', '');
define('NAVAI_UPDATE_DOMAIN', parse_url(home_url(), PHP_URL_HOST));

// 获取当前主题版本
if ( ! function_exists('navai_get_theme_version')) {
	function navai_get_theme_version() {
		$theme = wp_get_theme(NAVAI_UPDATE_SLUG);
		if ( ! $theme->exists()) {
			return '0.0.0';
		}
		return $theme->get('Version');
	}
}

// 检查主题更新
add_filter('pre_set_site_transient_update_themes', function ($transient) {
	if ( ! is_object($transient)) {
		$transient = new stdClass();
	}
	if ( ! isset($transient->response)) {
		$transient->response = array();
	}
	if ( ! isset($transient->no_update)) {
		$transient->no_update = array();
	}

	$slug    = NAVAI_UPDATE_SLUG;
	$version = navai_get_theme_version();
	if ('0.0.0' === $version) {
		return $transient;
	}

	$url = NAVAI_UPDATE_SERVER . '?action=theme_check'
	     . '&slug=' . rawurlencode($slug)
	     . '&version=' . rawurlencode($version)
	     . '&token=' . rawurlencode(NAVAI_UPDATE_TOKEN)
	     . '&license=' . rawurlencode(NAVAI_UPDATE_LICENSE)
	     . '&domain=' . rawurlencode(NAVAI_UPDATE_DOMAIN);

	$response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
	if (is_wp_error($response)) {
		return $transient;
	}
	if (200 !== wp_remote_retrieve_response_code($response)) {
		return $transient;
	}

	$data = json_decode(wp_remote_retrieve_body($response), true);
	if ( ! $data || empty($data['status'])) {
		return $transient;
	}

	// 主题的 transient 项必须是数组（非对象）
	$baseInfo = array(
		'theme'        => $slug,
		'new_version'  => $version,
		'url'          => NAVAI_UPDATE_SERVER,
		'package'      => '',
		'requires'     => isset($data['requires']) ? $data['requires'] : '',
		'requires_php' => isset($data['requires_php']) ? $data['requires_php'] : '',
	);

	if ('update_available' === $data['status']) {
		$baseInfo['new_version'] = $data['new_version'];
		$baseInfo['package']     = $data['package'];
		$transient->response[$slug] = $baseInfo;
	} else {
		// no_update：无更新时也写入，否则 WP 5.5+ 的自动更新UI不会显示
		$transient->no_update[$slug] = $baseInfo;
	}
	return $transient;
});

// 主题详情
add_filter('themes_api', function ($false, $action, $args) {
	if ('theme_information' !== $action) {
		return $false;
	}
	if ($args->slug !== NAVAI_UPDATE_SLUG) {
		return $false;
	}

	$url = NAVAI_UPDATE_SERVER . '?action=theme_information'
	     . '&slug=' . rawurlencode(NAVAI_UPDATE_SLUG)
	     . '&token=' . rawurlencode(NAVAI_UPDATE_TOKEN);

	$response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
	if (is_wp_error($response)) {
		return $false;
	}
	if (200 !== wp_remote_retrieve_response_code($response)) {
		return $false;
	}

	$data = json_decode(wp_remote_retrieve_body($response));
	if ($data && ! empty($data->slug)) {
		$data->download_link = isset($data->download_link) ? $data->download_link : '';
		$data->sections      = (array) (isset($data->sections) ? $data->sections : array());
		return $data;
	}
	return $false;
}, 10, 3);

/**
 * 手动触发主题更新检查
 *
 * WordPress 默认每 12 小时才通过 cron 检查主题更新。
 * 此函数清除 update_themes transient，强制下次页面加载时重新检查。
 * 可在后台通过 ?navai_force_check=1 参数触发，或通过 admin_init 钩子调用。
 *
 * @since 1.29.6
 *
 * @return void
 */
function navai_force_update_check() {
	// 通过 URL 参数手动触发：后台任意页面加 ?navai_force_check=1
	if (is_admin() && current_user_can('update_themes') && isset($_GET['navai_force_check'])) {
		delete_site_transient('update_themes');
		// 强制重新获取更新数据
		wp_update_themes();
		// 重定向去掉参数，避免重复触发
		$redirect = remove_query_arg('navai_force_check');
		wp_safe_redirect($redirect);
		exit;
	}
}
add_action('admin_init', 'navai_force_update_check');

/**
 * 在通用设置页面添加「检查主题更新」按钮
 *
 * @since 1.29.6
 *
 * @return void
 */
function navai_render_update_check_button() {
	if ( ! current_user_can('update_themes')) {
		return;
	}
	$check_url = wp_nonce_url(add_query_arg('navai_force_check', '1'), 'navai_force_check');
	?>
	<div class="navai-update-check notice notice-info inline" style="margin: 16px 0 0 0;">
		<p>
			<strong>NavAi</strong> —
			<a href="<?php echo esc_url($check_url); ?>"><?php esc_html_e('检查主题更新', 'navai'); ?></a>
		</p>
	</div>
	<?php
}

/**
 * ============================================================================
 * 1. 主题基础设置
 * ============================================================================
 */

/**
 * 主题初始化设置
 *
 * @return void
 */
function navai_theme_setup() {
	// 添加主题支持
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	));
	add_theme_support('custom-logo', array(
		'height'      => 36,
		'width'       => 150,
		'flex-height' => true,
		'flex-width'  => true,
	));
	add_theme_support('customize-selective-refresh-widgets');

	// 注册导航菜单
	register_nav_menus(array(
		'primary' => __('主导航菜单', 'navai'),
		'sidebar' => __('侧边栏分类菜单', 'navai'),
		'footer'  => __('底部链接菜单', 'navai'),
	));

	// 设置内容宽度
	global $content_width;
	if ( ! isset($content_width)) {
		$content_width = 1200;
	}
}
add_action('after_setup_theme', 'navai_theme_setup');

/**
 * ============================================================================
 * 2. 脚本和样式加载
 * ============================================================================
 */

/**
 * 加载主题脚本和样式
 *
 * @return void
 */
function navai_enqueue_scripts() {
	// 主题版本号（用于缓存控制）
	$theme_version = wp_get_theme()->get('Version');

	// 主样式
	wp_enqueue_style(
		'navai-style',
		get_stylesheet_uri(),
		array(),
		$theme_version
	);

	// Lucide Icons (本地文件)
	wp_enqueue_script(
		'lucide-icons',
		get_template_directory_uri() . '/assets/js/lucide.min.js',
		array(),
		'1.18.0',
		true
	);

	// Lucide Icons 初始化脚本
	wp_add_inline_script('lucide-icons', "(function(){function initLucide(){if(typeof lucide!=='undefined'){lucide.createIcons();}else{setTimeout(initLucide,100);}}if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',initLucide);}else{initLucide();}})();");

	// 主题主脚本
	wp_enqueue_script(
		'navai-script',
		get_template_directory_uri() . '/assets/js/main.js',
		array('jquery'),
		$theme_version,
		true
	);

	// 本地化脚本 - 传递AJAX配置
	wp_localize_script('navai-script', 'navaiAjax', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce('navai_nonce'),
	));
}
add_action('wp_enqueue_scripts', 'navai_enqueue_scripts');

/**
 * ============================================================================
 * 3. 侧边栏和小工具
 * ============================================================================
 */

/**
 * 注册侧边栏小工具区域
 *
 * @return void
 */
function navai_widgets_init() {
	register_sidebar(array(
		'name'          => __('侧边栏', 'navai'),
		'id'            => 'sidebar-1',
		'description'   => __('添加小工具到侧边栏区域', 'navai'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	));

	register_sidebar(array(
		'name'          => __('底部小工具区域', 'navai'),
		'id'            => 'footer-widgets',
		'description'   => __('添加小工具到底部区域', 'navai'),
		'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="footer-section-title">',
		'after_title'   => '</h4>',
	));
}
add_action('widgets_init', 'navai_widgets_init');

/**
 * ============================================================================
 * 4. 自定义文章类型 - AI工具
 * ============================================================================
 */

/**
 * 注册AI工具自定义文章类型
 *
 * @return void
 */
function navai_register_ai_tool_post_type() {
	$labels = array(
		'name'               => __('网址', 'navai'),
		'singular_name'      => __('网址', 'navai'),
		'menu_name'          => __('网址管理', 'navai'),
		'add_new'            => __('添加新网址', 'navai'),
		'add_new_item'       => __('添加新网址', 'navai'),
		'edit_item'          => __('编辑网址', 'navai'),
		'new_item'           => __('新网址', 'navai'),
		'view_item'          => __('查看网址', 'navai'),
		'search_items'       => __('搜索网址', 'navai'),
		'not_found'          => __('未找到网址', 'navai'),
		'not_found_in_trash' => __('回收站中未找到网址', 'navai'),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => true,
		'rewrite'             => array('slug' => 'navi'),
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-admin-generic',
		'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'comments'),
		'show_in_rest'        => true,
	);

	register_post_type('ai_tool', $args);
}
add_action('init', 'navai_register_ai_tool_post_type');

/**
 * 注册AI工具分类法
 *
 * @return void
 */
function navai_register_ai_tool_taxonomy() {
	$labels = array(
		'name'              => __('网址分类', 'navai'),
		'singular_name'     => __('网址分类', 'navai'),
		'search_items'      => __('搜索分类', 'navai'),
		'all_items'         => __('所有分类', 'navai'),
		'parent_item'       => __('父级分类', 'navai'),
		'parent_item_colon' => __('父级分类:', 'navai'),
		'edit_item'         => __('编辑分类', 'navai'),
		'update_item'       => __('更新分类', 'navai'),
		'add_new_item'      => __('添加新分类', 'navai'),
		'new_item_name'     => __('新分类名称', 'navai'),
		'menu_name'         => __('网址分类', 'navai'),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array('slug' => 'ai-category'),
		'show_in_rest'      => true,
	);

	register_taxonomy('ai_category', 'ai_tool', $args);
}
add_action('init', 'navai_register_ai_tool_taxonomy');

/**
 * 注册自定义字段为 REST API 可访问（Gutenberg 兼容）
 * 确保区块编辑器保存时这些字段不会丢失
 */
function navai_register_post_meta_fields() {
	$meta_fields = array(
		'_website_url'   => array('type' => 'string', 'single' => true, 'default' => '', 'show_in_rest' => true, 'sanitize_callback' => 'esc_url_raw'),
		'_site_icon_url' => array('type' => 'string', 'single' => true, 'default' => '', 'show_in_rest' => true, 'sanitize_callback' => 'esc_url_raw'),
		'_is_hot'        => array('type' => 'string', 'single' => true, 'default' => '', 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field'),
		'_is_new'        => array('type' => 'string', 'single' => true, 'default' => '', 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field'),
		'_site_tags'     => array('type' => 'string', 'single' => true, 'default' => '', 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field'),
	);

	foreach ($meta_fields as $key => $args) {
		register_post_meta('ai_tool', $key, $args);
	}
}
add_action('init', 'navai_register_post_meta_fields', 20);

/**
 * ============================================================================
 * 5a. 后台列表自定义列
 * ============================================================================
 */

/**
 * 自定义网址列表的列
 *
 * @param array $columns 现有列
 * @return array 修改后的列
 */
function navai_custom_admin_columns($columns) {
	$new_columns = array();

	// 在标题列前插入图标列
	if (isset($columns['title'])) {
		unset($new_columns['title']);
	}

	$new_columns['cb']              = $columns['cb'];
	$new_columns['navai_icon']      = __('图标', 'navai');
	$new_columns['title']           = $columns['title'];
	$new_columns['navai_url']       = __('网址', 'navai');
	$new_columns['taxonomy-ai_category'] = $columns['taxonomy-ai_category'];
	$new_columns['navai_marks']     = __('标记', 'navai');
	$new_columns['navai_visits']    = __('访问数', 'navai');

	// 保留评论列
	if (isset($columns['comments'])) {
		$new_columns['comments'] = $columns['comments'];
	}

	$new_columns['navai_visit'] = __('操作', 'navai');
	$new_columns['date']        = $columns['date'];

	return $new_columns;
}
add_filter('manage_ai_tool_posts_columns', 'navai_custom_admin_columns');

/**
 * 注册可排序列
 *
 * @param array $sortable 可排序列
 * @return array
 */
function navai_sortable_columns($sortable) {
	$sortable['navai_visits'] = 'navai_visits';
	return $sortable;
}
add_filter('manage_edit-ai_tool_sortable_columns', 'navai_sortable_columns');

/**
 * 处理访问数排序请求
 *
 * @param WP_Query $query 查询对象
 */
function navai_visits_orderby($query) {
	if ( ! is_admin() || ! $query->is_main_query()) {
		return;
	}

	if ($query->get('orderby') === 'navai_visits') {
		$query->set('meta_key', '_post_views');
		$query->set('orderby', 'meta_value_num');
	}
}
add_action('pre_get_posts', 'navai_visits_orderby');

/**
 * 渲染自定义列内容
 *
 * @param string $column  列名称
 * @param int    $post_id 文章ID
 */
function navai_custom_column_content($column, $post_id) {
	switch ($column) {
		case 'navai_icon':
			$icon_url  = get_post_meta($post_id, '_site_icon_url', true);
			$site_url  = get_post_meta($post_id, '_website_url', true);
			if ($icon_url) {
				$favicon = $icon_url;
			} elseif ($site_url) {
				$host = wp_parse_url($site_url, PHP_URL_HOST);
				$favicon = $host ? 'https://api.iowen.cn/favicon/' . urlencode($host) . '.png' : '';
			} else {
				$favicon = '';
			}
			if ($favicon) {
				echo '<img src="' . esc_url($favicon) . '" alt="" style="width:20px;height:20px;border-radius:3px;vertical-align:middle;" onerror="this.style.visibility=\'hidden\';">';
			} else {
				echo '<span class="dashicons dashicons-globe" style="font-size:20px;color:#ccc;vertical-align:middle;"></span>';
			}
			break;

		case 'navai_url':
			$url = get_post_meta($post_id, '_website_url', true);
			if ($url) {
				$display = wp_parse_url($url, PHP_URL_HOST) ?: $url;
				echo '<a href="' . esc_url($url) . '" target="_blank" style="color:#2271b1;" title="' . esc_attr($url) . '">' . esc_html($display) . '</a>';
			} else {
				echo '<span style="color:#bbb;">—</span>';
			}
			break;

		case 'navai_marks':
			$is_hot = get_post_meta($post_id, '_is_hot', true);
			$is_new = get_post_meta($post_id, '_is_new', true);
			if ($is_hot) {
				echo '<span style="display:inline-block;font-size:11px;padding:1px 8px;border-radius:3px;background:#fee7e7;color:#d63638;margin-right:4px;line-height:1.6;">' . esc_html__('热门', 'navai') . '</span>';
			}
			if ($is_new) {
				echo '<span style="display:inline-block;font-size:11px;padding:1px 8px;border-radius:3px;background:#e7f3fe;color:#2271b1;line-height:1.6;">' . esc_html__('新站', 'navai') . '</span>';
			}
			if ( ! $is_hot && ! $is_new) {
				echo '<span style="color:#bbb;">—</span>';
			}
			break;

		case 'navai_visits':
			$views      = intval(get_post_meta($post_id, '_post_views', true));
			$clicks     = intval(get_post_meta($post_id, '_click_count', true));
			echo '<span style="font-weight:600;color:#2271b1;">' . $views . '</span>';
			echo '<span style="font-size:11px;color:#999;"> / ' . $clicks . '</span>';
			break;

		case 'navai_visit':
			$permalink = get_permalink($post_id);
			echo '<a href="' . esc_url($permalink) . '" target="_blank" class="button button-small" style="font-size:11px;">' . esc_html__('访问', 'navai') . '</a>';
			break;
	}
}
add_action('manage_ai_tool_posts_custom_column', 'navai_custom_column_content', 10, 2);

/**
 * 后台列表页注入CSS样式
 */
function navai_admin_list_styles() {
	global $pagenow;
	$current_post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : '';

	if ($pagenow !== 'edit.php' || $current_post_type !== 'ai_tool') {
		return;
	}
	?>
	<style>
		/* 图标列 */
		.wp-list-table .column-navai_icon { width: 40px; text-align: center; }
		/* 网址列 */
		.wp-list-table .column-navai_url { width: 180px; }
		.wp-list-table .column-navai_url a {
			display: inline-block;
			max-width: 160px;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			vertical-align: middle;
		}
		/* 标记列 */
		.wp-list-table .column-navai_marks { width: 100px; }
		/* 访问数列 */
		.wp-list-table .column-navai_visits { width: 80px; text-align: center; }
		/* 操作列 */
		.wp-list-table .column-navai_visit { width: 60px; text-align: center; }
	</style>
	<?php
}
add_action('admin_head', 'navai_admin_list_styles');

/**
 * ============================================================================
 * 5. 自定义字段（元数据框）
 * ============================================================================
 */

/**
 * 添加AI工具详情元数据框
 *
 * @return void
 */
function navai_add_ai_tool_meta_box() {
	add_meta_box(
		'ai_tool_details',
		__('网址详情', 'navai'),
		'navai_ai_tool_meta_box_callback',
		'ai_tool',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'navai_add_ai_tool_meta_box');

/**
 * 元数据框回调函数
 *
 * @param WP_Post $post 当前文章对象
 * @return void
 */
function navai_ai_tool_meta_box_callback($post) {
	wp_nonce_field('navai_ai_tool_meta', 'navai_ai_tool_meta_nonce');

	$website_url  = get_post_meta($post->ID, '_website_url', true);
	$site_icon_url = get_post_meta($post->ID, '_site_icon_url', true);
	$is_hot        = get_post_meta($post->ID, '_is_hot', true);
	$is_new        = get_post_meta($post->ID, '_is_new', true);
	?>
	<style>
		.navai-meta-wrap { padding: 4px 0; }
		.navai-field { margin-bottom: 20px; }
		.navai-field label {
			display: block;
			font-weight: 600;
			font-size: 13px;
			color: #1d2327;
			margin-bottom: 6px;
		}
		.navai-field .description {
			font-size: 12px;
			color: #646970;
			margin-top: 4px;
		}
		.navai-url-row {
			display: flex;
			gap: 8px;
			align-items: center;
		}
		.navai-url-row input {
			flex: 1;
		}
		.navai-url-icon {
			width: 36px;
			height: 36px;
			border-radius: 8px;
			border: 1px solid #dcdcde;
			background: #f6f7f7;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
			overflow: hidden;
		}
		.navai-url-icon img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		.navai-url-icon .dashicons {
			color: #8c8f94;
			font-size: 20px;
		}
		.navai-checkbox-group {
			display: flex;
			gap: 20px;
			flex-wrap: wrap;
		}
		.navai-checkbox-group label {
			display: flex;
			align-items: center;
			gap: 6px;
			font-weight: 400;
			font-size: 13px;
			cursor: pointer;
		}
		.navai-divider {
			border: none;
			border-top: 1px solid #dcdcde;
			margin: 20px 0;
		}
	</style>

	<div class="navai-meta-wrap">
		<!-- 隐藏字段：保存采集到的图标URL -->
		<input type="hidden" id="site_icon_url" name="site_icon_url" value="<?php echo esc_url(get_post_meta($post->ID, '_site_icon_url', true)); ?>">

		<!-- 官网地址 -->
		<div class="navai-field">
			<label for="website_url"><?php esc_html_e('官网地址', 'navai'); ?></label>
			<div class="navai-url-row">
				<div class="navai-url-icon" id="url-icon-preview">
					<?php if ($site_icon_url) : ?>
						<img src="<?php echo esc_url($site_icon_url); ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
					<?php else : ?>
						<span class="dashicons dashicons-globe"></span>
					<?php endif; ?>
				</div>
				<input type="url" id="website_url" name="website_url"
                       value="<?php echo esc_url($website_url); ?>"
                       placeholder="https://example.com">
				<button type="button" id="fetch-site-info" class="button button-primary" style="display:inline-flex;align-items:center;gap:4px;flex-shrink:0;">
					<span class="dashicons dashicons-download" style="font-size:16px;width:16px;height:16px;line-height:1;"></span>
					<?php esc_html_e('获取网址信息', 'navai'); ?>
				</button>
			</div>
			<p class="description"><?php esc_html_e('输入网址后点击"获取网址信息"自动抓取网站图标、名称和描述', 'navai'); ?></p>
		</div>

		<hr class="navai-divider">

		<!-- 远程图片采集 -->
		<div class="navai-field">
			<label><?php esc_html_e('远程图片采集', 'navai'); ?></label>
			<p class="description" style="margin-bottom:8px;"><?php esc_html_e('自动扫描并采集文章内容中的外部图片到本地媒体库', 'navai'); ?></p>
			<button type="button" id="navai-scan-remote-images" class="button button-secondary" style="display:inline-flex;align-items:center;gap:4px;">
				<span class="dashicons dashicons-search" style="font-size:16px;width:16px;height:16px;line-height:1;"></span>
				<?php esc_html_e('扫描外链图片', 'navai'); ?>
			</button>
			<div id="navai-image-list" style="margin-top:12px;display:none;">
				<p style="font-weight:600;margin-bottom:6px;"><?php esc_html_e('发现以下外链图片：', 'navai'); ?></p>
				<div id="navai-image-items" style="max-height:200px;overflow-y:auto;border:1px solid #c3c4c7;border-radius:4px;padding:8px;background:#f6f7f7;"></div>
				<div style="margin-top:10px;display:flex;gap:8px;">
					<button type="button" id="navai-start-download" class="button button-primary" style="display:inline-flex;align-items:center;gap:4px;">
						<span class="dashicons dashicons-download" style="font-size:16px;width:16px;height:16px;line-height:1;"></span>
						<?php esc_html_e('开始采集', 'navai'); ?>
					</button>
					<button type="button" id="navai-cancel-download" class="button">
						<?php esc_html_e('取消', 'navai'); ?>
					</button>
				</div>
			</div>
			<div id="navai-download-progress" style="margin-top:8px;display:none;">
				<div style="background:#f0f0f1;border-radius:3px;overflow:hidden;height:20px;width:100%;">
					<div id="navai-download-bar" style="background:#2271b1;height:100%;width:0%;transition:width 0.3s;border-radius:3px;"></div>
				</div>
				<p id="navai-download-status" class="description" style="margin-top:4px;"><?php esc_html_e('准备中...', 'navai'); ?></p>
			</div>
		</div>

		<hr class="navai-divider">

		<!-- 标记选项 -->
		<div class="navai-field">
			<label><?php esc_html_e('标记选项', 'navai'); ?></label>
			<div class="navai-checkbox-group">
				<label>
					<input type="checkbox" name="is_hot" value="1" <?php checked($is_hot, '1'); ?>>
					<?php esc_html_e('热门推荐', 'navai'); ?>
				</label>
				<label>
					<input type="checkbox" name="is_new" value="1" <?php checked($is_new, '1'); ?>>
					<?php esc_html_e('新上线', 'navai'); ?>
				</label>
			</div>
		</div>
	</div>
	<?php
}

/**
 * 规范化网址 URL，用于重复检测
 * 去除尾部斜杠、统一协议为小写、去除 query string
 *
 * @param string $url 原始 URL
 * @return string 规范化后的 URL
 */
function navai_normalize_url($url) {
	$url = trim($url);
	if (empty($url)) {
		return '';
	}
	// 统一小写协议
	$url = preg_replace('#^([Hh][Tt][Tt][Pp][Ss]?)://#', strtolower('$1') . '://', $url);
	// 去除 fragment
	$url = preg_replace('/#.*$/', '', $url);
	// 去除尾部斜杠（根路径除外）
	$url = rtrim($url, '/');
	if ($url === 'http://' || $url === 'https://') {
		return '';
	}
	return $url;
}

/**
 * 检查网址是否已存在
 *
 * @param string $url 要检查的 URL
 * @param int    $exclude_post_id 排除的文章 ID（编辑时排除自身）
 * @return bool 是否已存在
 */
function navai_url_exists($url, $exclude_post_id = 0) {
	if (empty($url)) {
		return false;
	}

	$normalized = navai_normalize_url($url);

	// 先精确匹配
	$args = array(
		'post_type'      => 'ai_tool',
		'meta_key'       => '_website_url',
		'meta_value'     => $url,
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	);
	if ($exclude_post_id) {
		$args['post__not_in'] = array($exclude_post_id);
	}
	$existing = get_posts($args);
	if ( ! empty($existing)) {
		return true;
	}

	// 再用规范化 URL 匹配（防止尾部斜杠差异等）
	$args['meta_value'] = $normalized;
	$existing = get_posts($args);
	if ( ! empty($existing)) {
		return true;
	}

	// 检查带/不带尾部斜杠的变体
	$variants = array(
		rtrim($normalized, '/') . '/',
		rtrim($normalized, '/'),
	);
	foreach ($variants as $variant) {
		if ($variant === $url || $variant === $normalized) {
			continue;
		}
		$args['meta_value'] = $variant;
		if ($exclude_post_id) {
			$args['post__not_in'] = array($exclude_post_id);
		}
		$existing = get_posts($args);
		if ( ! empty($existing)) {
			return true;
		}
	}

	return false;
}

/**
 * 保存AI工具元数据
 *
 * @param int $post_id 文章ID
 * @return void
 */
function navai_save_ai_tool_meta($post_id) {
	// 安全检查
	if ( ! isset($_POST['navai_ai_tool_meta_nonce'])) {
		return;
	}

	if (!wp_verify_nonce($_POST['navai_ai_tool_meta_nonce'], 'navai_ai_tool_meta')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// 保存官网地址
	if (isset($_POST['website_url'])) {
		$website_url = esc_url_raw($_POST['website_url']);

		// 网址重复检测（排除当前编辑的文章）
		if ( ! empty($website_url) && navai_url_exists($website_url, $post_id)) {
			// 网址已存在，设置错误提示并恢复原值
			$old_url = get_post_meta($post_id, '_website_url', true);
			update_post_meta($post_id, '_website_url', $old_url);
			set_transient('navai_url_duplicate_error', sprintf(
				__('网址 "%s" 已存在，请勿重复添加', 'navai'),
				$website_url
			), 30);
		} else {
			update_post_meta($post_id, '_website_url', $website_url);
		}
	}

	// 保存热门标记
	$is_hot = isset($_POST['is_hot']) ? '1' : '';
	update_post_meta($post_id, '_is_hot', $is_hot);

	// 保存新上线标记
	$is_new = isset($_POST['is_new']) ? '1' : '';
	update_post_meta($post_id, '_is_new', $is_new);

	// 保存网站图标URL（空值不覆盖已有值，防止Gutenberg下隐藏字段未提交导致丢失）
	if (isset($_POST['site_icon_url']) && !empty(trim($_POST['site_icon_url']))) {
		update_post_meta($post_id, '_site_icon_url', esc_url_raw($_POST['site_icon_url']));
	}
}
add_action('save_post_ai_tool', 'navai_save_ai_tool_meta');

/**
 * 显示网址重复检测错误提示
 *
 * @return void
 */
function navai_show_url_duplicate_error() {
	$message = get_transient('navai_url_duplicate_error');
	if ($message) {
		delete_transient('navai_url_duplicate_error');
		$screen = get_current_screen();
		if ($screen && $screen->post_type === 'ai_tool') {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
		}
	}
}
add_action('admin_notices', 'navai_show_url_duplicate_error');

/**
 * ============================================================================
 * 5.5 图片采集插件兼容性
 * ============================================================================
 */

/**
 * 自动将 ai_tool 添加到图片采集插件支持的文章类型列表
 *
 * 兼容插件：Smart Auto Upload Images、Auto Upload Images、Sage Auto Upload Images 等
 *
 * @param array $post_types 插件支持的文章类型列表
 * @return array
 */
function navai_add_ai_tool_to_image_plugins($post_types) {
	if (!is_array($post_types)) {
		$post_types = array();
	}
	if (!in_array('ai_tool', $post_types)) {
		$post_types[] = 'ai_tool';
	}
	return $post_types;
}

// Smart Auto Upload Images 插件兼容性
add_filter('sauti_post_types', 'navai_add_ai_tool_to_image_plugins', 99);

// Auto Upload Images 插件兼容性（旧版钩子）
add_filter('aui_post_types', 'navai_add_ai_tool_to_image_plugins', 99);

/**
 * 确保 ai_tool 支持 editor
 *
 * @return void
 */
function navai_ensure_editor_support() {
	if (post_type_exists('ai_tool')) {
		add_post_type_support('ai_tool', 'editor');
	}
}
add_action('init', 'navai_ensure_editor_support', 20);

/**
 * ============================================================================
 * 6. 后台设置页面
 * ============================================================================
 */

/**
 * 添加主题设置菜单（两级）
 *
 * @return void
 */
function navai_add_admin_menu() {
	// 顶级菜单
	add_menu_page(
		__('主题设置', 'navai'),
		__('主题设置', 'navai'),
		'manage_options',
		'navai-settings',
		'navai_general_settings_page',
		'dashicons-admin-generic',
		60
	);

	// 子菜单 - 通用设置
	add_submenu_page(
		'navai-settings',
		__('通用设置', 'navai'),
		__('通用设置', 'navai'),
		'manage_options',
		'navai-settings',
		'navai_general_settings_page'
	);

	// 子菜单 - 页脚设置
	add_submenu_page(
		'navai-settings',
		__('页脚设置', 'navai'),
		__('页脚设置', 'navai'),
		'manage_options',
		'navai-footer-settings',
		'navai_footer_settings_page'
	);

	// 网址审核子菜单（在网址管理主菜单下）
	add_submenu_page(
		'edit.php?post_type=ai_tool',
		__('网址审核', 'navai'),
		__('网址审核', 'navai'),
		'manage_options',
		'navai-site-review',
		'navai_site_review_page'
	);

	// 批量添加子菜单（在网址管理主菜单下）
	add_submenu_page(
		'edit.php?post_type=ai_tool',
		__('批量添加', 'navai'),
		__('批量添加', 'navai'),
		'manage_options',
		'navai-batch-add',
		'navai_batch_add_page'
	);
}
add_action('admin_menu', 'navai_add_admin_menu');

/**
 * 通用设置页面
 *
 * @return void
 */
function navai_general_settings_page() {
	// 加载 WordPress 媒体上传器
	wp_enqueue_media();

	// 保存设置
	if (isset($_POST['navai_save_general']) && check_admin_referer('navai_general_nonce')) {
		update_option('navai_logo_text', sanitize_text_field($_POST['logo_text']));
		update_option('navai_logo_domain', sanitize_text_field($_POST['logo_domain']));
		update_option('navai_logo_url', esc_url_raw($_POST['logo_url']));
		update_option('navai_favicon_url', esc_url_raw($_POST['favicon_url']));
		update_option('navai_ranking_count', absint($_POST['ranking_count']));
		update_option('navai_content_ad', wp_kses_post($_POST['content_ad']));
		update_option('navai_sidebar_ad', wp_kses_post($_POST['sidebar_ad']));
		update_option('navai_category_order', sanitize_text_field($_POST['category_order']));
		echo '<div class="notice notice-success"><p>' . esc_html__('设置已保存', 'navai') . '</p></div>';
	}

	// 获取当前设置
	$logo_text      = get_option('navai_logo_text', '发现AI');
	$logo_domain    = get_option('navai_logo_domain', 'FAXIANAI.COM');
	$logo_url       = get_option('navai_logo_url', '');
	$favicon_url    = get_option('navai_favicon_url', '');
	$ranking_count  = get_option('navai_ranking_count', 10);
	$content_ad     = get_option('navai_content_ad', '');
	$sidebar_ad     = get_option('navai_sidebar_ad', '');
	$category_order = get_option('navai_category_order', '');

	// 获取所有一级分类用于排序参考
	$all_parent_cats = get_terms(array(
		'taxonomy'   => 'ai_category',
		'parent'     => 0,
		'hide_empty' => false,
		'fields'     => 'id=>name',
	));
	$category_order_list = '';
	if ( ! empty($all_parent_cats) && !is_wp_error($all_parent_cats)) {
		$parts = array();
		foreach ($all_parent_cats as $id => $name) {
			$parts[] = $id . '=' . $name;
		}
		$category_order_list = implode(', ', $parts);
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e('通用设置', 'navai'); ?></h1>
		<form method="post" action="" enctype="multipart/form-data">
			<?php wp_nonce_field('navai_general_nonce'); ?>
			<table class="form-table">
				<tr>
					<th><label for="logo_text"><?php esc_html_e('Logo文字', 'navai'); ?></label></th>
					<td>
						<input type="text" id="logo_text" name="logo_text"
                               value="<?php echo esc_attr($logo_text); ?>"
                               class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="logo_domain"><?php esc_html_e('Logo域名', 'navai'); ?></label></th>
					<td>
						<input type="text" id="logo_domain" name="logo_domain"
                               value="<?php echo esc_attr($logo_domain); ?>"
                               placeholder="<?php esc_attr_e('例如：FAXIANAI.COM', 'navai'); ?>"
                               class="regular-text">
						<p class="description"><?php esc_html_e('显示在Logo下方的小字域名', 'navai'); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="logo_url"><?php esc_html_e('Logo图片', 'navai'); ?></label></th>
					<td>
						<input type="url" id="logo_url" name="logo_url"
                               value="<?php echo esc_url($logo_url); ?>"
                               class="regular-text" placeholder="<?php esc_attr_e('输入图片URL或点击上传', 'navai'); ?>">
						<input type="button" class="button navai-upload-btn" data-target="logo_url" value="<?php esc_attr_e('上传图片', 'navai'); ?>">
						<?php if ($logo_url) : ?>
						<div class="navai-preview" style="margin-top:8px;">
							<img src="<?php echo esc_url($logo_url); ?>" style="max-width:120px;max-height:60px;border:1px solid #ddd;border-radius:4px;">
						</div>
						<?php endif; ?>
						<p class="description"><?php esc_html_e('留空则使用文字Logo', 'navai'); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="favicon_url"><?php esc_html_e('Favicon', 'navai'); ?></label></th>
					<td>
						<input type="url" id="favicon_url" name="favicon_url"
                               value="<?php echo esc_url($favicon_url); ?>"
                               class="regular-text" placeholder="<?php esc_attr_e('输入图片URL或点击上传', 'navai'); ?>">
						<input type="button" class="button navai-upload-btn" data-target="favicon_url" value="<?php esc_attr_e('上传图片', 'navai'); ?>">
						<?php if ($favicon_url) : ?>
						<div class="navai-preview" style="margin-top:8px;">
							<img src="<?php echo esc_url($favicon_url); ?>" style="max-width:32px;max-height:32px;border:1px solid #ddd;border-radius:4px;">
						</div>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><label for="ranking_count"><?php esc_html_e('排行榜数量', 'navai'); ?></label></th>
					<td>
						<input type="number" id="ranking_count" name="ranking_count"
                               value="<?php echo esc_attr($ranking_count); ?>"
                               class="small-text" min="1" max="50">
						<p class="description"><?php esc_html_e('详情页右侧排行榜显示的网址数量（默认10）', 'navai'); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="content_ad"><?php esc_html_e('详情页正文广告代码', 'navai'); ?></label></th>
					<td>
						<textarea id="content_ad" name="content_ad" rows="6" cols="50" class="large-text code"><?php echo esc_textarea($content_ad); ?></textarea>
						<p class="description"><?php esc_html_e('在详情页网站简介与缩略图之间显示的广告代码，支持HTML/JS（如百度联盟、Google AdSense等），建议尺寸 320x210', 'navai'); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="sidebar_ad"><?php esc_html_e('侧边栏广告代码', 'navai'); ?></label></th>
					<td>
						<textarea id="sidebar_ad" name="sidebar_ad" rows="6" cols="50" class="large-text code"><?php echo esc_textarea($sidebar_ad); ?></textarea>
						<p class="description"><?php esc_html_e('在详情页右侧排行榜下方显示的广告代码，支持HTML/JS（如百度联盟、Google AdSense等）', 'navai'); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="category_order"><?php esc_html_e('分类排序', 'navai'); ?></label></th>
					<td>
						<input type="text" id="category_order" name="category_order"
                               value="<?php echo esc_attr($category_order); ?>"
                               class="regular-text" placeholder="<?php esc_attr_e('例如：12,5,8,3,1', 'navai'); ?>">
						<p class="description"><?php esc_html_e('输入分类ID的排序，用英文逗号分隔。留空则按默认顺序显示。', 'navai'); ?></p>
						<?php if ($category_order_list) : ?>
						<p class="description" style="color:#666;">
							<strong><?php esc_html_e('当前可用分类：', 'navai'); ?></strong><br>
							<code style="font-size:12px;"><?php echo esc_html($category_order_list); ?></code>
						</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		<?php navai_render_update_check_button(); ?>
		<?php submit_button(__('保存设置', 'navai'), 'primary', 'navai_save_general'); ?>
	</form>
	</div>

	<script>
	jQuery(document).ready(function($) {
		$('.navai-upload-btn').on('click', function(e) {
			e.preventDefault();
			var targetId = $(this).data('target');
			var frame = wp.media({
				title: '<?php echo esc_js( __("选择图片", "navai") ); ?>',
				button: { text: '<?php echo esc_js( __("使用此图片", "navai") ); ?>' },
				multiple: false
			});
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				$('#' + targetId).val(attachment.url);
				// 更新预览
				var $preview = $('#' + targetId).closest('td').find('.navai-preview');
				if ($preview.length) {
					$preview.html('<img src="' + attachment.url + '" style="max-width:120px;max-height:60px;border:1px solid #ddd;border-radius:4px;">');
				} else {
					$('#' + targetId).after('<div class="navai-preview" style="margin-top:8px;"><img src="' + attachment.url + '" style="max-width:120px;max-height:60px;border:1px solid #ddd;border-radius:4px;"></div>');
				}
			});
			frame.open();
		});
	});
	</script>
	<?php
}

/**
 * 页脚设置页面
 *
 * @return void
 */
function navai_footer_settings_page() {
	// 保存设置
	if (isset($_POST['navai_save_footer']) && check_admin_referer('navai_footer_nonce')) {
		update_option('navai_footer_desc', sanitize_text_field($_POST['footer_desc']));
		update_option('navai_footer_copyright', sanitize_text_field($_POST['footer_copyright']));
		update_option('navai_footer_icp', sanitize_text_field($_POST['footer_icp']));
		update_option('navai_footer_gongan', sanitize_text_field($_POST['footer_gongan']));
		echo '<div class="notice notice-success"><p>' . esc_html__('设置已保存', 'navai') . '</p></div>';
	}

	// 获取当前设置
	$footer_desc        = get_option('navai_footer_desc', '发现AI，专业AI导航网站，一站式AI导航！');
	$footer_copyright   = get_option('navai_footer_copyright', '');
	$footer_icp         = get_option('navai_footer_icp', '');
	$footer_gongan      = get_option('navai_footer_gongan', '');
	?>
	<div class="wrap">
		<h1><?php esc_html_e('页脚设置', 'navai'); ?></h1>
		<p class="description"><?php esc_html_e('底部链接请通过「外观 → 菜单 → 底部链接菜单」管理。', 'navai'); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field('navai_footer_nonce'); ?>
			<table class="form-table">
				<tr>
					<th><label for="footer_desc"><?php esc_html_e('页脚描述', 'navai'); ?></label></th>
					<td>
						<input type="text" id="footer_desc" name="footer_desc"
                               value="<?php echo esc_attr($footer_desc); ?>"
                               class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="footer_copyright"><?php esc_html_e('版权信息', 'navai'); ?></label></th>
					<td>
						<input type="text" id="footer_copyright" name="footer_copyright"
                               value="<?php echo esc_attr($footer_copyright); ?>"
                               placeholder="<?php esc_attr_e('留空则使用默认：Copyright © 年份 网站名', 'navai'); ?>"
                               class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="footer_icp"><?php esc_html_e('ICP备案号', 'navai'); ?></label></th>
					<td>
						<input type="text" id="footer_icp" name="footer_icp"
                               value="<?php echo esc_attr($footer_icp); ?>"
                               placeholder="<?php esc_attr_e('例如：苏ICP备2023012627号', 'navai'); ?>"
                               class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="footer_gongan"><?php esc_html_e('公安备案号', 'navai'); ?></label></th>
					<td>
						<input type="text" id="footer_gongan" name="footer_gongan"
                               value="<?php echo esc_attr($footer_gongan); ?>"
                               placeholder="<?php esc_attr_e('例如：苏公网安备32011402012166号', 'navai'); ?>"
                               class="regular-text">
					</td>
				</tr>
			</table>
			<?php submit_button(__('保存设置', 'navai'), 'primary', 'navai_save_footer'); ?>
		</form>
	</div>
	<?php
}

/**
 * ============================================================================
 * 7. 辅助函数
 * ============================================================================
 */

/**
 * 获取Logo HTML
 *
 * @return string Logo HTML
 */
function navai_get_logo() {
	$logo_url = get_option('navai_logo_url', '');

	if ($logo_url) {
		return '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="site-logo-img">';
	}

	return '';
}

/**
 * 获取Logo文字
 *
 * @return string Logo文字
 */
function navai_get_logo_text() {
	return get_option('navai_logo_text', '发现AI');
}

/**
 * 获取Logo域名
 *
 * @return string Logo域名
 */
function navai_get_logo_domain() {
	return get_option('navai_logo_domain', 'FAXIANAI.COM');
}

/**
 * 获取Favicon
 *
 * @return string Favicon URL
 */
function navai_get_favicon() {
	$favicon_url = get_option('navai_favicon_url', '');

	if ($favicon_url) {
		return $favicon_url;
	}

	return '';
}

/**
 * 获取页脚内容
 *
 * @return string 页脚内容
 */
function navai_get_footer_content() {
	return get_option('navai_footer_content', '');
}

/**
 * 获取AI工具数量
 *
 * @return int AI工具数量
 */
function navai_get_ai_count() {
	$count = wp_count_posts('ai_tool');
	return $count->publish;
}

/**
 * ============================================================================
 * 8. 点击计数功能
 * ============================================================================
 */

/**
 * 增加点击计数
 *
 * @param int $post_id 文章ID
 * @return void
 */
function navai_increment_click_count($post_id) {
	$count = get_post_meta($post_id, '_click_count', true);
	$count = $count ? intval($count) + 1 : 1;
	update_post_meta($post_id, '_click_count', $count);
}

/**
 * 获取点击计数
 *
 * @param int $post_id 文章ID
 * @return int 点击次数
 */
function navai_get_click_count($post_id) {
	$count = get_post_meta($post_id, '_click_count', true);
	return $count ? intval($count) : 0;
}

/**
 * 增加浏览量计数
 *
 * @param int $post_id 文章ID
 * @return void
 */
function navai_increment_post_views($post_id) {
	$views = get_post_meta($post_id, '_post_views', true);
	$views = $views ? intval($views) + 1 : 1;
	update_post_meta($post_id, '_post_views', $views);
}

/**
 * 获取浏览量
 *
 * @param int $post_id 文章ID
 * @return int 浏览次数
 */
function navai_get_post_views($post_id) {
	$views = get_post_meta($post_id, '_post_views', true);
	return $views ? intval($views) : 0;
}

/**
 * 在访问详情页时增加浏览量
 * 使用 template_redirect 钩子确保在页面渲染前执行
 *
 * @return void
 */
function navai_track_post_views() {
	if ( ! is_singular('ai_tool')) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id) {
		return;
	}

	// 防止爬虫/预取请求增加计数
	if (wp_is_xml_request() && ! (defined('DOING_AJAX') && DOING_AJAX)) {
		return;
	}

	// 使用 cookie 防止同一会话内短时间内重复计数
	$cookie_key  = 'navai_viewed_' . $post_id;
	$viewed_time = isset($_COOKIE[$cookie_key]) ? intval($_COOKIE[$cookie_key]) : 0;
	$now         = time();

	// 同一篇文章 5 分钟内不重复计数
	if ($viewed_time && ($now - $viewed_time < 300)) {
		return;
	}

	navai_increment_post_views($post_id);

	// 设置 cookie 记录访问时间，5分钟过期
	setcookie($cookie_key, $now, $now + 300, '/');
}
add_action('template_redirect', 'navai_track_post_views');

/**
 * AJAX 增加点击数
 * 前端卡片点击外链时调用
 *
 * @return void
 */
function navai_ajax_increment_click() {
	check_ajax_referer('navai_nonce', 'nonce');

	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

	if ( ! $post_id || get_post_type($post_id) !== 'ai_tool') {
		wp_send_json_error(array('message' => __('无效的文章', 'navai')));
	}

	navai_increment_click_count($post_id);

	wp_send_json_success(array(
		'clicks' => navai_get_click_count($post_id),
	));
}
add_action('wp_ajax_navai_increment_click', 'navai_ajax_increment_click');
add_action('wp_ajax_nopriv_navai_increment_click', 'navai_ajax_increment_click');

/**
 * ============================================================================
 * 9. AJAX处理
 * ============================================================================
 */

/**
 * 搜索AI工具 AJAX
 *
 * @return void
 */
function navai_ajax_search() {
	check_ajax_referer('navai_nonce', 'nonce');

	$search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

	if (empty($search_term) || strlen($search_term) < 2) {
		wp_send_json_success(array());
	}

	$args = array(
		'post_type'      => 'ai_tool',
		'posts_per_page' => 20,
		's'              => $search_term,
	);

	$query = new WP_Query($args);
	$results = array();

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$results[] = array(
				'id'      => get_the_ID(),
				'title'   => get_the_title(),
				'excerpt' => wp_trim_words(get_the_excerpt(), 20),
				'url'     => get_permalink(),
				'icon'    => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
			);
		}
		wp_reset_postdata();
	}

	wp_send_json_success($results);
}
add_action('wp_ajax_navai_search', 'navai_ajax_search');
add_action('wp_ajax_nopriv_navai_search', 'navai_ajax_search');

/**
 * 自动采集网站信息 AJAX
 *
 * @return void
 */
function navai_ajax_fetch_site_info() {
	check_ajax_referer('navai_nonce', 'nonce');

	if (!current_user_can('edit_posts')) {
		wp_send_json_error(array('message' => __('权限不足', 'navai')));
	}

	$url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

	if (empty($url)) {
		wp_send_json_error(array('message' => __('请输入网址', 'navai')));
	}

	// 确保URL有协议
	if (!preg_match('/^https?:\/\//', $url)) {
		$url = 'https://' . $url;
	}

	// 检查缓存（10分钟有效期），避免重复采集同一网址
	$cache_key = 'navai_fetch_' . md5($url);
	$cached = get_transient($cache_key);
	if (false !== $cached && is_array($cached)) {
		wp_send_json_success($cached);
	}

	// SSRF 防护：禁止访问内网地址
	$parsed = parse_url($url);
	$host = isset($parsed['host']) ? strtolower($parsed['host']) : '';
	$ip = gethostbyname($host);
	$blocked_ranges = array(
		array('127.0.0.0', '8'),     // 127.0.0.0/8
		array('10.0.0.0', '8'),      // 10.0.0.0/8
		array('172.16.0.0', '12'),    // 172.16.0.0/12
		array('192.168.0.0', '16'),   // 192.168.0.0/16
		array('0.0.0.0', '8'),       // 0.0.0.0/8
		array('169.254.0.0', '16'),   // 链路本地
	);
	foreach ($blocked_ranges as $range) {
		if (navai_ip_in_range($ip, $range[0], $range[1])) {
			wp_send_json_error(array('message' => __('不允许访问内网地址', 'navai')));
		}
	}
	// 也检查 host 是否为 localhost 或纯 IP
	if ($host === 'localhost' || preg_match('/^[\d.]+$/', $host)) {
		wp_send_json_error(array('message' => __('不允许访问内网地址', 'navai')));
	}

	// 使用WordPress HTTP API获取网站内容
	// 允许重定向（最多5次），增强User-Agent模拟真实浏览器，增大超时
	$response = wp_remote_get($url, array(
		'timeout'     => 30,
		'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
		'sslverify'   => false,
		'redirection' => 5,
		'headers'     => array(
			'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8',
		),
	));

	if (is_wp_error($response)) {
		wp_send_json_error(array('message' => __('无法访问该网站：', 'navai') . $response->get_error_message()));
	}

	$body = wp_remote_retrieve_body($response);
	$status_code = wp_remote_retrieve_response_code($response);

	if ($status_code !== 200) {
		wp_send_json_error(array('message' => __('网站返回错误状态码：', 'navai') . $status_code));
	}

	// 解析HTML获取标题和描述
	$title = '';
	$description = '';
	$icon_url = '';

	// 编码检测：优先从 HTTP Content-Type header 获取，其次从 meta 标签获取
	$content_type = wp_remote_retrieve_header($response, 'content-type');
	$charset = '';
	if (preg_match('/charset\s*=\s*["\']?([\w\-]+)/i', $content_type, $m)) {
		$charset = strtoupper(trim($m[1]));
	}
	if ( ! $charset && preg_match('/<meta[^>]+charset\s*=\s*["\']?([^"\'\s;>]+)/i', $body, $m)) {
		$charset = strtoupper(trim($m[1]));
	}
	// 检测 HTML5 的 <meta charset="UTF-8">
	if ( ! $charset && preg_match('/<meta\s+charset\s*=\s*["\']?([^"\'\s;>]+)/i', $body, $m)) {
		$charset = strtoupper(trim($m[1]));
	}
	if ($charset && $charset !== 'UTF-8' && $charset !== 'UTF8') {
		$converted = @mb_convert_encoding($body, 'UTF-8', $charset);
		if ($converted !== false && $converted !== '') {
			$body = $converted;
		}
	}

	// 获取标题：优先 og:title，其次 <title>
	if (preg_match('/<meta[^>]+property\s*=\s*["\']og:title["\'][^>]+content\s*=\s*["\']([^"\']*)["\']/i', $body, $matches)) {
		$title = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
	} elseif (preg_match('/<meta[^>]+content\s*=\s*["\']([^"\']*)["\'][^>]+property\s*=\s*["\']og:title["\']/i', $body, $matches)) {
		$title = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
	} elseif (preg_match('/<title[^>]*>(.*?)<\/title>/is', $body, $matches)) {
		$title = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
		// 清理标题中的分隔符和网站名后缀（只清理尾部，不清理头部）
		$title = preg_replace('/\s*[\|\-–—_·]\s*.+$/', '', $title);
		// 去掉末尾的特殊字符
		$title = rtrim($title, ' ?！?…·_ -—–');
		$title = trim($title);
	}

	// 获取描述：依次尝试 meta description、og:description、twitter:description
	$desc_patterns = array(
		// meta name="description" content="..." (name在前)
		'/<meta[^>]+name\s*=\s*["\']description["\'][^>]+content\s*=\s*["\']([^"\']*)["\']/i',
		// meta content="..." name="description" (content在前)
		'/<meta[^>]+content\s*=\s*["\']([^"\']*)["\'][^>]+name\s*=\s*["\']description["\']/i',
		// og:description
		'/<meta[^>]+property\s*=\s*["\']og:description["\'][^>]+content\s*=\s*["\']([^"\']*)["\']/i',
		'/<meta[^>]+content\s*=\s*["\']([^"\']*)["\'][^>]+property\s*=\s*["\']og:description["\']/i',
		// twitter:description
		'/<meta[^>]+name\s*=\s*["\']twitter:description["\'][^>]+content\s*=\s*["\']([^"\']*)["\']/i',
		'/<meta[^>]+content\s*=\s*["\']([^"\']*)["\'][^>]+name\s*=\s*["\']twitter:description["\']/i',
		// itemprop description (schema.org)
		'/<meta[^>]+itemprop\s*=\s*["\']description["\'][^>]+content\s*=\s*["\']([^"\']*)["\']/i',
		'/<meta[^>]+content\s*=\s*["\']([^"\']*)["\'][^>]+itemprop\s*=\s*["\']description["\']/i',
	);

	foreach ($desc_patterns as $pattern) {
		if (preg_match($pattern, $body, $matches) && ! empty(trim($matches[1]))) {
			$description = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
			break;
		}
	}

	// 获取favicon
	$parsed_url = parse_url($url);
	$scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] : 'https';
	$host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
	$base_url = $scheme . '://' . $host;

	// 构建完整URL的辅助函数（处理相对路径）
	$resolve_url = function($rel_url) use ($scheme, $host, $base_url) {
		$rel_url = html_entity_decode($rel_url, ENT_QUOTES, 'UTF-8');
		$rel_url = trim($rel_url);
		if (empty($rel_url)) {
			return '';
		}
		// data: URI 直接返回空
		if (strpos($rel_url, 'data:') === 0) {
			return '';
		}
		// 协议相对 URL: //example.com/favicon.ico
		if (strpos($rel_url, '//') === 0) {
			return $scheme . ':' . $rel_url;
		}
		// 绝对 URL
		if (strpos($rel_url, 'http://') === 0 || strpos($rel_url, 'https://') === 0) {
			return $rel_url;
		}
		// 根路径相对: /favicon.ico
		if (strpos($rel_url, '/') === 0) {
			return $base_url . $rel_url;
		}
		// 相对路径: favicon.ico 或 images/favicon.ico
		return $base_url . '/' . $rel_url;
	};

	// 尝试从HTML中获取favicon（兼容多种格式和属性顺序）
	$icon_patterns = array(
		// rel="icon" 或 rel="shortcut icon"，href在前或后
		'/<link[^>]+rel\s*=\s*["\'](?:shortcut\s+)?icon["\'][^>]*>/i',
		'/<link[^>]+rel\s*=\s*["\']icon\s+shortcut["\'][^>]*>/i',
		// apple-touch-icon
		'/<link[^>]+rel\s*=\s*["\']apple-touch-icon(?:-precomposed)?["\'][^>]*>/i',
		// mask-icon (SVG)
		'/<link[^>]+rel\s*=\s*["\']mask-icon["\'][^>]*>/i',
		// fluid-icon
		'/<link[^>]+rel\s*=\s*["\']fluid-icon["\'][^>]*>/i',
	);

	// 从匹配到的 link 标签中提取 href
	foreach ($icon_patterns as $pattern) {
		if (preg_match_all($pattern, $body, $link_matches)) {
			foreach ($link_matches[0] as $link_tag) {
				// 从 link 标签中提取 href
				if (preg_match('/href\s*=\s*["\']([^"\']+)["\']/i', $link_tag, $href_match)) {
					$resolved = $resolve_url($href_match[1]);
					if ($resolved) {
						$icon_url = $resolved;
						break 2;
					}
				}
			}
		}
	}

	// 构建favicon候选列表（不进行服务器端验证，交给客户端onerror回退，避免超时）
	$favicon_candidates = array();

	// 如果从HTML中获取到了favicon，优先使用
	if ($icon_url) {
		$favicon_candidates[] = $icon_url;
	}

	// 备选：默认路径 /favicon.ico
	$favicon_candidates[] = $base_url . '/favicon.ico';

	// 备选：默认路径 /favicon.png
	$favicon_candidates[] = $base_url . '/favicon.png';

	// 备选：一文favicon API（国内可访问）
	$favicon_candidates[] = 'https://api.iowen.cn/favicon/' . urlencode($host) . '.png';

	// 备选：DuckDuckGo Icon API（部分地区可访问）
	$favicon_candidates[] = 'https://icons.duckduckgo.com/ip3/' . $host . '.ico';

	// 备选：Google Favicon API（国外使用，sz=64 高清）
	$favicon_candidates[] = 'https://www.google.com/s2/favicons?domain=' . urlencode($host) . '&sz=64';

	// icon_url 保留为HTML中找到的值（可能为空），客户端通过 favicon_candidates 回退

	// 如果仍然没有获取到描述，尝试从body中提取文本片段
	if (empty($description)) {
		// 移除script、style、noscript标签
		$clean_body = preg_replace('/<(script|style|noscript)[^>]*>.*?<\/\1>/is', '', $body);
		// 移除所有HTML标签
		$clean_body = strip_tags($clean_body);
		// 提取前200个字符作为描述
		$clean_body = trim(preg_replace('/\s+/', ' ', $clean_body));
		if (mb_strlen($clean_body, 'UTF-8') > 50) {
			$description = mb_substr($clean_body, 0, 200, 'UTF-8');
			if (mb_strlen($clean_body, 'UTF-8') > 200) {
				$description .= '...';
			}
		}
	}

	$result = array(
		'title'              => $title,
		'description'        => $description,
		'icon_url'           => $icon_url,
		'url'                => $url,
		'host'               => $host,
		'favicon_candidates' => $favicon_candidates,
	);

	// 缓存结果（10分钟），避免重复采集
	set_transient($cache_key, $result, 600);

	wp_send_json_success($result);
}
add_action('wp_ajax_navai_fetch_site_info', 'navai_ajax_fetch_site_info');

/**
 * 重复网址检测 AJAX
 *
 * 扫描所有 ai_tool 文章，找出 _website_url 重复的记录
 *
 * @return void
 */
function navai_ajax_check_duplicates() {
	check_ajax_referer('navai_nonce', 'nonce');

	if ( ! current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('权限不足', 'navai')));
	}

	// 获取所有已发布和待审核的网址
	$all_posts = get_posts(array(
		'post_type'      => 'ai_tool',
		'post_status'    => array('publish', 'pending', 'draft'),
		'posts_per_page' => -1,
		'meta_key'       => '_website_url',
		'fields'         => 'ids',
	));

	// 按 URL 分组
	$url_map = array();
	$normalized_map = array();

	foreach ($all_posts as $post_id) {
		$url = get_post_meta($post_id, '_website_url', true);
		if (empty($url)) {
			continue;
		}

		$normalized = navai_normalize_url($url);
		$key = $normalized;

		$post_obj = get_post($post_id);
		$has_desc     = ! empty(trim($post_obj->post_content));
		$has_icon     = ! empty(get_post_meta($post_id, '_site_icon_url', true));
		$has_tags     = ! empty(get_post_meta($post_id, '_site_tags', true));
		$terms        = get_the_terms($post_id, 'ai_category');
		$has_category = ($terms && ! is_wp_error($terms) && ! empty($terms));

		$completeness = 0;
		if ($has_desc) {
			$completeness++;
		}
		if ($has_icon) {
			$completeness++;
		}
		if ($has_category) {
			$completeness++;
		}
		if ($has_tags) {
			$completeness++;
		}

		if ( ! isset($normalized_map[$key])) {
			$normalized_map[$key] = array();
		}
		$normalized_map[$key][] = array(
			'id'           => $post_id,
			'url'          => $url,
			'title'        => get_the_title($post_id),
			'icon_url'     => get_post_meta($post_id, '_site_icon_url', true),
			'has_desc'     => $has_desc,
			'has_icon'     => $has_icon,
			'has_category' => $has_category,
			'has_tags'     => $has_tags,
			'completeness' => $completeness,
			'post_status'  => $post_obj->post_status,
			'post_date'    => $post_obj->post_date,
		);
	}

	// 找出重复项
	$duplicates = array();
	foreach ($normalized_map as $key => $items) {
		if (count($items) > 1) {
			$duplicates[] = array(
				'url'   => $items[0]['url'],
				'count' => count($items),
				'items' => $items,
			);
		}
	}

	$total_dup = count($duplicates);
	$total_dup_posts = 0;
	foreach ($duplicates as $dup) {
		$total_dup_posts += $dup['count'];
	}

	wp_send_json_success(array(
		'duplicates'      => $duplicates,
		'total_groups'    => $total_dup,
		'total_posts'     => $total_dup_posts,
		'total_scanned'   => count($all_posts),
	));
}
add_action('wp_ajax_navai_check_duplicates', 'navai_ajax_check_duplicates');

/**
 * 删除重复网址 AJAX
 *
 * @return void
 */
function navai_ajax_delete_duplicate() {
	check_ajax_referer('navai_nonce', 'nonce');

	if ( ! current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('权限不足', 'navai')));
	}

	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	$keep_id = isset($_POST['keep_id']) ? intval($_POST['keep_id']) : 0;

	if ( ! $post_id || $post_id === $keep_id) {
		wp_send_json_error(array('message' => __('无效的文章ID', 'navai')));
	}

	// 确认是 ai_tool 类型
	$post = get_post($post_id);
	if ( ! $post || $post->post_type !== 'ai_tool') {
		wp_send_json_error(array('message' => __('无效的网址文章', 'navai')));
	}

	// 移到回收站而非永久删除
	$result = wp_trash_post($post_id);

	if ($result) {
		wp_send_json_success(array('message' => sprintf(__('已删除重复网址「%s」', 'navai'), $post->post_title)));
	} else {
		wp_send_json_error(array('message' => __('删除失败', 'navai')));
	}
}
add_action('wp_ajax_navai_delete_duplicate', 'navai_ajax_delete_duplicate');

/**
 * 扫描文章中的外部图片 AJAX
 *
 * @return void
 */
function navai_ajax_scan_remote_images() {
	check_ajax_referer('navai_nonce', 'nonce');

	// 优先从前端传入的内容获取，其次从文章ID获取
	$content = '';
	if ( ! empty($_POST['content'])) {
		$content = wp_kses_post($_POST['content']);
	} else {
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		if ($post_id) {
			$post = get_post($post_id);
			if ($post) {
				$content = $post->post_content;
			}
		}
	}

	if (empty($content)) {
		wp_send_json_error(array('message' => __('内容为空', 'navai')));
	}

	$site_url = site_url();
	$home_url = home_url();
	$images = array();

	// 匹配所有 <img> 标签（含 src 和 alt），s修饰符使.匹配换行
	preg_match_all('/<img\s[^>]*?>/is', $content, $img_tags);

	foreach ($img_tags[0] as $tag) {
		if (preg_match('/src=["\']([^"\']+)["\']/i', $tag, $src_match)) {
			$url = trim($src_match[1]);
			if (empty($url)) {
				continue;
			}
			// 跳过本地图片和 data URI
			if (strpos($url, $site_url) === 0 || strpos($url, $home_url) === 0) {
				continue;
			}
			if (strpos($url, 'data:') === 0) {
				continue;
			}
			if (strpos($url, 'base64') !== false) {
				continue;
			}
			// 跳过空src或#号开头的占位符
			if ($url === '#' || $url === '') {
				continue;
			}
			$images[] = array(
				'url' => $url,
				'tag' => $tag,
			);
		}
	}

	wp_send_json_success(array(
		'images' => $images,
		'count'  => count($images),
		'message'  => count($images) > 0 ? sprintf(__('发现 %d 张外部图片', 'navai'), count($images)) : __('未发现外部图片', 'navai'),
	));
}
add_action('wp_ajax_navai_scan_remote_images', 'navai_ajax_scan_remote_images');

/**
 * 下载远程图片到媒体库 AJAX（带占位符替换）
 *
 * @return void
 */
function navai_ajax_download_remote_images() {
	check_ajax_referer('navai_nonce', 'nonce');

	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	$images  = isset($_POST['images']) ? $_POST['images'] : array();

	if (empty($images)) {
		wp_send_json_error(array('message' => __('没有需要下载的图片', 'navai')));
	}

	// 优先从前端传入的内容获取，其次从文章ID获取
	$content = '';
	if ( ! empty($_POST['content'])) {
		$content = wp_kses_post($_POST['content']);
	} elseif ($post_id) {
		$post = get_post($post_id);
		if ($post) {
			$content = $post->post_content;
		}
	}

	if (empty($content)) {
		wp_send_json_error(array('message' => __('内容为空', 'navai')));
	}

	$upload_dir = wp_upload_dir();
	$downloaded = 0;
	$failed     = 0;
	$results    = array();

	// 逐张下载图片，下载成功后直接替换内容中的外链URL为本地URL
	foreach ($images as $idx => $img) {
		$old_url = $img['url'];

		$response = wp_remote_get($old_url, array(
			'timeout'   => 15,
			'sslverify' => false,
		));

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			$failed++;
			$results[] = array('url' => $old_url, 'status' => 'failed', 'reason' => __('下载失败', 'navai'));
			continue;
		}

		$image_data = wp_remote_retrieve_body($response);
		if (empty($image_data)) {
			$failed++;
			$results[] = array('url' => $old_url, 'status' => 'failed', 'reason' => __('内容为空', 'navai'));
			continue;
		}

		// 获取文件扩展名
		$ext = pathinfo(wp_parse_url($old_url, PHP_URL_PATH), PATHINFO_EXTENSION);
		if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp'))) {
			$ext = 'jpg';
		}

		$filename = sanitize_file_name('remote-' . time() . '-' . ($idx + 1) . '.' . $ext);
		$file_path = $upload_dir['path'] . '/' . $filename;
		$saved = file_put_contents($file_path, $image_data);

		if ($saved === false) {
			$failed++;
			$results[] = array('url' => $old_url, 'status' => 'failed', 'reason' => __('保存失败', 'navai'));
			continue;
		}

		$attachment = array(
			'post_title'     => __('远程图片 -', 'navai') . ' ' . ($idx + 1),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => wp_check_filetype($filename)['type'],
		);

		$attach_id = wp_insert_attachment($attachment, $file_path, $post_id);

		if (is_wp_error($attach_id)) {
			$failed++;
			@unlink($file_path);
			$results[] = array('url' => $old_url, 'status' => 'failed', 'reason' => __('媒体库插入失败', 'navai'));
			continue;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
		wp_update_attachment_metadata($attach_id, $attach_data);

		$new_url = $upload_dir['url'] . '/' . $filename;

		// 直接在内容中替换外链URL为本地URL（全局替换，不依赖完整标签匹配）
		$content = str_replace($old_url, $new_url, $content);

		$downloaded++;
		$results[] = array('url' => $old_url, 'status' => 'success', 'local_url' => $new_url);
	}

	// 如果有文章ID，同时更新数据库
	if ($post_id) {
		wp_update_post(array(
			'ID'           => $post_id,
			'post_content' => $content,
		));
	}

	wp_send_json_success(array(
		'downloaded'  => $downloaded,
		'failed'      => $failed,
		'total'       => count($images),
		'results'     => $results,
		'new_content' => $content,
		'message'     => sprintf(__('采集完成：成功 %d 张，失败 %d 张，共 %d 张', 'navai'), $downloaded, $failed, count($images)),
	));
}
add_action('wp_ajax_navai_download_remote_images', 'navai_ajax_download_remote_images');

/**
 * 前台下载远程图片并替换内容 AJAX（无需文章ID，直接返回新内容）
 *
 * @return void
 */
function navai_ajax_download_remote_images_front() {
	check_ajax_referer('navai_nonce', 'nonce');

	if (!is_user_logged_in()) {
		wp_send_json_error(array('message' => __('请先登录', 'navai')));
	}

	$images  = isset($_POST['images']) ? $_POST['images'] : array();
	$content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

	if (empty($images)) {
		wp_send_json_error(array('message' => __('没有需要下载的图片', 'navai')));
	}

	$upload_dir = wp_upload_dir();
	$downloaded = 0;
	$failed     = 0;

	// 逐张下载并替换
	foreach ($images as $img) {
		$old_url = $img['url'];
		$old_tag = $img['tag'];

		$response = wp_remote_get($old_url, array(
			'timeout'   => 15,
			'sslverify' => false,
		));

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			$failed++;
			continue;
		}

		$image_data = wp_remote_retrieve_body($response);
		if (empty($image_data)) {
			$failed++;
			continue;
		}

		// 获取文件扩展名
		$ext = pathinfo(wp_parse_url($old_url, PHP_URL_PATH), PATHINFO_EXTENSION);
		if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp'))) {
			$ext = 'jpg';
		}

		$filename = sanitize_file_name('remote-' . time() . '-' . ($downloaded + $failed + 1) . '.' . $ext);
		$file_path = $upload_dir['path'] . '/' . $filename;
		$saved = file_put_contents($file_path, $image_data);

		if ($saved === false) {
			$failed++;
			continue;
		}

		$attachment = array(
			'post_title'     => __('远程图片 -', 'navai') . ' ' . ($downloaded + 1),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => wp_check_filetype($filename)['type'],
		);

		$attach_id = wp_insert_attachment($attachment, $file_path);

		if (is_wp_error($attach_id)) {
			$failed++;
			@unlink($file_path);
			continue;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
		wp_update_attachment_metadata($attach_id, $attach_data);

		$new_url = $upload_dir['url'] . '/' . $filename;

		// 替换内容中的图片URL
		$content = str_replace($old_url, $new_url, $content);
		$downloaded++;
	}

	wp_send_json_success(array(
		'downloaded'  => $downloaded,
		'failed'      => $failed,
		'total'       => count($images),
		'new_content' => $content,
		'message'     => sprintf(__('采集完成：成功 %d 张，失败 %d 张，共 %d 张', 'navai'), $downloaded, $failed, count($images)),
	));
}
add_action('wp_ajax_navai_download_remote_images_front', 'navai_ajax_download_remote_images_front');

/**
 * 获取网站截图（带本地缓存）
 *
 * @param string $url     目标网址
 * @param int    $width   截图宽度
 * @param int    $height  截图高度
 * @return string 截图URL（本地缓存URL或外部API URL）
 */
function navai_get_screenshot($url, $width = 456, $height = 300) {
	if (empty($url)) {
		return '';
	}

	// 确保URL有协议
	if (!preg_match('/^https?:\/\//', $url)) {
		$url = 'https://' . $url;
	}

	// 生成缓存文件名
	$url_hash = md5($url . '_' . $width . '_' . $height);
	$cache_dir = wp_upload_dir()['basedir'] . '/navai-screenshots';
	$cache_file = $cache_dir . '/' . $url_hash . '.jpg';
	$cache_url = wp_upload_dir()['baseurl'] . '/navai-screenshots/' . $url_hash . '.jpg';

	// 如果本地缓存存在且未过期（7天），直接返回
	if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 7 * DAY_IN_SECONDS) {
		return $cache_url;
	}

	// 确保缓存目录存在
	if (!file_exists($cache_dir)) {
		wp_mkdir_p($cache_dir);
		// 创建 .htaccess 保护目录
		$htaccess = $cache_dir . '/.htaccess';
		if (!file_exists($htaccess)) {
			file_put_contents($htaccess, "Options -Indexes\n<FilesMatch \"\\.(jpg|jpeg|png|gif)$\">\n    Allow from all\n</FilesMatch>\n");
		}
	}

	// 优先尝试 wkhtmltoimage（如果服务器已安装）
	$wkhtmltoimage = shell_exec('which wkhtmltoimage 2>/dev/null');
	if ( ! empty($wkhtmltoimage)) {
		$wkhtmltoimage = trim($wkhtmltoimage);
		$cmd = escapeshellcmd($wkhtmltoimage) . ' --width ' . intval($width) . ' --height ' . intval($height) . ' --quality 85 --format jpg ' . escapeshellarg($url) . ' ' . escapeshellarg($cache_file) . ' 2>&1';
		exec($cmd, $output, $return_code);
		if ($return_code === 0 && file_exists($cache_file) && filesize($cache_file) > 1000) {
			return $cache_url;
		}
	}

	// 备选1：尝试从外部API下载截图并缓存
	$api_urls = array(
		'https://image.thum.io/get/width/' . intval($width) . '/crop/' . intval($height) . '/' . urlencode($url),
		'https://urlscan.io/liveshot/?width=' . intval($width) . '&height=' . intval($height) . '&url=' . urlencode($url),
	);

	foreach ($api_urls as $api_url) {
		$response = wp_remote_get($api_url, array(
			'timeout'    => 30,
			'sslverify'  => false,
		));

		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$image_data = wp_remote_retrieve_body($response);
			if ( ! empty($image_data) && strlen($image_data) > 1000) {
				// 验证是否为图片
				$is_image = false;
				if (function_exists('finfo_open') && function_exists('finfo_buffer')) {
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$mime_type = finfo_buffer($finfo, $image_data);
					finfo_close($finfo);
					$is_image = (strpos($mime_type, 'image/') === 0);
				} else {
					// 降级方案：检查图片文件头
					$is_image = (strpos($image_data, '\xFF\xD8') === 0) || // JPEG
								(strpos($image_data, '\x89PNG') === 0) || // PNG
								(strpos($image_data, 'GIF') === 0);       // GIF
				}

				if ($is_image) {
					file_put_contents($cache_file, $image_data);
					return $cache_url;
				}
			}
		}
	}

	// 如果所有方法都失败，返回占位图URL
	return get_template_directory_uri() . '/assets/images/placeholder-screenshot.jpg';
}

/**
 * 上传远程图片到媒体库
 *
 * @param string $image_url 图片URL
 * @param int    $post_id   文章ID
 * @return int|WP_Error 附件ID或错误
 */
function navai_upload_remote_image($image_url, $post_id = 0) {
	if (empty($image_url)) {
		return new WP_Error('empty_url', __('图片URL为空', 'navai'));
	}

	// 安全校验：禁止内网地址
	if (!navai_is_valid_external_url($image_url)) {
		return new WP_Error('invalid_url', __('不允许访问该地址', 'navai'));
	}

	// 只允许图片协议
	$parsed = parse_url($image_url);
	if ( ! isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), array('http', 'https'), true)) {
		return new WP_Error('invalid_scheme', __('只允许HTTP/HTTPS协议', 'navai'));
	}

	// 下载图片
	$response = wp_remote_get($image_url, array(
		'timeout'    => 10,
		'sslverify'  => true,
		'redirection' => 0,
	));

	if (is_wp_error($response)) {
		return $response;
	}

	$image_data = wp_remote_retrieve_body($response);
	$status_code = wp_remote_retrieve_response_code($response);

	if ($status_code !== 200) {
		return new WP_Error('http_error', __('获取图片失败，HTTP状态码：', 'navai') . $status_code);
	}

	if (empty($image_data)) {
		return new WP_Error('empty_image', __('无法获取图片数据', 'navai'));
	}

	// 严格校验文件类型（通过文件内容，而非扩展名）
	$mime_type = '';
	if (class_exists('finfo')) {
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime_type = $finfo->buffer($image_data);
	} else {
		// 降级方案：通过文件头判断
		if (strpos($image_data, '\xFF\xD8') === 0) {
			$mime_type = 'image/jpeg';
		} elseif (strpos($image_data, '\x89PNG') === 0) {
			$mime_type = 'image/png';
		} elseif (strpos($image_data, 'GIF') === 0) {
			$mime_type = 'image/gif';
		} elseif (strpos($image_data, '<svg') !== false) {
			$mime_type = 'image/svg+xml';
		} elseif (strpos($image_data, 'RIFF') === 0 && strpos($image_data, 'WEBP') !== false) {
			$mime_type = 'image/webp';
		}
	}

	$allowed_mimes = array(
		'image/jpeg' => 'jpg',
		'image/png'  => 'png',
		'image/gif'  => 'gif',
		'image/x-icon' => 'ico',
		'image/svg+xml' => 'svg',
		'image/webp' => 'webp',
	);

	if ( ! isset($allowed_mimes[$mime_type])) {
		return new WP_Error('invalid_type', __('不支持的图片类型：', 'navai') . $mime_type);
	}

	// 获取文件名
	$filename = basename(parse_url($image_url, PHP_URL_PATH));
	$ext = $allowed_mimes[$mime_type];
	if (empty($filename) || !preg_match('/\.\w+$/', $filename)) {
		$filename = 'site-icon.' . $ext;
	}

	// 上传目录
	$upload_dir = wp_upload_dir();
	$unique_filename = wp_unique_filename($upload_dir['path'], $filename);
	$file_path = $upload_dir['path'] . '/' . $unique_filename;

	// 保存文件
	if (!file_put_contents($file_path, $image_data)) {
		return new WP_Error('save_error', __('保存图片失败', 'navai'));
	}

	// 准备附件数据
	$wp_filetype = wp_check_filetype($filename, null);
	$attachment = array(
		'post_mime_type' => $mime_type,
		'post_title'     => sanitize_file_name($filename),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	// 插入附件
	$attach_id = wp_insert_attachment($attachment, $file_path, $post_id);

	if (is_wp_error($attach_id)) {
		return $attach_id;
	}

	// 生成缩略图
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
	wp_update_attachment_metadata($attach_id, $attach_data);

	return $attach_id;
}

/**
 * ============================================================================
 * 10. 短代码
 * ============================================================================
 */

/**
 * 热门AI工具短代码
 *
 * @param array $atts 短代码属性
 * @return string HTML输出
 */
function navai_hot_ai_shortcode($atts) {
	$atts = shortcode_atts(array(
		'count' => 8,
	), $atts);

	$args = array(
		'post_type'      => 'ai_tool',
		'posts_per_page' => intval($atts['count']),
		'meta_key'       => '_is_hot',
		'meta_value'     => '1',
	);

	$query = new WP_Query($args);

	ob_start();

	if ($query->have_posts()) {
		echo '<div class="card-grid">';
		while ($query->have_posts()) {
			$query->the_post();
			get_template_part('template-parts/content', 'ai-card');
		}
		echo '</div>';
		wp_reset_postdata();
	}

	return ob_get_clean();
}
add_shortcode('hot_ai', 'navai_hot_ai_shortcode');

/**
 * 按分类显示AI工具短代码
 *
 * @param array $atts 短代码属性
 * @return string HTML输出
 */
function navai_category_ai_shortcode($atts) {
	$atts = shortcode_atts(array(
		'category' => '',
		'count'    => 8,
	), $atts);

	if (empty($atts['category'])) {
		return '';
	}

	$args = array(
		'post_type'      => 'ai_tool',
		'posts_per_page' => intval($atts['count']),
		'tax_query'      => array(
			array(
				'taxonomy' => 'ai_category',
				'field'    => 'slug',
				'terms'    => sanitize_text_field($atts['category']),
			),
		),
	);

	$query = new WP_Query($args);

	ob_start();

	if ($query->have_posts()) {
		echo '<div class="card-grid">';
		while ($query->have_posts()) {
			$query->the_post();
			get_template_part('template-parts/content', 'ai-card');
		}
		echo '</div>';
		wp_reset_postdata();
	}

	return ob_get_clean();
}
add_shortcode('category_ai', 'navai_category_ai_shortcode');

/**
 * ============================================================================
 * 11. 主题激活钩子
 * ============================================================================
 */

/**
 * 主题激活时创建默认数据
 *
 * @return void
 */
function navai_theme_activation() {
	// 创建默认页面（仅当页面不存在时）
	$pages = array(
		__('首页', 'navai')     => array('slug' => 'home', 'content' => ''),
		__('在线工具', 'navai') => array('slug' => 'online-tools', 'content' => ''),
		__('今日热榜', 'navai') => array('slug' => 'hot-list', 'content' => ''),
		__('AI排行榜', 'navai') => array('slug' => 'ai-ranking', 'content' => ''),
	);

	foreach ($pages as $title => $data) {
		$page = get_page_by_path($data['slug']);
		if (!$page) {
			wp_insert_post(array(
				'post_title'   => $title,
				'post_name'    => $data['slug'],
				'post_content' => $data['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			));
		}
	}

	// 仅当 ai_category 分类法中完全没有任何分类时，才创建默认分类
	if (taxonomy_exists('ai_category')) {
		$existing_terms = get_terms(array(
			'taxonomy'   => 'ai_category',
			'hide_empty' => false,
			'fields'     => 'ids',
			'number'     => 1,
		));

		// 如果已有分类数据，跳过创建默认分类，保护用户现有数据
		if (empty($existing_terms) && !is_wp_error($existing_terms)) {

			$categories = array(
				__('大热门AI', 'navai'),
				__('图像AI', 'navai'),
				__('视频AI', 'navai'),
				__('写作AI', 'navai'),
				__('办公AI', 'navai'),
				__('对话AI', 'navai'),
				__('编程AI', 'navai'),
				__('设计AI', 'navai'),
				__('音频AI', 'navai'),
				__('搜索AI', 'navai'),
				__('翻译AI', 'navai'),
				__('学习AI', 'navai'),
				__('数据分析AI', 'navai'),
				__('营销AI', 'navai'),
				__('生活AI', 'navai'),
				__('游戏AI', 'navai'),
				__('健康AI', 'navai'),
				__('金融AI', 'navai'),
				__('法律AI', 'navai'),
				__('电商AI', 'navai'),
				__('社交AI', 'navai'),
				__('新闻AI', 'navai'),
				__('3D建模AI', 'navai'),
				__('PPT演示AI', 'navai'),
				__('思维导图AI', 'navai'),
				__('笔记AI', 'navai'),
				__('阅读AI', 'navai'),
				__('邮件AI', 'navai'),
				__('天气AI', 'navai'),
				__('旅行AI', 'navai'),
				__('美食AI', 'navai'),
				__('健身AI', 'navai'),
				__('宠物AI', 'navai'),
				__('儿童AI', 'navai'),
				__('星座AI', 'navai'),
				__('简历AI', 'navai'),
				__('论文AI', 'navai'),
				__('总结AI', 'navai'),
				__('抠图AI', 'navai'),
				__('换脸AI', 'navai'),
				__('变声AI', 'navai'),
				__('字幕AI', 'navai'),
				__('配音AI', 'navai'),
				__('修图AI', 'navai'),
				__('压缩转换AI', 'navai'),
				__('下载AI', 'navai'),
				__('检测查重AI', 'navai'),
				__('修复增强AI', 'navai'),
				__('生成AI', 'navai'),
				__('预测AI', 'navai'),
				__('推荐AI', 'navai'),
				__('排行榜AI', 'navai'),
				__('新出AI', 'navai'),
				__('大厂AI', 'navai'),
				__('开源AI', 'navai'),
				__('国产AI', 'navai'),
				__('国外AI', 'navai'),
				__('综合AI', 'navai'),
			);

			foreach ($categories as $cat_name) {
				if (!term_exists($cat_name, 'ai_category')) {
					wp_insert_term($cat_name, 'ai_category');
				}
			}
		}
	}

	// 不修改首页设置，保持 WordPress 默认（显示最新文章）

	// 刷新重写规则
	flush_rewrite_rules();
}

/**
 * 主题激活钩子处理
 *
 * @return void
 */
function navai_activate_theme() {
	// 使用 transient 确保只执行一次
	if (get_transient('navai_activated')) {
		return;
	}
	set_transient('navai_activated', true, 30);
	add_action('admin_init', 'navai_theme_activation');
}
add_action('after_switch_theme', 'navai_activate_theme');

/**
 * ============================================================================
 * 12. 批量添加网址页面
 * ============================================================================
 */

/**
 * 批量添加网址页面
 *
 * @return void
 */
function navai_batch_add_page() {
	if (!current_user_can('manage_options')) {
		wp_die(__('您没有权限访问此页面', 'navai'));
	}

	// 获取所有分类
	$categories = get_terms(array(
		'taxonomy'   => 'ai_category',
		'hide_empty' => false,
	));

	// 处理表单提交
	$message = '';
	if (isset($_POST['navai_batch_submit']) && check_admin_referer('navai_batch_add_nonce')) {
		$urls    = isset($_POST['batch_urls']) ? array_map('sanitize_text_field', $_POST['batch_urls']) : array();
		$titles  = isset($_POST['batch_titles']) ? array_map('sanitize_text_field', $_POST['batch_titles']) : array();
		$descs   = isset($_POST['batch_descriptions']) ? array_map('sanitize_textarea_field', $_POST['batch_descriptions']) : array();
		$cats    = isset($_POST['batch_categories']) ? array_map('intval', $_POST['batch_categories']) : array();
		$icons   = isset($_POST['batch_icons']) ? array_map('esc_url_raw', $_POST['batch_icons']) : array();

		$success_count = 0;
		$error_count   = 0;
		$dup_urls      = array();

		// 先检测批次内部重复
		$seen_urls = array();
		$batch_dups = array();

		foreach ($urls as $i => $url) {
			$url = trim($url);
			if (empty($url)) {
				continue;
			}

			$normalized = navai_normalize_url($url);

			// 检查批次内部重复
			if (in_array($normalized, $seen_urls, true)) {
				$batch_dups[] = $url;
				$error_count++;
				continue;
			}
			$seen_urls[] = $normalized;

			// 检查数据库中是否已存在
			if (navai_url_exists($url)) {
				$dup_urls[] = $url;
				$error_count++;
				continue;
			}

			$title = !empty($titles[$i]) ? $titles[$i] : $url;
			$desc  = !empty($descs[$i]) ? $descs[$i] : '';
			$cat   = !empty($cats[$i]) ? array($cats[$i]) : array();
			$icon  = !empty($icons[$i]) ? $icons[$i] : '';

			$post_id = wp_insert_post(array(
				'post_title'   => $title,
				'post_content' => $desc,
				'post_status'  => 'publish',
				'post_type'    => 'ai_tool',
			));

			if (is_wp_error($post_id)) {
				$error_count++;
				continue;
			}

			// 保存元数据
			update_post_meta($post_id, '_website_url', esc_url_raw($url));
			if ($icon) {
				update_post_meta($post_id, '_site_icon_url', esc_url_raw($icon));
			}

			// 设置分类
			if ( ! empty($cat)) {
				wp_set_post_terms($post_id, $cat, 'ai_category');
			}

			$success_count++;
		}

		if ($success_count > 0) {
			$error_detail = '';
			if ( ! empty($dup_urls)) {
				$error_detail .= '<br>' . sprintf(__('重复网址 %d 个：%s', 'navai'), count($dup_urls), esc_html(implode('、', array_slice($dup_urls, 0, 5)) . (count($dup_urls) > 5 ? '...' : '')));
			}
			if ( ! empty($batch_dups)) {
				$error_detail .= '<br>' . sprintf(__('批次内重复 %d 个：%s', 'navai'), count($batch_dups), esc_html(implode('、', array_slice($batch_dups, 0, 5)) . (count($batch_dups) > 5 ? '...' : '')));
			}
			$message = '<div class="notice notice-success"><p>' . sprintf(__('成功添加 %d 个网址', 'navai'), $success_count) . ($error_count > 0 ? '，' . sprintf(__('%d 个失败', 'navai'), $error_count) : '') . $error_detail . '</p></div>';
		} else {
			$error_detail = '';
			if ( ! empty($dup_urls)) {
				$error_detail = sprintf(__('重复网址：%s', 'navai'), esc_html(implode('、', $dup_urls)));
			} elseif ( ! empty($batch_dups)) {
				$error_detail = sprintf(__('批次内重复：%s', 'navai'), esc_html(implode('、', $batch_dups)));
			} else {
				$error_detail = __('请检查网址是否已存在', 'navai');
			}
			$message = '<div class="notice notice-error"><p>' . __('添加失败，', 'navai') . esc_html($error_detail) . '</p></div>';
		}
	}
	// 获取层级分类列表
	$category_options = '';
	$top_cats = get_terms(array(
		'taxonomy'   => 'ai_category',
		'hide_empty' => false,
		'parent'     => 0,
	));
	foreach ($top_cats as $top_cat) {
		$category_options .= '<option value="' . esc_attr($top_cat->term_id) . '">' . esc_html($top_cat->name) . '</option>';
		$sub_cats = get_terms(array(
			'taxonomy'   => 'ai_category',
			'hide_empty' => false,
			'parent'     => $top_cat->term_id,
		));
		foreach ($sub_cats as $sub_cat) {
			$category_options .= '<option value="' . esc_attr($sub_cat->term_id) . '">&nbsp;&nbsp;└ ' . esc_html($sub_cat->name) . '</option>';
		}
	}
	?>
	<div class="wrap">
		<h1><?php _e('批量添加网址', 'navai'); ?></h1>
		<?php echo $message; ?>
		<p class="description"><?php esc_html_e('输入多个网址，每个网址可单独设置分类。点击"采集信息"可自动获取网站图标和描述。', 'navai'); ?></p>

		<p>
			<button type="button" class="button button-secondary" id="navai-fetch-all">
				<span class="dashicons dashicons-download" style="font-size:16px;width:16px;height:16px;line-height:1;"></span>
				<?php esc_html_e('一键采集全部', 'navai'); ?>
			</button>
			<span class="description" style="margin-left:8px;"><?php esc_html_e('自动采集所有已输入网址的信息', 'navai'); ?></span>
		</p>

		<form method="post" id="navai-batch-form">
			<?php wp_nonce_field('navai_batch_add_nonce'); ?>
			<table class="wp-list-table widefat fixed striped" id="navai-batch-table">
				<thead>
					<tr>
						<th style="width:30px;">#</th>
						<th style="width:22%;"><?php esc_html_e('网址 URL', 'navai'); ?> <span class="required">*</span></th>
						<th style="width:14%;"><?php esc_html_e('网站名称', 'navai'); ?></th>
						<th style="width:18%;"><?php esc_html_e('描述', 'navai'); ?></th>
						<th style="width:14%;"><?php esc_html_e('分类', 'navai'); ?></th>
						<th style="width:40px;"><?php esc_html_e('图标', 'navai'); ?></th>
						<th style="width:90px;"><?php esc_html_e('操作', 'navai'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="navai-batch-row" data-index="0">
						<td class="row-num">1</td>
						<td>
							<div class="navai-url-cell">
								<input type="url" name="batch_urls[]" class="regular-text batch-url" placeholder="https://example.com" required>
								<button type="button" class="button navai-fetch-btn">
									<span class="dashicons dashicons-download" style="font-size:14px;width:14px;height:14px;line-height:1;"></span>
									<?php esc_html_e('采集', 'navai'); ?>
								</button>
							</div>
						</td>
						<td><input type="text" name="batch_titles[]" class="regular-text batch-title" placeholder="<?php esc_attr_e('自动获取', 'navai'); ?>"></td>
						<td><textarea name="batch_descriptions[]" class="regular-text batch-desc" rows="2" placeholder="<?php esc_attr_e('自动获取', 'navai'); ?>"></textarea></td>
						<td>
							<select name="batch_categories[]" class="batch-category">
								<option value=""><?php esc_html_e('选择分类', 'navai'); ?></option>
								<?php echo $category_options; ?>
							</select>
						</td>
						<td>
							<input type="hidden" name="batch_icons[]" class="batch-icon">
							<div class="batch-icon-preview" style="width:32px;height:32px;border:1px solid #ddd;border-radius:4px;display:flex;align-items:center;justify-content:center;background:#f6f7f7;flex-shrink:0;">
								<span class="dashicons dashicons-globe" style="color:#8c8f94;font-size:18px;"></span>
							</div>
						</td>
						<td>
							<button type="button" class="button navai-remove-row"><?php esc_html_e('删除', 'navai'); ?></button>
						</td>
					</tr>
				</tbody>
			</table>

			<p>
				<button type="button" class="button" id="navai-add-row" style="display:inline-flex;align-items:center;gap:4px;">
					<span class="dashicons dashicons-plus-alt" style="font-size:16px;width:16px;height:16px;line-height:1;"></span>
					<?php esc_html_e('添加一行', 'navai'); ?>
				</button>
			</p>

			<p class="submit">
				<input type="submit" name="navai_batch_submit" class="button button-primary" value="<?php esc_attr_e('批量添加网址', 'navai'); ?>">
			</p>
		</form>
	</div>

	<style>
		#navai-batch-table input.regular-text,
		#navai-batch-table textarea.regular-text,
		#navai-batch-table select {
			width: 100%;
		}
		#navai-batch-table td {
			vertical-align: top;
		}
		.navai-url-cell {
			display: flex;
			align-items: center;
			gap: 6px;
			flex-wrap: nowrap;
		}
		.navai-url-cell .batch-url {
			flex: 1;
			min-width: 0;
		}
		.navai-fetch-btn {
			display: inline-flex !important;
			align-items: center;
			gap: 4px;
			font-size: 12px;
			padding: 2px 8px;
			white-space: nowrap;
			flex-shrink: 0;
		}
		.navai-fetch-btn .dashicons {
			font-size: 14px !important;
			width: 14px !important;
			height: 14px !important;
			line-height: 1 !important;
		}
		.navai-batch-row.fetching .navai-fetch-btn {
			opacity: 0.6;
			pointer-events: none;
		}
		#navai-fetch-all {
			display: inline-flex;
			align-items: center;
			gap: 4px;
		}
		#navai-fetch-all .dashicons {
			font-size: 16px;
			width: 16px;
			height: 16px;
			line-height: 1;
		}
	</style>

	<script>
	(function($) {
		'use strict';

		var categoryOptions = <?php echo wp_json_encode($category_options); ?>;
		var navaiBatchI18n = {
			fetch: '<?php echo esc_js(__('采集', 'navai')); ?>',
			autoFetch: '<?php echo esc_js(__('自动获取', 'navai')); ?>',
			selectCategory: '<?php echo esc_js(__('选择分类', 'navai')); ?>',
			deleteRow: '<?php echo esc_js(__('删除', 'navai')); ?>',
			keepOneRow: '<?php echo esc_js(__('至少保留一行', 'navai')); ?>',
			noUrl: '<?php echo esc_js(__('无网址', 'navai')); ?>',
			fetching: '<?php echo esc_js(__('采集中...', 'navai')); ?>',
			enterUrl: '<?php echo esc_js(__('请先输入网址', 'navai')); ?>',
			fetchFailed: '<?php echo esc_js(__('采集失败', 'navai')); ?>',
			fetchSuccess: '<?php echo esc_js(__('采集成功', 'navai')); ?>',
			partialSuccess: '<?php echo esc_js(__('部分采集成功', 'navai')); ?>',
			noDataFetched: '<?php echo esc_js(__('未采集到有效信息', 'navai')); ?>',
			gotTitle: '<?php echo esc_js(__('网站名称', 'navai')); ?>',
			gotDesc: '<?php echo esc_js(__('网站描述', 'navai')); ?>',
			gotIcon: '<?php echo esc_js(__('网站图标', 'navai')); ?>',
			enterAtLeastOne: '<?php echo esc_js(__('请先输入至少一个网址', 'navai')); ?>',
			fetchAllComplete: '<?php echo esc_js(__('全部采集完成', 'navai')); ?>',
			fetchProgress: '<?php echo esc_js(__('采集中', 'navai')); ?>',
			timeout: '<?php echo esc_js(__('请求超时', 'navai')); ?>',
			unknownError: '<?php echo esc_js(__('未知错误', 'navai')); ?>',
			fetchAllSummary: '<?php echo esc_js(__('采集完成：成功 {success}，失败 {fail}', 'navai')); ?>'
		};
		var rowTemplate = '<tr class="navai-batch-row" data-index="0">' +
			'<td class="row-num">1</td>' +
			'<td>' +
				'<div class="navai-url-cell">' +
					'<input type="url" name="batch_urls[]" class="regular-text batch-url" placeholder="https://example.com" required>' +
					'<button type="button" class="button navai-fetch-btn">' +
						'<span class="dashicons dashicons-download" style="font-size:14px;width:14px;height:14px;line-height:1;"></span>' +
						navaiBatchI18n.fetch +
					'</button>' +
				'</div>' +
			'</td>' +
			'<td><input type="text" name="batch_titles[]" class="regular-text batch-title" placeholder="' + navaiBatchI18n.autoFetch + '"></td>' +
			'<td><textarea name="batch_descriptions[]" class="regular-text batch-desc" rows="2" placeholder="' + navaiBatchI18n.autoFetch + '"></textarea></td>' +
			'<td><select name="batch_categories[]" class="batch-category"><option value="">' + navaiBatchI18n.selectCategory + '</option>' + categoryOptions + '</select></td>' +
			'<td>' +
				'<input type="hidden" name="batch_icons[]" class="batch-icon">' +
				'<div class="batch-icon-preview" style="width:32px;height:32px;border:1px solid #ddd;border-radius:4px;display:flex;align-items:center;justify-content:center;background:#f6f7f7;flex-shrink:0;">' +
					'<span class="dashicons dashicons-globe" style="color:#8c8f94;font-size:18px;"></span>' +
				'</div>' +
			'</td>' +
			'<td><button type="button" class="button navai-remove-row">' + navaiBatchI18n.deleteRow + '</button></td>' +
		'</tr>';
		var rowCount = 1;

		// 添加行
		$('#navai-add-row').on('click', function() {
			var $newRow = $(rowTemplate);
			rowCount++;
			$newRow.attr('data-index', rowCount - 1);
			$newRow.find('.row-num').text(rowCount);
			$newRow.find('input[type=url], input[type=text], textarea').val('');
			$newRow.find('.batch-icon-preview').html('<span class="dashicons dashicons-globe" style="color:#8c8f94;font-size:18px;"></span>');
			$('#navai-batch-table tbody').append($newRow);
		});

		// 删除行
		$(document).on('click', '.navai-remove-row', function() {
			var $rows = $('.navai-batch-row');
			if ($rows.length <= 1) {
				navaiToast.warning(navaiBatchI18n.keepOneRow);
				return;
			}
			$(this).closest('.navai-batch-row').remove();
			$('.navai-batch-row').each(function(i) {
				$(this).attr('data-index', i);
				$(this).find('.row-num').text(i + 1);
			});
			rowCount = $('.navai-batch-row').length;
		});

		// 客户端favicon回退加载：依次尝试候选URL，成功后设置隐藏字段
		function loadBatchFavicon($row, candidates) {
			var $iconPreview = $row.find('.batch-icon-preview');
			var $iconHidden = $row.find('.batch-icon');
			$iconHidden.val(''); // 先清空，加载成功后再设置

			if (!candidates || candidates.length === 0) {
				$iconPreview.html('<span class="dashicons dashicons-globe" style="color:#8c8f94;font-size:18px;"></span>');
				return false;
			}

			var idx = 0;
			function tryNext() {
				if (idx >= candidates.length) {
					// 全部失败，显示默认图标
					$iconPreview.html('<span class="dashicons dashicons-globe" style="color:#8c8f94;font-size:18px;"></span>');
					return false;
				}
				var src = candidates[idx];
				idx++;
				var $img = $('<img>').attr('src', src).css({width:'100%',height:'100%',objectFit:'cover',borderRadius:'4px'});
				$img.on('error', function() {
					tryNext();
				});
				$img.on('load', function() {
					// 加载成功，设置隐藏字段值
					$iconHidden.val(src);
				});
				$iconPreview.html('').append($img);
			}
			tryNext();
		}

		// 采集单行
		function fetchRow($row, $btn) {
			var url = $row.find('.batch-url').val().trim();
			var deferred = $.Deferred();

			if (!url) {
				deferred.reject(navaiBatchI18n.noUrl);
				return deferred.promise();
			}

			$row.addClass('fetching');
			var originalHtml = $btn ? $btn.html() : '';
			if ($btn) {
				$btn.html('<span class="dashicons dashicons-update-alt spinning" style="font-size:14px;width:14px;height:14px;line-height:1;"></span> ' + navaiBatchI18n.fetching);
			}

			$.ajax({
				url: navaiAjax.ajaxurl,
				type: 'POST',
				timeout: 60000,
				data: {
					action: 'navai_fetch_site_info',
					nonce: navaiAjax.nonce,
					url: url
				}
			}).done(function(response) {
				if (response.success) {
					var data = response.data;
					var gotFields = [];
					if (data.title && !$row.find('.batch-title').val()) {
						$row.find('.batch-title').val(data.title);
						gotFields.push(navaiBatchI18n.gotTitle);
					}
					if (data.description && !$row.find('.batch-desc').val()) {
						$row.find('.batch-desc').val(data.description);
						gotFields.push(navaiBatchI18n.gotDesc);
					}

					// favicon处理：使用服务器返回的候选列表进行客户端回退加载
					var faviconCandidates = data.favicon_candidates || [];
					if (faviconCandidates.length > 0) {
						loadBatchFavicon($row, faviconCandidates);
						gotFields.push(navaiBatchI18n.gotIcon);
					}

					// 单行采集时显示Toast（批量采集时不逐行弹Toast）
					if ($btn) {
						if (gotFields.length === 3) {
							navaiToast.success(navaiBatchI18n.fetchSuccess, gotFields.join('、'));
						} else if (gotFields.length > 0) {
							navaiToast.warning(navaiBatchI18n.partialSuccess, gotFields.join('、'), 7000);
						} else {
							navaiToast.warning(navaiBatchI18n.noDataFetched, '', 7000);
						}
					}
					$row.data('fetchResult', gotFields.length);
					deferred.resolve(gotFields.length);
				} else {
					var errMsg = (response.data && response.data.message) ? response.data.message : navaiBatchI18n.unknownError;
					$row.data('fetchResult', -1);
					$row.data('fetchError', errMsg);
					deferred.reject(errMsg);
				}
			}).fail(function(jqXHR, textStatus) {
				var errMsg = (textStatus === 'timeout') ? navaiBatchI18n.timeout : navaiBatchI18n.unknownError;
				// 尝试从响应中提取错误信息
				try {
					var resp = JSON.parse(jqXHR.responseText);
					if (resp && resp.data && resp.data.message) {
						errMsg = resp.data.message;
					}
				} catch(e) {}
				$row.data('fetchResult', -1);
				$row.data('fetchError', errMsg);
				deferred.reject(errMsg);
			}).always(function() {
				$row.removeClass('fetching');
				if ($btn) {
					$btn.html(originalHtml);
				}
			});

			return deferred.promise();
		}

		$(document).on('click', '.navai-fetch-btn', function() {
			var $btn = $(this);
			var $row = $btn.closest('.navai-batch-row');
			var url = $row.find('.batch-url').val().trim();

			if (!url) {
				navaiToast.warning(navaiBatchI18n.enterUrl);
				$row.find('.batch-url').focus();
				return;
			}

			fetchRow($row, $btn).fail(function(msg) {
				if (msg !== navaiBatchI18n.noUrl) {
					navaiToast.error(navaiBatchI18n.fetchFailed, msg);
				}
			});
		});

		// 一键采集全部
		$('#navai-fetch-all').on('click', function() {
			var $rows = $('.navai-batch-row');
			var validRows = [];
			var fetchBtns = [];

			$rows.each(function() {
				var $row = $(this);
				var url = $row.find('.batch-url').val().trim();
				if (url) {
					validRows.push($row);
					fetchBtns.push($row.find('.navai-fetch-btn'));
				}
			});

			if (validRows.length === 0) {
				navaiToast.warning(navaiBatchI18n.enterAtLeastOne);
				return;
			}

			var $btn = $(this);
			var originalHtml = $btn.html();
			var totalRows = validRows.length;
			var completedCount = 0;
			$btn.prop('disabled', true);

			function updateProgress() {
				$btn.html('<span class="dashicons dashicons-update-alt spinning" style="font-size:16px;width:16px;height:16px;line-height:1;"></span> ' +
					navaiBatchI18n.fetchProgress + ' (' + completedCount + '/' + totalRows + ')');
			}
			updateProgress();

			var index = 0;
			function processNext() {
				if (index >= validRows.length) {
					$btn.prop('disabled', false);
					$btn.html(originalHtml);
					// 汇总采集结果
					var successCount = 0;
					var failCount = 0;
					var partialCount = 0;
					validRows.forEach(function($r) {
						var result = $r.data('fetchResult');
						if (result === 3) {
							successCount++;
						} else if (result > 0) {
							partialCount++;
						} else {
							failCount++;
						}
					});
					var summary = navaiBatchI18n.fetchAllSummary.replace('{success}', successCount).replace('{fail}', failCount);
					var body = '';
					if (partialCount > 0) {
						body += '<div>' + navaiBatchI18n.partialSuccess + ': ' + partialCount + '</div>';
					}
					body += '<div style="margin-top:4px;">' + summary + '</div>';
					if (failCount > 0) {
						navaiToast.warning(navaiBatchI18n.fetchAllComplete, body, 8000);
					} else {
						navaiToast.success(navaiBatchI18n.fetchAllComplete, body);
					}
					return;
				}

				var currentBtn = fetchBtns[index];
				var currentRow = validRows[index];

				// 更新当前行的采集按钮状态
				if (currentBtn) {
					var btnOriginal = currentBtn.html();
					currentBtn.html('<span class="dashicons dashicons-update-alt spinning" style="font-size:14px;width:14px;height:14px;line-height:1;"></span> ' + navaiBatchI18n.fetching);
					currentBtn.prop('disabled', true);
				}

				fetchRow(currentRow, null)
					.always(function() {
						completedCount++;
						updateProgress();
						// 恢复当前行按钮
						if (currentBtn) {
							currentBtn.prop('disabled', false);
							currentBtn.html('<span class="dashicons dashicons-download" style="font-size:14px;width:14px;height:14px;line-height:1;"></span>' + navaiBatchI18n.fetch);
						}
						index++;
						setTimeout(processNext, 300);
					});
			}
			processNext();
		});
	})(jQuery);
	</script>
	<?php
}

/**
 * ============================================================================
 * 13. 自动采集功能 - 后台JS
 * ============================================================================
 */

/**
 * 添加后台采集脚本
 *
 * @param string $hook 当前页面hook
 * @return void
 */
function navai_admin_scripts($hook) {
	// 批量添加页面也需要加载脚本
	$is_batch_page = (isset($_GET['post_type']) && $_GET['post_type'] === 'ai_tool' && isset($_GET['page']) && $_GET['page'] === 'navai-batch-add');

	if ('post.php' !== $hook && 'post-new.php' !== $hook && !$is_batch_page) {
		return;
	}

	// 获取当前文章类型（兼容经典编辑器）
	$current_post_type = '';
	if (function_exists('get_current_screen')) {
		$screen = get_current_screen();
		if ($screen) {
			$current_post_type = $screen->post_type;
		}
	}
	// 备用：从URL参数获取
	if (empty($current_post_type) && isset($_GET['post_type'])) {
		$current_post_type = sanitize_key($_GET['post_type']);
	}
	// 备用：从post ID获取
	if (empty($current_post_type) && isset($_GET['post'])) {
		$post = get_post(intval($_GET['post']));
		if ($post) {
			$current_post_type = $post->post_type;
		}
	}

	if (('ai_tool' !== $current_post_type) && !$is_batch_page) {
		return;
	}

	// 输出 navaiAjax 对象供后台 JS 使用（前端通过 wp_localize_script 注入，后台需要手动输出）
	?>
	<script>
	window.navaiAjax = window.navaiAjax || {
		ajaxurl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
		nonce: '<?php echo esc_attr(wp_create_nonce('navai_nonce')); ?>'
	};
	</script>
	<style>
		@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
		.spinning { animation: spin 1s linear infinite; display: inline-block; }
	</style>
	<?php
}
add_action('admin_enqueue_scripts', 'navai_admin_scripts');

/**
 * Toast 通知组件 - 注入到所有后台页面
 */
function navai_admin_toast_component() {
	?>
	<style>
		.navai-toast-container {
			position: fixed;
			top: 40px;
			right: 20px;
			z-index: 999999;
			display: flex;
			flex-direction: column;
			gap: 8px;
			pointer-events: none;
		}
		.navai-toast {
			min-width: 280px;
			max-width: 420px;
			padding: 12px 16px;
			border-radius: 6px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
			font-size: 13px;
			line-height: 1.5;
			color: #1d2327;
			background: #fff;
			border-left: 4px solid #2271b1;
			pointer-events: auto;
			animation: navaiToastSlide 0.3s ease;
		}
		.navai-toast.navai-toast-success { border-left-color: #00a32a; }
		.navai-toast.navai-toast-error { border-left-color: #d63638; }
		.navai-toast.navai-toast-warning { border-left-color: #dba617; }
		.navai-toast.navai-toast-info { border-left-color: #2271b1; }
		.navai-toast .navai-toast-title {
			font-weight: 600;
			margin-bottom: 4px;
			display: flex;
			align-items: center;
			gap: 6px;
		}
		.navai-toast .navai-toast-body {
			color: #646970;
		}
		.navai-toast .navai-toast-body ul {
			margin: 4px 0 0 16px;
			padding: 0;
			list-style: disc;
		}
		.navai-toast .navai-toast-close {
			float: right;
			cursor: pointer;
			font-size: 18px;
			line-height: 1;
			color: #999;
			margin-left: 8px;
		}
		.navai-toast .navai-toast-close:hover { color: #333; }
		@keyframes navaiToastSlide {
			from { transform: translateX(100%); opacity: 0; }
			to { transform: translateX(0); opacity: 1; }
		}
		.navai-toast.navai-toast-out { animation: navaiToastOut 0.3s ease forwards; }
		@keyframes navaiToastOut {
			to { transform: translateX(100%); opacity: 0; }
		}
	</style>
	<script>
	(function($) {
		if (window.navaiToast) return;
		window.navaiToast = {
			_container: null,
			_ensureContainer: function() {
				if (!this._container || this._container.length === 0) {
					this._container = $('<div class="navai-toast-container"></div>');
					$('body').append(this._container);
				}
				return this._container;
			},
			show: function(type, title, body, duration) {
				var self = this;
				duration = duration || 5000;
				var $container = this._ensureContainer();

				var icons = {
					success: '\u2713',
					error: '\u2717',
					warning: '\u26a0',
					info: '\u2139'
				};

				var $toast = $('<div class="navai-toast navai-toast-' + type + '"></div>');
				var $title = $('<div class="navai-toast-title"></div>');
				$title.text((icons[type] || '') + ' ' + title);
				$title.append('<span class="navai-toast-close">&times;</span>');
				$toast.append($title);

				if (body) {
					$toast.append('<div class="navai-toast-body">' + body + '</div>');
				}

				$container.append($toast);

				$toast.find('.navai-toast-close').on('click', function() {
					self._remove($toast);
				});

				if (duration > 0) {
					setTimeout(function() {
						self._remove($toast);
					}, duration);
				}

				return $toast;
			},
			success: function(title, body, duration) {
				return this.show('success', title, body, duration);
			},
			error: function(title, body, duration) {
				return this.show('error', title, body, duration || 7000);
			},
			warning: function(title, body, duration) {
				return this.show('warning', title, body, duration);
			},
			info: function(title, body, duration) {
				return this.show('info', title, body, duration);
			},
			_remove: function($toast) {
				$toast.addClass('navai-toast-out');
				setTimeout(function() {
					$toast.remove();
				}, 300);
			}
		};
	})(jQuery);
	</script>
	<?php
}
add_action('admin_footer', 'navai_admin_toast_component');

/**
 * Toast 通知组件 - 注入到前端页面
 */
function navai_front_toast_component() {
	// 仅在投稿页加载
	if ( ! is_page_template('template-contribute.php')) {
		return;
	}
	?>
	<style>
		.navai-toast-container {
			position: fixed;
			top: 80px;
			right: 20px;
			z-index: 999999;
			display: flex;
			flex-direction: column;
			gap: 8px;
			pointer-events: none;
		}
		.navai-toast {
			min-width: 280px;
			max-width: 420px;
			padding: 12px 16px;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
			font-size: 14px;
			line-height: 1.5;
			color: #333;
			background: #fff;
			border-left: 4px solid #2271b1;
			pointer-events: auto;
			animation: navaiToastSlide 0.3s ease;
		}
		.navai-toast.navai-toast-success { border-left-color: #00a32a; }
		.navai-toast.navai-toast-error { border-left-color: #d63638; }
		.navai-toast.navai-toast-warning { border-left-color: #dba617; }
		.navai-toast.navai-toast-info { border-left-color: #2271b1; }
		.navai-toast .navai-toast-title {
			font-weight: 600;
			margin-bottom: 4px;
			display: flex;
			align-items: center;
			gap: 6px;
		}
		.navai-toast .navai-toast-body { color: #666; }
		.navai-toast .navai-toast-body ul { margin: 4px 0 0 16px; padding: 0; list-style: disc; }
		.navai-toast .navai-toast-close {
			float: right; cursor: pointer; font-size: 18px; line-height: 1;
			color: #999; margin-left: 8px;
		}
		.navai-toast .navai-toast-close:hover { color: #333; }
		@keyframes navaiToastSlide { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
		.navai-toast.navai-toast-out { animation: navaiToastOut 0.3s ease forwards; }
		@keyframes navaiToastOut { to { transform: translateX(100%); opacity: 0; } }
	</style>
	<script>
	(function($) {
		if (window.navaiToast) return;
		window.navaiToast = {
			_container: null,
			_ensureContainer: function() {
				if (!this._container || this._container.length === 0) {
					this._container = $('<div class="navai-toast-container"></div>');
					$('body').append(this._container);
				}
				return this._container;
			},
			show: function(type, title, body, duration) {
				var self = this;
				duration = duration || 5000;
				var $container = this._ensureContainer();
				var icons = { success: '\u2713', error: '\u2717', warning: '\u26a0', info: '\u2139' };
				var $toast = $('<div class="navai-toast navai-toast-' + type + '"></div>');
				var $title = $('<div class="navai-toast-title"></div>');
				$title.text((icons[type] || '') + ' ' + title);
				$title.append('<span class="navai-toast-close">&times;</span>');
				$toast.append($title);
				if (body) { $toast.append('<div class="navai-toast-body">' + body + '</div>'); }
				$container.append($toast);
				$toast.find('.navai-toast-close').on('click', function() { self._remove($toast); });
				if (duration > 0) { setTimeout(function() { self._remove($toast); }, duration); }
				return $toast;
			},
			success: function(title, body, duration) { return this.show('success', title, body, duration); },
			error: function(title, body, duration) { return this.show('error', title, body, duration || 7000); },
			warning: function(title, body, duration) { return this.show('warning', title, body, duration); },
			info: function(title, body, duration) { return this.show('info', title, body, duration); },
			_remove: function($toast) {
				$toast.addClass('navai-toast-out');
				setTimeout(function() { $toast.remove(); }, 300);
			}
		};
	})(jQuery);
	</script>
	<?php
}
add_action('wp_footer', 'navai_front_toast_component');

/**
 * 后台编辑页面底部输出采集脚本（使用admin_footer确保可靠加载）
 */
function navai_admin_footer_scripts() {
	global $pagenow;
	$is_edit_page = ($pagenow === 'post.php' || $pagenow === 'post-new.php');
	$is_batch_page = (isset($_GET['post_type']) && $_GET['post_type'] === 'ai_tool' && isset($_GET['page']) && $_GET['page'] === 'navai-batch-add');

	if (!$is_edit_page && !$is_batch_page) {
		return;
	}

	// 获取当前文章类型
	$current_post_type = '';
	if (function_exists('get_current_screen')) {
		$screen = get_current_screen();
		if ($screen) {
			$current_post_type = $screen->post_type;
		}
	}
	if (empty($current_post_type) && isset($_GET['post_type'])) {
		$current_post_type = sanitize_key($_GET['post_type']);
	}
	if (empty($current_post_type) && isset($_GET['post'])) {
		$post = get_post(intval($_GET['post']));
		if ($post) {
			$current_post_type = $post->post_type;
		}
	}

	if (('ai_tool' !== $current_post_type) && !$is_batch_page) {
		return;
	}

	$ajax_url = admin_url('admin-ajax.php');
	$nonce = wp_create_nonce('navai_nonce');
	?>
	<script>
	(function($) {
		"use strict";
		var navaiAdminI18n = {
			enterUrl: '<?php echo esc_js(__('请先输入网址', 'navai')); ?>',
			fetching: '<?php echo esc_js(__('采集中...', 'navai')); ?>',
			fetchSuccess: '<?php echo esc_js(__('采集成功', 'navai')); ?>',
			fetchFailed: '<?php echo esc_js(__('采集失败', 'navai')); ?>',
			unknownError: '<?php echo esc_js(__('未知错误', 'navai')); ?>',
			fetchError: '<?php echo esc_js(__('采集出错', 'navai')); ?>',
			gotTitle: '<?php echo esc_js(__('网站名称', 'navai')); ?>',
			gotDesc: '<?php echo esc_js(__('网站描述', 'navai')); ?>',
			gotIcon: '<?php echo esc_js(__('网站图标', 'navai')); ?>',
			gotNothing: '<?php echo esc_js(__('未采集到', 'navai')); ?>',
			partialSuccess: '<?php echo esc_js(__('部分采集成功', 'navai')); ?>',
			noDataFetched: '<?php echo esc_js(__('未采集到有效信息，请手动填写', 'navai')); ?>',
			timeout: '<?php echo esc_js(__('请求超时，请稍后重试', 'navai')); ?>',
			editorEmpty: '<?php echo esc_js(__('编辑器内容为空，请先输入内容', 'navai')); ?>',
			scanning: '<?php echo esc_js(__('扫描中...', 'navai')); ?>',
			scanFailed: '<?php echo esc_js(__('扫描失败：', 'navai')); ?>',
			requestError: '<?php echo esc_js(__('请求出错，请重试', 'navai')); ?>',
			noImages: '<?php echo esc_js(__('没有需要采集的图片', 'navai')); ?>',
			downloading: '<?php echo esc_js(__('正在下载图片...', 'navai')); ?>',
			downloadFailed: '<?php echo esc_js(__('采集失败：', 'navai')); ?>'
		};
		$(document).ready(function() {
			var $fetchBtn = $("#fetch-site-info");
			var $urlInput = $("#website_url");
			var $titleInput = $("#title");
			var $contentInput = $("#content");

			// 获取域名（兼容 URL 对象和手动解析）
			function extractDomain(rawUrl) {
				try { return new URL(rawUrl).hostname; } catch(e) {
					return rawUrl.replace(/^https?:\/\//, '').split('/')[0];
				}
			}

			// 多源 favicon 回退链：依次尝试多个来源
			function getFaviconFallbacks(domain, originalUrl) {
				var fallbacks = [];
				// 1. 网站自身 /favicon.ico
				if (originalUrl) {
					var proto = originalUrl.match(/^(https?:\/\/[^\/]+)/);
					if (proto) fallbacks.push(proto[1] + '/favicon.ico');
				}
				// 2. 一文 favicon API（国内可访问）
				if (domain) fallbacks.push('https://api.iowen.cn/favicon/' + encodeURIComponent(domain) + '.png');
				// 3. DuckDuckGo Icon API（部分地区可访问）
				if (domain) fallbacks.push('https://icons.duckduckgo.com/ip3/' + domain + '.ico');
				// 4. Google Favicon API（国外可用）
				if (domain) fallbacks.push('https://www.google.com/s2/favicons?domain=' + encodeURIComponent(domain) + '&sz=32');
				return fallbacks;
			}

			// 依次尝试加载 favicon 回退链
			function loadFaviconWithFallback($container, sources, idx, onAllFail) {
				idx = idx || 0;
				if (idx >= sources.length) {
					if (onAllFail) onAllFail();
					else $container.html('<span class="dashicons dashicons-globe"></span>');
					return;
				}
				var $img = $('<img>').attr("src", sources[idx]).css({width:"100%",height:"100%",objectFit:"cover"});
				$img.on("error", function() {
					loadFaviconWithFallback($container, sources, idx + 1, onAllFail);
				});
				$container.html("").append($img);
			}

			// 页面加载时恢复图标预览（防止图标加载失败或未渲染）
			function restoreIconPreview() {
				var iconUrl = $("#site_icon_url").val();
				var $preview = $("#url-icon-preview");
				var websiteUrl = $("#website_url").val();
				var domain = websiteUrl ? extractDomain(websiteUrl) : '';

				// 为已有的 img 添加 onerror 处理
				$preview.find("img").on("error", function() {
					var sources = getFaviconFallbacks(domain, websiteUrl);
					loadFaviconWithFallback($preview, sources, 0);
				});

				// 如果没有 img 但有 iconUrl，创建 img
				if (iconUrl && $preview.find("img").length === 0) {
					var $img = $('<img>').attr("src", iconUrl).css({width:"100%",height:"100%",objectFit:"cover"});
					$img.on("error", function() {
						var sources = getFaviconFallbacks(domain, websiteUrl);
						loadFaviconWithFallback($preview, sources, 0);
					});
					$preview.html("").append($img);
				}
			}
			restoreIconPreview();

			$fetchBtn.on("click", function(e) {
				e.preventDefault();
				var url = $urlInput.val().trim();
				if (!url) {
				navaiToast.warning(navaiAdminI18n.enterUrl);
				$urlInput.focus();
				return;
			}
				var originalText = $fetchBtn.html();
				$fetchBtn.html('<span class="dashicons dashicons-update-alt spinning" style="font-size:16px;width:16px;height:16px;line-height:1;"></span> ' + navaiAdminI18n.fetching);
				$fetchBtn.prop("disabled", true);
				$.ajax({
					url: "<?php echo esc_url($ajax_url); ?>",
					type: "POST",
					timeout: 60000,
					data: {
						action: "navai_fetch_site_info",
						nonce: "<?php echo esc_attr($nonce); ?>",
						url: url
					},
					success: function(response) {
						if (response.success) {
							var data = response.data;
							var gotFields = [];
							if (data.title) {
								if (wp.data && wp.data.select("core/editor")) {
									wp.data.dispatch("core/editor").editPost({ title: data.title });
								}
								if ($titleInput.length) $titleInput.val(data.title);
								var titlePreview = data.title.length > 40 ? data.title.substring(0, 40) + '...' : data.title;
								gotFields.push('<li>' + navaiAdminI18n.gotTitle + ': <strong>' + $('<div>').text(titlePreview).html() + '</strong></li>');
							}
							if (data.description) {
								if (wp.data && wp.data.select("core/editor")) {
									wp.data.dispatch("core/editor").editPost({ content: data.description });
								}
								if (typeof tinymce !== "undefined" && tinymce.get("content")) {
									tinymce.get("content").setContent(data.description);
								} else if ($contentInput.length) {
									$contentInput.val(data.description);
								}
								var descPreview = data.description.length > 60 ? data.description.substring(0, 60) + '...' : data.description;
								gotFields.push('<li>' + navaiAdminI18n.gotDesc + ': <strong>' + $('<div>').text(descPreview).html() + '</strong></li>');
							}
							var $urlIcon = $("#url-icon-preview");

							// favicon处理：使用服务器返回的候选列表进行客户端回退加载
							var faviconCandidates = data.favicon_candidates || [];
							var iconLoaded = false;
							$("#site_icon_url").val("");

							if (faviconCandidates.length > 0) {
								gotFields.push('<li>' + navaiAdminI18n.gotIcon + ': <strong>&#10003;</strong></li>');
								(function tryIcon(idx) {
									if (idx >= faviconCandidates.length || iconLoaded) {
										if (!iconLoaded) {
											$urlIcon.html('<span class="dashicons dashicons-globe"></span>');
										}
										return;
									}
									var $img = $('<img>').attr("src", faviconCandidates[idx]).css({width:"100%",height:"100%",objectFit:"cover"});
									$img.on("error", function() {
										tryIcon(idx + 1);
									});
									$img.on("load", function() {
										if (!iconLoaded) {
											iconLoaded = true;
											$("#site_icon_url").val(faviconCandidates[idx]);
											// 同步到 Gutenberg 区块编辑器数据存储
											if (wp.data && wp.data.select("core/editor")) {
												var currentMeta = wp.data.select("core/editor").getEditedPostAttribute("meta") || {};
												currentMeta._site_icon_url = faviconCandidates[idx];
												wp.data.dispatch("core/editor").editPost({ meta: currentMeta });
											}
										}
									});
									$urlIcon.html("").append($img);
								})(0);
							}

							var missingFields = [];
							if (!data.title) missingFields.push(navaiAdminI18n.gotTitle);
							if (!data.description) missingFields.push(navaiAdminI18n.gotDesc);
							if (faviconCandidates.length === 0) missingFields.push(navaiAdminI18n.gotIcon);

							if (gotFields.length === 3) {
								navaiToast.success(navaiAdminI18n.fetchSuccess, '<ul>' + gotFields.join('') + '</ul>');
							} else if (gotFields.length > 0) {
								var body = '<ul>' + gotFields.join('') + '</ul>';
								if (missingFields.length) {
									body += '<div style="margin-top:4px;color:#999;">' + navaiAdminI18n.gotNothing + ': ' + missingFields.join('、') + '</div>';
								}
								navaiToast.warning(navaiAdminI18n.partialSuccess, body, 7000);
							} else {
								navaiToast.warning(navaiAdminI18n.noDataFetched, '', 7000);
							}
						} else {
							navaiToast.error(navaiAdminI18n.fetchFailed, response.data.message || navaiAdminI18n.unknownError);
						}
					},
					error: function(xhr, status, error) {
						var msg = (status === 'timeout') ? navaiAdminI18n.timeout : (error || status);
						navaiToast.error(navaiAdminI18n.fetchError, msg);
					},
					complete: function() {
						$fetchBtn.html(originalText);
						$fetchBtn.prop("disabled", false);
					}
				});
			});

			var style = document.createElement("style");
			style.textContent = "@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .spinning { animation: spin 1s linear infinite; }";
			document.head.appendChild(style);

			// 远程图片采集
			var scannedImages = [];

			// 从编辑器获取当前内容
			function getEditorContent() {
				if (typeof tinymce !== "undefined" && tinymce.get("content")) {
					return tinymce.get("content").getContent();
				}
				if (wp.data && wp.data.select("core/editor")) {
					return wp.data.select("core/editor").getEditedPostAttribute("content");
				}
				return $("#content").val() || "";
			}

			// 将内容写回编辑器
			function setEditorContent(newContent) {
				if (typeof tinymce !== "undefined" && tinymce.get("content")) {
					tinymce.get("content").setContent(newContent);
					tinymce.get("content").save();
				} else if (wp.data && wp.data.select("core/editor")) {
					wp.data.dispatch("core/editor").editPost({ content: newContent });
				} else {
					$("#content").val(newContent);
				}
			}

			$(document).on("click", "#navai-scan-remote-images", function() {
				var $btn = $(this);
				var content = getEditorContent();

				if (!content || content.length < 10) {
					navaiToast.warning(navaiAdminI18n.editorEmpty);
					return;
				}

				$btn.prop("disabled", true);
				var origHtml = $btn.html();
				$btn.html('<span class="dashicons dashicons-update-alt spinning" style="font-size:16px;width:16px;height:16px;line-height:1;"></span> ' + navaiAdminI18n.scanning);

				$.ajax({
					url: "<?php echo esc_url($ajax_url); ?>",
					type: "POST",
					data: {
						action: "navai_scan_remote_images",
						nonce: "<?php echo esc_attr($nonce); ?>",
						content: content
					},
					success: function(response) {
						if (response.success) {
							var data = response.data;
							scannedImages = data.images || [];
							if (scannedImages.length === 0) {
							navaiToast.info(navaiAdminI18n.noImages, data.message);
							return;
						}
							var html = '';
							scannedImages.forEach(function(img, idx) {
								html += '<div style="display:flex;align-items:center;gap:8px;padding:4px 0;border-bottom:1px solid #ddd;">';
								html += '<span style="color:#646970;font-size:12px;">' + (idx + 1) + '.</span>';
								html += '<img src="' + img.url + '" style="width:40px;height:40px;object-fit:cover;border-radius:3px;border:1px solid #c3c4c7;flex-shrink:0;" onerror="this.style.display=\'none\'">';
								html += '<span style="font-size:12px;color:#1d2327;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">' + img.url + '</span>';
								html += '</div>';
							});
							$("#navai-image-items").html(html);
							$("#navai-image-list").show();
							$btn.hide();
						} else {
							navaiToast.error(navaiAdminI18n.scanFailed, response.data.message || navaiAdminI18n.unknownError);
						}
					},
					error: function() {
						navaiToast.error(navaiAdminI18n.requestError);
					},
					complete: function() {
						$btn.prop("disabled", false);
						$btn.html(origHtml);
					}
				});
			});

			$(document).on("click", "#navai-cancel-download", function() {
				$("#navai-image-list").hide();
				$("#navai-scan-remote-images").show();
				scannedImages = [];
			});

			$(document).on("click", "#navai-start-download", function() {
				var $btn = $(this);
				var $bar = $("#navai-download-bar");
				var $status = $("#navai-download-status");
				var $progress = $("#navai-download-progress");
				var postId = $("#post_ID").val() || $("input[name='post_ID']").val();
				var content = getEditorContent();

				if (scannedImages.length === 0) {
					navaiToast.warning(navaiAdminI18n.noImages);
					return;
				}

				$btn.prop("disabled", true);
				$("#navai-cancel-download").prop("disabled", true);
				$progress.show();
				$bar.css("width", "10%");
				$bar.css("background", "#2271b1");
				$status.text(navaiAdminI18n.downloading);
				$status.css("color", "");

				$.ajax({
					url: "<?php echo esc_url($ajax_url); ?>",
					type: "POST",
					data: {
						action: "navai_download_remote_images",
						nonce: "<?php echo esc_attr($nonce); ?>",
						post_id: postId,
						content: content,
						images: scannedImages
					},
					success: function(response) {
						if (response.success) {
							var data = response.data;
							$bar.css("width", "100%");
							$status.text(data.message);
							if (data.downloaded > 0) {
								$status.css("color", "#00a32a");
								// 将替换后的内容写回编辑器
								if (data.new_content) {
									setEditorContent(data.new_content);
								}
							}
							setTimeout(function() {
								$("#navai-image-list").hide();
								$("#navai-scan-remote-images").show();
								scannedImages = [];
							}, 2000);
						} else {
							$bar.css("width", "100%");
							$bar.css("background", "#d63638");
							$status.text(navaiAdminI18n.downloadFailed + (response.data.message || navaiAdminI18n.unknownError));
							$status.css("color", "#d63638");
						}
					},
					error: function() {
						$bar.css("width", "100%");
						$bar.css("background", "#d63638");
						$status.text(navaiAdminI18n.requestError);
						$status.css("color", "#d63638");
					},
					complete: function() {
						$btn.prop("disabled", false);
						$("#navai-cancel-download").prop("disabled", false);
					}
				});
			});
		});
	})(jQuery);
	</script>
	<?php
}
add_action('admin_footer', 'navai_admin_footer_scripts');

/**
 * 网址列表页底部脚本 - 重复检测功能
 */
function navai_admin_list_footer_scripts() {
	global $pagenow;
	$current_post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : '';

	if ($pagenow !== 'edit.php' || $current_post_type !== 'ai_tool') {
		return;
	}

	$ajax_url = admin_url('admin-ajax.php');
	$nonce = wp_create_nonce('navai_nonce');
	?>
	<script>
	(function($) {
		'use strict';

		// 在页面顶部添加重复检测按钮
		$(function() {
			var $pageHead = $('.page-title-action');
			if ($pageHead.length === 0) return;

			var $btn = $('<a href="#" class="page-title-action navai-dup-check-btn" style="margin-left:5px;background:#f0f0f1;">' +
				'<?php echo esc_js(__('重复检测', 'navai')); ?>' +
				'</a>');

			$pageHead.after($btn);
		});

		// 重复检测结果模态框
		var navaiDupModal = {
			i18n: {
				checking: '<?php echo esc_js(__('检测中...', 'navai')); ?>',
				noDuplicates: '<?php echo esc_js(__('未发现重复网址', 'navai')); ?>',
				foundDuplicates: '<?php echo esc_js(__('发现重复', 'navai')); ?>',
				close: '<?php echo esc_js(__('关闭', 'navai')); ?>',
				delete: '<?php echo esc_js(__('删除', 'navai')); ?>',
				keep: '<?php echo esc_js(__('保留', 'navai')); ?>',
				keepThis: '<?php echo esc_js(__('设为保留', 'navai')); ?>',
				edit: '<?php echo esc_js(__('编辑', 'navai')); ?>',
				groups: '<?php echo esc_js(__('组重复', 'navai')); ?>',
				posts: '<?php echo esc_js(__('个网址', 'navai')); ?>',
				scanned: '<?php echo esc_js(__('已扫描', 'navai')); ?>',
				confirmDelete: '<?php echo esc_js(__('确定要删除此重复网址吗？（将移至回收站）', 'navai')); ?>',
				deleting: '<?php echo esc_js(__('删除中...', 'navai')); ?>',
				deleteFailed: '<?php echo esc_js(__('删除失败', 'navai')); ?>',
				deleteSuccess: '<?php echo esc_js(__('已删除', 'navai')); ?>',
				statusPublish: '<?php echo esc_js(__('已发布', 'navai')); ?>',
				statusPending: '<?php echo esc_js(__('待审核', 'navai')); ?>',
				statusDraft: '<?php echo esc_js(__('草稿', 'navai')); ?>',
				hasDesc: '<?php echo esc_js(__('描述', 'navai')); ?>',
				hasIcon: '<?php echo esc_js(__('图标', 'navai')); ?>',
				hasCategory: '<?php echo esc_js(__('分类', 'navai')); ?>',
				hasTags: '<?php echo esc_js(__('标签', 'navai')); ?>',
				completeness: '<?php echo esc_js(__('完整度', 'navai')); ?>'
			},

			show: function(data) {
				var self = this;
				this.removeModal();

				var dupCount = data.total_groups;
				var postCount = data.total_posts;
				var scanned = data.total_scanned;

				var html = '<div class="navai-dup-modal-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999999;display:flex;align-items:center;justify-content:center;">';
				html += '<div class="navai-dup-modal" style="background:#fff;border-radius:8px;max-width:800px;width:90%;max-height:80vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 10px 30px rgba(0,0,0,0.3);">';
				html += '<div style="padding:20px 24px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">';
				html += '<h2 style="font-size:18px;margin:0;">' + (dupCount > 0 ? this.i18n.foundDuplicates : this.i18n.noDuplicates) + '</h2>';
				html += '<button type="button" class="navai-dup-close" style="background:none;border:none;font-size:24px;cursor:pointer;color:#666;line-height:1;padding:0 4px;">&times;</button>';
				html += '</div>';

				html += '<div style="padding:16px 24px;background:#f9f9f9;font-size:13px;color:#666;border-bottom:1px solid #eee;">';
				html += this.i18n.scanned + ' <strong>' + scanned + '</strong> ' + this.i18n.posts;
				if (dupCount > 0) {
					html += '，' + this.i18n.foundDuplicates + ' <strong style="color:#d63638;">' + dupCount + '</strong> ' + this.i18n.groups + '（' + postCount + ' ' + this.i18n.posts + '）';
				}
				html += '</div>';

				if (dupCount > 0) {
					html += '<div style="overflow-y:auto;flex:1;padding:0 24px;">';
					for (var i = 0; i < data.duplicates.length; i++) {
						var dup = data.duplicates[i];
						html += '<div class="navai-dup-group" data-group-index="' + i + '" style="padding:16px 0;border-bottom:1px solid #f0f0f0;">';
						html += '<div style="margin-bottom:8px;"><strong style="color:#d63638;">' + this.i18n.foundDuplicates + ' ' + dup.count + '</strong> — <a href="' + dup.url + '" target="_blank" style="color:#2271b1;">' + dup.url + '</a></div>';
						for (var j = 0; j < dup.items.length; j++) {
							var item = dup.items[j];
						var isKeep = (j === 0);
						// 构建图标回退源：优先已保存图标，否则使用多源回退链
						var iconSrc = item.icon_url;
						if (!iconSrc) {
							var itemDomain = '';
							try { itemDomain = new URL(item.url).hostname; } catch(e) { itemDomain = item.url.replace(/^https?:\/\//, '').split('/')[0]; }
							iconSrc = itemDomain ? ('https://api.iowen.cn/favicon/' + encodeURIComponent(itemDomain) + '.png') : '';
						}
						var iconHtml = iconSrc
							? '<img src="' + iconSrc + '" style="width:16px;height:16px;border-radius:3px;vertical-align:middle;" onerror="this.onerror=null;this.src=\'https://icons.duckduckgo.com/ip3/' + (item.url.replace(/^https?:\/\//, '').split('/')[0]) + '.ico\';this.onerror=function(){this.style.display=\'none\';}">'
							: '<span class="dashicons dashicons-globe" style="font-size:16px;color:#ccc;"></span>';
							var statusLabel = '', statusColor = '#999';
							if (item.post_status === 'publish') {
								statusLabel = this.i18n.statusPublish;
								statusColor = '#00a32a';
							} else if (item.post_status === 'pending') {
								statusLabel = this.i18n.statusPending;
								statusColor = '#d63638';
							} else if (item.post_status === 'draft') {
								statusLabel = this.i18n.statusDraft;
								statusColor = '#996600';
							}
							html += '<div class="navai-dup-row" data-post-id="' + item.id + '" data-group-index="' + i + '" style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:' + (isKeep ? '#f0f7ff' : '#fff') + ';border-radius:4px;margin-bottom:4px;border:1px solid ' + (isKeep ? '#2271b1' : '#eee') + ';">';
							// 保留单选
							html += '<label style="display:flex;align-items:center;cursor:pointer;flex-shrink:0;" title="' + this.i18n.keepThis + '">';
							html += '<input type="radio" name="navai_keep_group_' + i + '" value="' + item.id + '"' + (isKeep ? ' checked' : '') + ' class="navai-keep-radio" style="margin:0;cursor:pointer;">';
							html += '</label>';
							// 网址图标
						html += iconHtml;
							// 标题 + 网址 + 完整度标签
							html += '<span style="font-size:13px;flex:1;min-width:0;overflow:hidden;">';
							html += '<div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">';
							html += '<span style="color:#999;">#' + item.id + '</span> ';
							html += '<span style="font-weight:500;">' + (item.title || '(无标题)') + '</span>';
							html += '</div>';
							html += '<a href="' + item.url + '" target="_blank" style="font-size:12px;color:#666;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + item.url + '">' + item.url + '</a>';
							// 完整度标签
							html += '<div style="margin-top:3px;display:flex;gap:4px;flex-wrap:wrap;">';
							html += '<span style="font-size:11px;padding:1px 6px;border-radius:3px;background:' + statusColor + ';color:#fff;">' + statusLabel + '</span>';
							html += '<span style="font-size:11px;padding:1px 6px;border-radius:3px;background:' + (item.has_desc ? '#e0f0e0' : '#f0f0f0') + ';color:' + (item.has_desc ? '#2e7d32' : '#bbb') + ';">' + (item.has_desc ? '\u2713' : '\u2717') + this.i18n.hasDesc + '</span>';
							html += '<span style="font-size:11px;padding:1px 6px;border-radius:3px;background:' + (item.has_icon ? '#e0f0e0' : '#f0f0f0') + ';color:' + (item.has_icon ? '#2e7d32' : '#bbb') + ';">' + (item.has_icon ? '\u2713' : '\u2717') + this.i18n.hasIcon + '</span>';
							html += '<span style="font-size:11px;padding:1px 6px;border-radius:3px;background:' + (item.has_category ? '#e0f0e0' : '#f0f0f0') + ';color:' + (item.has_category ? '#2e7d32' : '#bbb') + ';">' + (item.has_category ? '\u2713' : '\u2717') + this.i18n.hasCategory + '</span>';
							html += '<span style="font-size:11px;padding:1px 6px;border-radius:3px;background:' + (item.has_tags ? '#e0f0e0' : '#f0f0f0') + ';color:' + (item.has_tags ? '#2e7d32' : '#bbb') + ';">' + (item.has_tags ? '\u2713' : '\u2717') + this.i18n.hasTags + '</span>';
							html += '<span style="font-size:11px;padding:1px 6px;border-radius:3px;background:#e8e8e8;color:#666;">' + this.i18n.completeness + ' ' + item.completeness + '/4</span>';
							html += '</div>';
							html += '</span>';
							// 操作按钮
							html += '<span class="navai-row-actions" style="display:flex;align-items:center;gap:6px;flex-shrink:0;">';
							if (isKeep) {
								html += '<span style="color:#00a32a;font-size:12px;font-weight:600;">' + this.i18n.keep + '</span>';
							} else {
								html += '<a href="post.php?post=' + item.id + '&action=edit" target="_blank" style="font-size:12px;color:#2271b1;">' + this.i18n.edit + '</a>';
								html += '<button type="button" class="button navai-dup-delete-btn" data-post-id="' + item.id + '" style="font-size:12px;padding:2px 10px;color:#d63638;border-color:#d63638;">' + this.i18n.delete + '</button>';
							}
							html += '</span>';
							html += '</div>';
						}
						html += '</div>';
					}
					html += '</div>';
				} else {
					html += '<div style="padding:48px 24px;text-align:center;color:#666;">';
					html += '<span class="dashicons dashicons-yes-alt" style="font-size:48px;color:#00a32a;display:block;margin-bottom:16px;"></span>';
					html += '<p style="font-size:15px;">' + this.i18n.noDuplicates + '</p>';
					html += '</div>';
				}

				html += '<div style="padding:16px 24px;border-top:1px solid #eee;text-align:right;">';
				html += '<button type="button" class="button button-primary navai-dup-close">' + this.i18n.close + '</button>';
				html += '</div>';
				html += '</div></div>';

				$('body').append(html);

				$(document).on('click', '.navai-dup-close', function() {
					self.removeModal();
				});

				$(document).on('click', '.navai-dup-modal-overlay', function(e) {
					if (e.target === this) {
						self.removeModal();
					}
				});

				// 切换保留项
				$(document).on('change', '.navai-keep-radio', function() {
					var keepId = $(this).val();
					var groupIndex = $(this).attr('name').replace('navai_keep_group_', '');
					var $group = $('.navai-dup-group[data-group-index="' + groupIndex + '"]');

					$group.find('.navai-dup-row').each(function() {
						var $row = $(this);
						var postId = String($row.data('post-id'));
						var isKeep = (postId === keepId);

						$row.css({
							'background':   isKeep ? '#f0f7ff' : '#fff',
							'border-color': isKeep ? '#2271b1' : '#eee'
						});

						var $actions = $row.find('.navai-row-actions');
						if (isKeep) {
							$actions.html('<span style="color:#00a32a;font-size:12px;font-weight:600;">' + navaiDupModal.i18n.keep + '</span>');
						} else {
							var pid = $row.data('post-id');
							$actions.html(
								'<a href="post.php?post=' + pid + '&action=edit" target="_blank" style="font-size:12px;color:#2271b1;">' + navaiDupModal.i18n.edit + '</a>' +
								'<button type="button" class="button navai-dup-delete-btn" data-post-id="' + pid + '" style="font-size:12px;padding:2px 10px;color:#d63638;border-color:#d63638;">' + navaiDupModal.i18n.delete + '</button>'
							);
						}
					});
				});

				// 删除重复
				$(document).on('click', '.navai-dup-delete-btn', function() {
					var postId = $(this).data('post-id');
					var $row = $(this).closest('.navai-dup-row');
					var groupIndex = $row.data('group-index');
					var $btn = $(this);

					// 从同组中获取当前选中的保留项
					var keepId = $('input[name="navai_keep_group_' + groupIndex + '"]:checked').val();

					if ( ! confirm(navaiDupModal.i18n.confirmDelete)) {
						return;
					}

					$btn.prop('disabled', true).text(navaiDupModal.i18n.deleting);

					$.ajax({
						url: '<?php echo esc_js($ajax_url); ?>',
						type: 'POST',
						data: {
							action: 'navai_delete_duplicate',
							nonce: '<?php echo esc_js($nonce); ?>',
							post_id: postId,
							keep_id: keepId
						},
						success: function(response) {
							if (response.success) {
								$row.fadeOut(300, function() {
									$(this).remove();
								});
							} else {
								alert(response.data.message || navaiDupModal.i18n.deleteFailed);
								$btn.prop('disabled', false).text(navaiDupModal.i18n.delete);
							}
						},
						error: function() {
							alert(navaiDupModal.i18n.deleteFailed);
							$btn.prop('disabled', false).text(navaiDupModal.i18n.delete);
						}
					});
				});
			},

			removeModal: function() {
				$('.navai-dup-modal-overlay').remove();
			}
		};

		// 点击检测按钮
		$(document).on('click', '.navai-dup-check-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var originalText = $btn.text();
			$btn.prop('disabled', true).text(navaiDupModal.i18n.checking);

			$.ajax({
				url: '<?php echo esc_js($ajax_url); ?>',
				type: 'POST',
				data: {
					action: 'navai_check_duplicates',
					nonce: '<?php echo esc_js($nonce); ?>'
				},
				success: function(response) {
					$btn.prop('disabled', false).text(originalText);
					if (response.success) {
						navaiDupModal.show(response.data);
					} else {
						alert(response.data.message || 'Error');
					}
				},
				error: function() {
					$btn.prop('disabled', false).text(originalText);
					alert('Request failed');
				}
			});
		});
	})(jQuery);
	</script>
	<?php
}
add_action('admin_footer', 'navai_admin_list_footer_scripts');

/**
 * ============================================================================
 * 13. 修改固定链接结构
 * ============================================================================
 */

/**
 * 自定义文章类型链接 - 使用post_id
 *
 * @param string  $post_link 文章链接
 * @param WP_Post $post      文章对象
 * @return string 修改后的链接
 */
function navai_custom_post_link($post_link, $post) {
	if ('ai_tool' === $post->post_type) {
		return home_url('/navi/' . $post->ID . '.html');
	}
	return $post_link;
}
add_filter('post_type_link', 'navai_custom_post_link', 10, 2);

/**
 * 添加重写规则（使用文章ID+.html格式）
 *
 * @return void
 */
function navai_custom_rewrite_rules() {
	add_rewrite_rule(
		'^navi/([0-9]+)\.html?$',
		'index.php?post_type=ai_tool&p=$matches[1]',
		'top'
	);
}
add_action('init', 'navai_custom_rewrite_rules', 20);

/**
 * 强制ai_tool文章类型评论始终打开
 *
 * @param bool $open    评论是否打开
 * @param int  $post_id 文章ID
 * @return bool
 */
function navai_force_comments_open($open, $post_id) {
	$post = get_post($post_id);
	if ($post && $post->post_type === 'ai_tool') {
		return true;
	}
	return $open;
}
add_filter('comments_open', 'navai_force_comments_open', 10, 2);

/**
 * ============================================================================
 * 14. 侧边栏子分类z-index修复
 * ============================================================================
 */

/**
 * 添加侧边栏子菜单样式
 *
 * @return void
 */
function navai_sidebar_submenu_styles() {
	$custom_css = '.sidebar-item-wrapper{position:relative}.sidebar-item-wrapper.has-children{z-index:1}.sidebar-item-wrapper.has-children:hover{z-index:100}.sidebar-submenu{z-index:101}';
	wp_add_inline_style('navai-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'navai_sidebar_submenu_styles');

/**
 * ============================================================================
 * 15. 网址审核页面
 * ============================================================================
 */

/**
 * 检查IP是否在CIDR范围内（用于SSRF防护）
 *
 * @param string $ip       要检查的IP
 * @param string $range    网络范围起始IP
 * @param int    $cidr     CIDR前缀长度
 * @return bool
 */
function navai_ip_in_range($ip, $range, $cidr) {
	if (!function_exists('ip2long')) {
		return false;
	}
	$ip_long = ip2long($ip);
	$range_long = ip2long($range);
	if ($ip_long === false || $range_long === false) {
		return false;
	}
	$mask = -1 << (32 - $cidr);
	return (($ip_long & $mask) === ($range_long & $mask));
}

/**
 * 验证URL是否为合法的外部URL（非内网）
 *
 * @param string $url URL地址
 * @return bool
 */
function navai_is_valid_external_url($url) {
	$parsed = parse_url($url);
	$host = isset($parsed['host']) ? strtolower($parsed['host']) : '';
	if (empty($host)) {
		return false;
	}
	if ('localhost' === $host ) {
		return false;
	}
	if (preg_match('/^[\d.]+$/', $host)) {
		return false;
	}
	$ip = gethostbyname($host);
	$blocked_ranges = array(
		array('127.0.0.0', '8'),
		array('10.0.0.0', '8'),
		array('172.16.0.0', '12'),
		array('192.168.0.0', '16'),
		array('0.0.0.0', '8'),
		array('169.254.0.0', '16'),
	);
	foreach ($blocked_ranges as $range) {
		if (navai_ip_in_range($ip, $range[0], $range[1])) {
			return false;
		}
	}
	return true;
}

/**
 * 网址审核页面
 *
 * @return void
 */
function navai_site_review_page() {
	global $wpdb;

	// 处理审核操作
	if (isset($_POST['navai_review_action']) && check_admin_referer('navai_review_nonce')) {
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$action_type = sanitize_text_field($_POST['navai_review_action']);

		if ($post_id) {
			switch ($action_type) {
				case 'approve':
					wp_update_post(array(
						'ID'          => $post_id,
						'post_status' => 'publish',
					));
					update_post_meta($post_id, '_submit_status', 'approved');
					echo '<div class="notice notice-success"><p>' . esc_html__('网站已通过审核并发布', 'navai') . '</p></div>';
					break;

				case 'reject':
					wp_update_post(array(
						'ID'          => $post_id,
						'post_status' => 'draft',
					));
					update_post_meta($post_id, '_submit_status', 'rejected');
					echo '<div class="notice notice-warning"><p>' . esc_html__('网站已拒绝', 'navai') . '</p></div>';
					break;

				case 'update':
					$site_name = sanitize_text_field($_POST['site_name']);
					$site_url = esc_url_raw($_POST['site_url']);
					$site_desc = wp_kses_post($_POST['site_desc']);
					$site_category = isset($_POST['site_category']) ? array_map('intval', $_POST['site_category']) : array();
					$site_tags = sanitize_text_field($_POST['site_tags']);
					$site_icon_url = esc_url_raw($_POST['site_icon_url']);

					// post_status 白名单校验
					$allowed_statuses = array('pending', 'publish', 'draft');
					$site_status = isset($_POST['site_status']) ? sanitize_text_field($_POST['site_status']) : 'pending';
					if (!in_array($site_status, $allowed_statuses, true)) {
						$site_status = 'pending';
					}

					wp_update_post(array(
						'ID'           => $post_id,
						'post_title'   => $site_name,
						'post_content' => $site_desc,
						'post_status'  => $site_status,
					));

					update_post_meta($post_id, '_website_url', $site_url);
					update_post_meta($post_id, '_site_icon_url', $site_icon_url);
					update_post_meta($post_id, '_site_tags', $site_tags);

					if ( ! empty($site_category)) {
						wp_set_object_terms($post_id, $site_category, 'ai_category');
					}

					if ('publish' === $site_status ) {
						update_post_meta($post_id, '_submit_status', 'approved');
					} elseif ($site_status === 'draft') {
						update_post_meta($post_id, '_submit_status', 'rejected');
					} else {
						update_post_meta($post_id, '_submit_status', 'pending');
					}

					echo '<div class="notice notice-success"><p>' . esc_html__('网站信息已更新', 'navai') . '</p></div>';
					break;
			}
		}
	}

	// 获取当前筛选状态
	$filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';

	// 获取待审核网站列表（仅外部提交的网站）
	$args = array(
		'post_type'      => 'ai_tool',
		'posts_per_page' => 20,
		'paged'          => isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1,
		'meta_query'     => array(
			array(
				'key'     => '_submit_user',
				'compare' => 'EXISTS',
			),
		),
	);

	if ('pending' === $filter_status ) {
		$args['post_status'] = 'pending';
	} elseif ($filter_status === 'approved') {
		$args['post_status'] = 'publish';
		$args['meta_query'][] = array(
			'key'     => '_submit_status',
			'value'   => 'approved',
			'compare' => '=',
		);
	} elseif ($filter_status === 'rejected') {
		$args['post_status'] = 'draft';
		$args['meta_query'][] = array(
			'key'     => '_submit_status',
			'value'   => 'rejected',
			'compare' => '=',
		);
	} else {
		$args['post_status'] = array('pending', 'publish', 'draft');
	}

	$query = new WP_Query($args);

	// 获取各状态数量（仅外部提交的网站）
	$pending_count = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} p 
         INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
         WHERE p.post_type = %s AND p.post_status = 'pending' AND pm.meta_key = '_submit_user'",
		'ai_tool'
	));
	$publish_count = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} p 
         INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id 
         INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id 
         WHERE p.post_type = %s AND p.post_status = 'publish' AND pm1.meta_key = '_submit_user' AND pm2.meta_key = '_submit_status' AND pm2.meta_value = 'approved'",
		'ai_tool'
	));
	$rejected_count = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} p 
         INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id 
         INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id 
         WHERE p.post_type = %s AND p.post_status = 'draft' AND pm1.meta_key = '_submit_user' AND pm2.meta_key = '_submit_status' AND pm2.meta_value = 'rejected'",
		'ai_tool'
	));
	$total_count = $pending_count + $publish_count + $rejected_count;
	?>
	<div class="wrap">
		<h1><?php esc_html_e('网址审核', 'navai'); ?></h1>

		<!-- 筛选标签 -->
		<ul class="subsubsub">
			<li><a href="<?php echo admin_url('edit.php?post_type=ai_tool&page=navai-site-review&filter_status=all'); ?>" class="<?php echo $filter_status === 'all' ? 'current' : ''; ?>"><?php esc_html_e('全部', 'navai'); ?> <span class="count">(<?php echo intval($total_count); ?>)</span></a> |</li>
			<li><a href="<?php echo admin_url('edit.php?post_type=ai_tool&page=navai-site-review&filter_status=pending'); ?>" class="<?php echo $filter_status === 'pending' ? 'current' : ''; ?>"><?php esc_html_e('待审核', 'navai'); ?> <span class="count">(<?php echo intval($pending_count); ?>)</span></a> |</li>
			<li><a href="<?php echo admin_url('edit.php?post_type=ai_tool&page=navai-site-review&filter_status=approved'); ?>" class="<?php echo $filter_status === 'approved' ? 'current' : ''; ?>"><?php esc_html_e('已收录', 'navai'); ?> <span class="count">(<?php echo intval($publish_count); ?>)</span></a> |</li>
			<li><a href="<?php echo admin_url('edit.php?post_type=ai_tool&page=navai-site-review&filter_status=rejected'); ?>" class="<?php echo $filter_status === 'rejected' ? 'current' : ''; ?>"><?php esc_html_e('已拒绝', 'navai'); ?> <span class="count">(<?php echo intval($rejected_count); ?>)</span></a></li>
		</ul>

		<?php if ($query->have_posts()) : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th width="60">ID</th>
					<th width="40"><?php esc_html_e('图标', 'navai'); ?></th>
					<th><?php esc_html_e('网站名称', 'navai'); ?></th>
					<th><?php esc_html_e('网址', 'navai'); ?></th>
					<th><?php esc_html_e('提交用户', 'navai'); ?></th>
					<th><?php esc_html_e('提交时间', 'navai'); ?></th>
					<th><?php esc_html_e('状态', 'navai'); ?></th>
					<th width="180"><?php esc_html_e('操作', 'navai'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php while ($query->have_posts()) : $query->the_post(); ?>
				<?php
				$post_id = get_the_ID();
				$website_url = get_post_meta($post_id, '_website_url', true);
				$site_icon_url = get_post_meta($post_id, '_site_icon_url', true);
				$submit_user = get_post_meta($post_id, '_submit_user', true);
				$submit_time = get_post_meta($post_id, '_submit_time', true);
				$current_status = get_post_status($post_id);
				$submit_status = get_post_meta($post_id, '_submit_status', true);

				$user_info = $submit_user ? get_userdata($submit_user) : null;
				$user_name = $user_info ? $user_info->display_name : get_the_author();

				if ('pending' === $current_status ) {
					$status_label = __('待审核', 'navai');
					$status_class = 'status-pending';
				} elseif ($current_status === 'publish') {
					$status_label = __('已收录', 'navai');
					$status_class = 'status-approved';
				} else {
					$status_label = __('已拒绝', 'navai');
					$status_class = 'status-rejected';
				}
				?>
				<tr>
					<td><?php echo $post_id; ?></td>
					<td>
						<?php if ($site_icon_url) : ?>
							<img src="<?php echo esc_url($site_icon_url); ?>" alt="" style="width:32px;height:32px;border-radius:4px;">
						<?php else : ?>
							<span class="dashicons dashicons-globe" style="font-size:28px;color:#ccc;"></span>
						<?php endif; ?>
					</td>
					<td><strong><?php the_title(); ?></strong></td>
					<td><a href="<?php echo esc_url($website_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($website_url); ?></a></td>
					<td><?php echo esc_html($user_name); ?></td>
					<td><?php echo $submit_time ? esc_html($submit_time) : get_the_date('Y-m-d H:i'); ?></td>
					<td><span class="navai-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></td>
					<td>
						<a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="button button-small"><?php esc_html_e('编辑', 'navai'); ?></a>
						<?php if ('pending' === $current_status ) : ?>
							<form method="post" action="" style="display:inline;">
								<?php wp_nonce_field('navai_review_nonce'); ?>
								<input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
								<button type="submit" name="navai_review_action" value="approve" class="button button-small button-primary"><?php esc_html_e('通过', 'navai'); ?></button>
								<button type="submit" name="navai_review_action" value="reject" class="button button-small"><?php esc_html_e('拒绝', 'navai'); ?></button>
							</form>
						<?php endif; ?>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>

		<!-- 分页 -->
		<?php
		$total_pages = $query->max_num_pages;
		if ($total_pages > 1) :
		?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php
				echo paginate_links(array(
					'base'      => add_query_arg('paged', '%#%'),
					'format'    => '',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
					'total'     => $total_pages,
					'current'   => $args['paged'],
				));
				?>
			</div>
		</div>
		<?php endif; ?>

		<?php wp_reset_postdata(); ?>

		<?php else : ?>
		<p><?php esc_html_e('暂无需要审核的网站', 'navai'); ?></p>
		<?php endif; ?>
	</div>

	<style>
	.navai-status-badge {
		display: inline-block;
		padding: 2px 8px;
		border-radius: 4px;
		font-size: 12px;
		font-weight: 500;
	}
	.status-pending {
		background: #fff3cd;
		color: #856404;
	}
	.status-approved {
		background: #d4edda;
		color: #155724;
	}
	.status-rejected {
		background: #f8d7da;
		color: #721c24;
	}
	</style>
	<?php
}

/**
 * ============================================================================
 * 15. 评论登录拦截 - 在 wp_die 之前重定向
 * ============================================================================
 */

/**
 * 拦截未登录用户的评论提交
 *
 * 在 init 钩子中提前检查：如果 WordPress 设置了「用户必须登录才能评论」，
 * 且当前请求是 POST 到 wp-comments-post.php 的评论提交，
 * 则在 WordPress 核心调用 wp_die() 之前重定向回原文章页，
 * 并附带查询参数让模板显示登录提示。
 *
 * 此方案比 wp_die_handler 过滤器更可靠，因为：
 * 1. 不依赖私有函数 _default_wp_die_handler
 * 2. 在 WordPress 核心检查之前执行，完全避免 wp_die 页面
 * 3. 重定向回文章页，用户体验更好
 *
 * @since 1.29.6
 *
 * @return void
 */
function navai_intercept_comment_login_required() {
	// 仅处理 POST 请求
	if ('POST' !== $_SERVER['REQUEST_METHOD']) {
		return;
	}

	// 检测是否为 wp-comments-post.php 请求
	$script = '';
	if (isset($_SERVER['SCRIPT_NAME'])) {
		$script = sanitize_text_field(wp_unslash($_SERVER['SCRIPT_NAME']));
	} elseif (isset($_SERVER['PHP_SELF'])) {
		$script = sanitize_text_field(wp_unslash($_SERVER['PHP_SELF']));
	}

	if (empty($script) || false === strpos($script, 'wp-comments-post.php')) {
		return;
	}

	// 如果不需要登录才能评论，不拦截
	if ( ! get_option('comment_registration')) {
		return;
	}

	// 如果已登录，不拦截
	if (is_user_logged_in()) {
		return;
	}

	// 获取文章ID用于重定向
	$post_id  = isset($_POST['comment_post_ID']) ? intval($_POST['comment_post_ID']) : 0;
	$back_url = $post_id ? get_permalink($post_id) : home_url('/');
	if (empty($back_url)) {
		$back_url = home_url('/');
	}

	// 附带查询参数，让模板显示登录提示
	$back_url = add_query_arg('navai_comment_login', '1', $back_url);

	// 重定向并退出，阻止 WordPress 后续执行 wp_die()
	wp_safe_redirect($back_url, 302);
	exit;
}
add_action('init', 'navai_intercept_comment_login_required', 1);

/**
 * 在文章页显示评论登录提示消息
 *
 * 当用户被从 wp-comments-post.php 重定向回来时，
 * URL 中带有 navai_comment_login=1 参数。
 * 此函数检测该参数并在评论区域上方显示提示消息。
 *
 * @since 1.29.6
 *
 * @return void
 */
function navai_comment_login_notice() {
	if ( ! isset($_GET['navai_comment_login']) || '1' !== $_GET['navai_comment_login']) {
		return;
	}
	?>
	<div class="navai-comment-notice">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
			<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
			<path d="M7 11V7a5 5 0 0 1 10 0v4"/>
		</svg>
		<span><?php esc_html_e('请先登录后再发表评论。', 'navai'); ?></span>
		<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="navai-comment-notice-login">
			<?php esc_html_e('立即登录', 'navai'); ?>
		</a>
	</div>
	<?php
}
add_action('comment_form_before', 'navai_comment_login_notice', 5);
