<?php
/**
 * NavAi 后台综合管理 Dashboard
 *
 * @package NavAi
 * @version 1.29.56
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * 注册 Dashboard 菜单
 */
function navai_dashboard_menu() {
	add_submenu_page(
		'edit.php?post_type=ai_tool',
		__('导航概览', 'navai'),
		__('导航概览', 'navai'),
		'manage_options',
		'navai-dashboard',
		'navai_dashboard_page'
	);
}
add_action('admin_menu', 'navai_dashboard_menu', 5);

/**
 * 调整菜单顺序：导航概览排在网址管理下第一位
 */
function navai_dashboard_menu_order($menu_ord) {
	global $submenu;
	if (!isset($submenu['edit.php?post_type=ai_tool'])) {
		return $menu_ord;
	}
	$items = $submenu['edit.php?post_type=ai_tool'];
	$dashboard = null;
	$rest = array();
	foreach ($items as $item) {
		if (isset($item[2]) && $item[2] === 'navai-dashboard') {
			$dashboard = $item;
		} else {
			$rest[] = $item;
		}
	}
	if ($dashboard) {
		array_splice($rest, 0, 0, array($dashboard));
		$submenu['edit.php?post_type=ai_tool'] = $rest;
	}
	return $menu_ord;
}
add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'navai_dashboard_menu_order');

/**
 * 获取统计数据
 */
function navai_dashboard_get_stats() {
	global $wpdb;

	// 网址总数
	$total_sites = wp_count_posts('ai_tool');
	$published = isset($total_sites->publish) ? intval($total_sites->publish) : 0;
	$pending = isset($total_sites->pending) ? intval($total_sites->pending) : 0;
	$draft = isset($total_sites->draft) ? intval($total_sites->draft) : 0;

	// 今日新增
	$today = wp_date('Y-m-d 00:00:00');
	$today_count = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'ai_tool' AND post_date >= %s AND post_status IN ('publish', 'pending', 'draft')",
		$today
	));

	// 昨日新增（用于环比）
	$yesterday = wp_date('Y-m-d 00:00:00', strtotime('-1 day'));
	$yesterday_count = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'ai_tool' AND post_date >= %s AND post_date < %s AND post_status IN ('publish', 'pending', 'draft')",
		$yesterday, $today
	));

	// 总点击量
	$total_clicks = $wpdb->get_var(
		"SELECT COALESCE(SUM(CAST(meta_value AS UNSIGNED)), 0) FROM {$wpdb->postmeta} WHERE meta_key = '_click_count'"
	);

	// 总浏览量
	$total_views = $wpdb->get_var(
		"SELECT COALESCE(SUM(CAST(meta_value AS UNSIGNED)), 0) FROM {$wpdb->postmeta} WHERE meta_key = '_post_views'"
	);

	// 今日访问量（通过 _post_views 估算：今日有浏览记录的文章数 × 平均浏览）
	// 更精确的方式是使用 transient 记录每日访问
	$today_visits = intval(get_transient('navai_today_visits_' . wp_date('Y-m-d')));
	if ($today_visits === 0) {
		// 回退：估算今日访问 = 今日点击数
		$today_visits = intval(get_transient('navai_today_clicks_' . wp_date('Y-m-d')));
	}

	// 注册用户数
	$total_users = count_users();
	$total_users_count = isset($total_users['total_users']) ? $total_users['total_users'] : 0;

	// 分类总数
	$category_count = wp_count_terms(array(
		'taxonomy' => 'ai_category',
		'hide_empty' => false,
	));
	$category_count = is_wp_error($category_count) ? 0 : intval($category_count);

	// 待审核评论数
	$pending_comments = wp_count_comments();
	$pending_comments_count = isset($pending_comments->moderated) ? intval($pending_comments->moderated) : 0;

	// 有图标的网址数
	$with_icon = $wpdb->get_var(
		"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_site_icon_url' AND meta_value != ''"
	);

	// 图标覆盖率
	$icon_coverage = $published > 0 ? round(($with_icon / $published) * 100, 1) : 0;

	// 环比变化
	$daily_change = $yesterday_count > 0 ? round((($today_count - $yesterday_count) / $yesterday_count) * 100, 1) : ($today_count > 0 ? 100 : 0);

	return array(
		'total_sites' => $published,
		'pending_sites' => $pending,
		'draft_sites' => $draft,
		'today_new' => intval($today_count),
		'yesterday_new' => intval($yesterday_count),
		'daily_change' => $daily_change,
		'total_clicks' => intval($total_clicks),
		'total_views' => intval($total_views),
		'today_visits' => $today_visits,
		'total_users' => $total_users_count,
		'category_count' => $category_count,
		'pending_comments' => $pending_comments_count,
		'icon_coverage' => $icon_coverage,
		'with_icon' => intval($with_icon),
	);
}

/**
 * 记录每日访问量（在 template_redirect 中调用）
 */
function navai_dashboard_track_daily_visit() {
	if (is_admin()) return;

	$date_key = wp_date('Y-m-d');
	$transient_key = 'navai_today_visits_' . $date_key;
	$current = intval(get_transient($transient_key));
	set_transient($transient_key, $current + 1, DAY_IN_SECONDS * 2);

	// 同时记录每日历史数据（用于趋势图）
	$history = get_option('navai_daily_stats', array());
	$today_data = isset($history[$date_key]) ? $history[$date_key] : array('visits' => 0, 'clicks' => 0, 'new_sites' => 0);
	$today_data['visits'] = $current + 1;
	$history[$date_key] = $today_data;

	// 只保留最近90天
	$cutoff = wp_date('Y-m-d', strtotime('-90 days'));
	foreach ($history as $date => $data) {
		if ($date < $cutoff) {
			unset($history[$date]);
		}
	}
	update_option('navai_daily_stats', $history);
}
add_action('template_redirect', 'navai_dashboard_track_daily_visit', 5);

/**
 * Dashboard 页面渲染
 */
function navai_dashboard_page() {
	$stats = navai_dashboard_get_stats();
	$ajax_url = admin_url('admin-ajax.php');
	$nonce = wp_create_nonce('navai_dashboard_nonce');
	?>
	<div class="wrap navai-dashboard">
		<h1 class="navai-dash-title">
			<span class="dashicons dashicons-dashboard"></span>
			<?php esc_html_e('NavAi 导航概览', 'navai'); ?>
		</h1>

		<!-- 统计卡片 -->
		<div class="navai-dash-stats-grid">
			<div class="navai-dash-stat-card">
				<div class="navai-dash-stat-icon" style="background:#e7f3fe;color:#2271b1;"><span class="dashicons dashicons-admin-links"></span></div>
				<div class="navai-dash-stat-body">
					<div class="navai-dash-stat-num"><?php echo esc_html($stats['total_sites']); ?></div>
					<div class="navai-dash-stat-label"><?php esc_html_e('收录网址', 'navai'); ?></div>
				</div>
			</div>
			<div class="navai-dash-stat-card">
				<div class="navai-dash-stat-icon" style="background:#fef7e7;color:#dba617;"><span class="dashicons dashicons-clock"></span></div>
				<div class="navai-dash-stat-body">
					<div class="navai-dash-stat-num"><?php echo esc_html($stats['pending_sites']); ?></div>
					<div class="navai-dash-stat-label"><?php esc_html_e('待审核网址', 'navai'); ?></div>
				</div>
			</div>
			<div class="navai-dash-stat-card">
				<div class="navai-dash-stat-icon" style="background:#edfaef;color:#00a32a;"><span class="dashicons dashicons-plus-alt"></span></div>
				<div class="navai-dash-stat-body">
					<div class="navai-dash-stat-num"><?php echo esc_html($stats['today_new']); ?></div>
					<div class="navai-dash-stat-label"><?php esc_html_e('今日新增', 'navai'); ?></div>
					<?php if ($stats['daily_change'] != 0): ?>
					<div class="navai-dash-stat-trend <?php echo $stats['daily_change'] > 0 ? 'up' : 'down'; ?>">
						<?php echo $stats['daily_change'] > 0 ? '&#9650;' : '&#9660;'; ?> <?php echo abs($stats['daily_change']); ?>%
					</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="navai-dash-stat-card">
				<div class="navai-dash-stat-icon" style="background:#fef0f0;color:#d63638;"><span class="dashicons dashicons-chart-bar"></span></div>
				<div class="navai-dash-stat-body">
					<div class="navai-dash-stat-num"><?php echo esc_html(number_format($stats['total_clicks'])); ?></div>
					<div class="navai-dash-stat-label"><?php esc_html_e('总点击量', 'navai'); ?></div>
				</div>
			</div>
			<div class="navai-dash-stat-card">
				<div class="navai-dash-stat-icon" style="background:#f0f7ff;color:#2271b1;"><span class="dashicons dashicons-visibility"></span></div>
				<div class="navai-dash-stat-body">
					<div class="navai-dash-stat-num"><?php echo esc_html(number_format($stats['total_views'])); ?></div>
					<div class="navai-dash-stat-label"><?php esc_html_e('总浏览量', 'navai'); ?></div>
				</div>
			</div>
			<div class="navai-dash-stat-card">
				<div class="navai-dash-stat-icon" style="background:#fef7e7;color:#996800;"><span class="dashicons dashicons-calendar-alt"></span></div>
				<div class="navai-dash-stat-body">
					<div class="navai-dash-stat-num"><?php echo esc_html(number_format($stats['today_visits'])); ?></div>
					<div class="navai-dash-stat-label"><?php esc_html_e('今日访问', 'navai'); ?></div>
				</div>
			</div>
			<div class="navai-dash-stat-card">
				<div class="navai-dash-stat-icon" style="background:#edfaef;color:#00a32a;"><span class="dashicons dashicons-admin-users"></span></div>
				<div class="navai-dash-stat-body">
					<div class="navai-dash-stat-num"><?php echo esc_html($stats['total_users']); ?></div>
					<div class="navai-dash-stat-label"><?php esc_html_e('注册用户', 'navai'); ?></div>
				</div>
			</div>
			<div class="navai-dash-stat-card">
				<div class="navai-dash-stat-icon" style="background:#f0edff;color:#6366f1;"><span class="dashicons dashicons-category"></span></div>
				<div class="navai-dash-stat-body">
					<div class="navai-dash-stat-num"><?php echo esc_html($stats['category_count']); ?></div>
					<div class="navai-dash-stat-label"><?php esc_html_e('分类数量', 'navai'); ?></div>
				</div>
			</div>
		</div>

		<!-- 主要内容区 -->
		<div class="navai-dash-main-row">
			<!-- 左列：访问趋势 + 热门网站 -->
			<div class="navai-dash-col-left">
				<!-- 访问趋势 -->
				<div class="navai-dash-panel">
					<div class="navai-dash-panel-header">
						<h2><span class="dashicons dashicons-chart-line"></span> <?php esc_html_e('访问趋势', 'navai'); ?></h2>
						<div class="navai-dash-period-tabs">
							<button class="navai-dash-tab active" data-period="7"><?php esc_html_e('近7天', 'navai'); ?></button>
							<button class="navai-dash-tab" data-period="30"><?php esc_html_e('近30天', 'navai'); ?></button>
							<button class="navai-dash-tab" data-period="90"><?php esc_html_e('近90天', 'navai'); ?></button>
						</div>
					</div>
					<div class="navai-dash-panel-body">
						<canvas id="navai-trend-chart" height="200"></canvas>
					</div>
				</div>

				<!-- 热门网站 Top 10 -->
				<div class="navai-dash-panel">
					<div class="navai-dash-panel-header">
						<h2><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e('热门网站 Top 10', 'navai'); ?></h2>
						<a href="<?php echo esc_url(admin_url('edit.php?post_type=ai_tool&orderby=navai_visits&order=desc')); ?>" class="navai-dash-link"><?php esc_html_e('查看全部', 'navai'); ?></a>
					</div>
					<div class="navai-dash-panel-body" id="navai-top-sites">
						<div class="navai-dash-loading"><span class="dashicons dashicons-update-alt spinning"></span> <?php esc_html_e('加载中...', 'navai'); ?></div>
					</div>
				</div>

				<!-- 最新操作日志 -->
				<div class="navai-dash-panel">
					<div class="navai-dash-panel-header">
						<h2><span class="dashicons dashicons-list-view"></span> <?php esc_html_e('最新操作日志', 'navai'); ?></h2>
					</div>
					<div class="navai-dash-panel-body" id="navai-recent-logs">
						<div class="navai-dash-loading"><span class="dashicons dashicons-update-alt spinning"></span> <?php esc_html_e('加载中...', 'navai'); ?></div>
					</div>
				</div>
			</div>

			<!-- 右列：分类分布 + 快捷操作 + 待审核 -->
			<div class="navai-dash-col-right">
				<!-- 快捷操作 -->
				<div class="navai-dash-panel">
					<div class="navai-dash-panel-header">
						<h2><span class="dashicons dashicons-screenoptions"></span> <?php esc_html_e('快捷操作', 'navai'); ?></h2>
					</div>
					<div class="navai-dash-panel-body">
						<div class="navai-dash-quick-actions">
							<a href="<?php echo esc_url(admin_url('post-new.php?post_type=ai_tool')); ?>" class="navai-dash-quick-btn">
								<span class="dashicons dashicons-plus-alt"></span>
								<span><?php esc_html_e('添加网址', 'navai'); ?></span>
							</a>
							<a href="<?php echo esc_url(admin_url('edit.php?post_type=ai_tool&page=navai-batch-add')); ?>" class="navai-dash-quick-btn">
								<span class="dashicons dashicons-download"></span>
								<span><?php esc_html_e('批量导入', 'navai'); ?></span>
							</a>
							<a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=ai_category&post_type=ai_tool')); ?>" class="navai-dash-quick-btn">
								<span class="dashicons dashicons-category"></span>
								<span><?php esc_html_e('添加分类', 'navai'); ?></span>
							</a>
							<a href="<?php echo esc_url(admin_url('edit.php?post_type=ai_tool&page=navai-site-review')); ?>" class="navai-dash-quick-btn">
								<span class="dashicons dashicons-yes-alt"></span>
								<span><?php esc_html_e('网址审核', 'navai'); ?></span>
								<?php if ($stats['pending_sites'] > 0): ?>
								<span class="navai-dash-badge"><?php echo esc_html($stats['pending_sites']); ?></span>
								<?php endif; ?>
							</a>
							<a href="<?php echo esc_url(admin_url('admin.php?page=navai-settings')); ?>" class="navai-dash-quick-btn">
								<span class="dashicons dashicons-admin-generic"></span>
								<span><?php esc_html_e('主题设置', 'navai'); ?></span>
							</a>
							<a href="<?php echo esc_url(admin_url('edit-comments.php?comment_status=moderated')); ?>" class="navai-dash-quick-btn">
								<span class="dashicons dashicons-admin-comments"></span>
								<span><?php esc_html_e('评论审核', 'navai'); ?></span>
								<?php if ($stats['pending_comments'] > 0): ?>
								<span class="navai-dash-badge"><?php echo esc_html($stats['pending_comments']); ?></span>
								<?php endif; ?>
							</a>
							<a href="#" class="navai-dash-quick-btn" id="navai-dash-export">
								<span class="dashicons dashicons-database-export"></span>
								<span><?php esc_html_e('导出数据', 'navai'); ?></span>
							</a>
							<a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="navai-dash-quick-btn">
								<span class="dashicons dashicons-external"></span>
								<span><?php esc_html_e('访问前台', 'navai'); ?></span>
							</a>
						</div>
					</div>
				</div>

				<!-- 分类分布饼图 -->
				<div class="navai-dash-panel">
					<div class="navai-dash-panel-header">
						<h2><span class="dashicons dashicons-chart-pie"></span> <?php esc_html_e('分类内容分布', 'navai'); ?></h2>
					</div>
					<div class="navai-dash-panel-body" id="navai-category-chart-wrap">
						<div class="navai-dash-cat-chart-row">
							<canvas id="navai-category-chart" height="180"></canvas>
							<div id="navai-category-legend" class="navai-dash-cat-legend"></div>
						</div>
					</div>
				</div>

				<!-- 网站状态统计 -->
				<div class="navai-dash-panel">
					<div class="navai-dash-panel-header">
						<h2><span class="dashicons dashicons-info"></span> <?php esc_html_e('网站状态', 'navai'); ?></h2>
					</div>
					<div class="navai-dash-panel-body">
						<div class="navai-dash-status-bar">
							<div class="navai-dash-status-item published">
								<span class="navai-dash-status-dot"></span>
								<span class="navai-dash-status-num"><?php echo esc_html($stats['total_sites']); ?></span>
								<span class="navai-dash-status-text"><?php esc_html_e('已发布', 'navai'); ?></span>
							</div>
							<div class="navai-dash-status-item pending">
								<span class="navai-dash-status-dot"></span>
								<span class="navai-dash-status-num"><?php echo esc_html($stats['pending_sites']); ?></span>
								<span class="navai-dash-status-text"><?php esc_html_e('待审核', 'navai'); ?></span>
							</div>
							<div class="navai-dash-status-item draft">
								<span class="navai-dash-status-dot"></span>
								<span class="navai-dash-status-num"><?php echo esc_html($stats['draft_sites']); ?></span>
								<span class="navai-dash-status-text"><?php esc_html_e('草稿', 'navai'); ?></span>
							</div>
						</div>
						<div class="navai-dash-info-row">
							<div class="navai-dash-info-item">
								<span class="dashicons dashicons-format-image"></span>
								<span><?php printf(__('图标覆盖率: %s%%', 'navai'), esc_html($stats['icon_coverage'])); ?></span>
							</div>
							<div class="navai-dash-info-item">
								<span class="dashicons dashicons-admin-comments"></span>
								<span><?php printf(__('待审评论: %s', 'navai'), esc_html($stats['pending_comments'])); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- 死链检测 -->
				<div class="navai-dash-panel">
					<div class="navai-dash-panel-header">
						<h2><span class="dashicons dashicons-warning"></span> <?php esc_html_e('死链检测', 'navai'); ?></h2>
						<button class="button button-small navai-dash-check-deadlinks" id="navai-check-deadlinks"><?php esc_html_e('开始检测', 'navai'); ?></button>
					</div>
					<div class="navai-dash-panel-body" id="navai-deadlinks-result">
						<p class="description"><?php esc_html_e('点击"开始检测"检查最近50个网址的可用性。', 'navai'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<style>
	/* === Dashboard 样式 === */
	.navai-dashboard { max-width: 1400px; }
	.navai-dash-title { display: flex; align-items: center; gap: 8px; font-size: 22px; margin-bottom: 20px; }
	.navai-dash-title .dashicons { font-size: 28px; width: 28px; height: 28px; color: #2271b1; }

	/* 统计卡片 */
	.navai-dash-stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px; }
	.navai-dash-stat-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; display: flex; align-items: center; gap: 12px; transition: box-shadow 0.2s; }
	.navai-dash-stat-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
	.navai-dash-stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
	.navai-dash-stat-icon .dashicons { font-size: 24px; width: 24px; height: 24px; }
	.navai-dash-stat-num { font-size: 24px; font-weight: 700; line-height: 1.2; color: #1d2327; }
	.navai-dash-stat-label { font-size: 12px; color: #646970; margin-top: 2px; }
	.navai-dash-stat-trend { font-size: 11px; margin-top: 2px; }
	.navai-dash-stat-trend.up { color: #00a32a; }
	.navai-dash-stat-trend.down { color: #d63638; }

	/* 主布局 */
	.navai-dash-main-row { display: flex; gap: 16px; align-items: flex-start; }
	.navai-dash-col-left { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 16px; }
	.navai-dash-col-right { width: 360px; flex-shrink: 0; display: flex; flex-direction: column; gap: 16px; }
	@media (max-width: 1200px) {
		.navai-dash-main-row { flex-direction: column; }
		.navai-dash-col-right { width: 100%; }
	}

	/* 面板 */
	.navai-dash-panel { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
	.navai-dash-panel-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #f0f0f1; }
	.navai-dash-panel-header h2 { font-size: 14px; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 6px; }
	.navai-dash-panel-header h2 .dashicons { font-size: 18px; width: 18px; height: 18px; color: #2271b1; }
	.navai-dash-panel-body { padding: 16px; }
	.navai-dash-link { font-size: 12px; color: #2271b1; text-decoration: none; }
	.navai-dash-link:hover { text-decoration: underline; }
	.navai-dash-loading { text-align: center; padding: 20px; color: #646970; font-size: 13px; }
	.navai-dash-loading .dashicons { font-size: 16px; vertical-align: middle; }

	/* 趋势图 tabs */
	.navai-dash-period-tabs { display: flex; gap: 2px; }
	.navai-dash-tab { background: none; border: 1px solid #dcdcde; border-radius: 4px; padding: 3px 10px; font-size: 12px; cursor: pointer; color: #646970; }
	.navai-dash-tab.active { background: #2271b1; color: #fff; border-color: #2271b1; }

	/* 热门网站列表 */
	.navai-dash-top-site { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f6f7f7; }
	.navai-dash-top-site:last-child { border-bottom: none; }
	.navai-dash-top-rank { width: 24px; height: 24px; border-radius: 50%; background: #f0f0f1; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #646970; flex-shrink: 0; }
	.navai-dash-top-rank.gold { background: #ffd700; color: #fff; }
	.navai-dash-top-rank.silver { background: #c0c0c0; color: #fff; }
	.navai-dash-top-rank.bronze { background: #cd7f32; color: #fff; }
	.navai-dash-top-icon { width: 28px; height: 28px; border-radius: 6px; flex-shrink: 0; object-fit: cover; }
	.navai-dash-top-info { flex: 1; min-width: 0; }
	.navai-dash-top-name { font-size: 13px; font-weight: 500; color: #1d2327; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
	.navai-dash-top-url { font-size: 11px; color: #999; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
	.navai-dash-top-views { text-align: right; flex-shrink: 0; }
	.navai-dash-top-views-num { font-size: 14px; font-weight: 700; color: #2271b1; }
	.navai-dash-top-views-label { font-size: 10px; color: #999; }

	/* 快捷操作 */
	.navai-dash-quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
	.navai-dash-quick-btn { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 12px 8px; background: #f6f7f7; border-radius: 8px; text-decoration: none; color: #3c434a; font-size: 12px; transition: all 0.2s; position: relative; }
	.navai-dash-quick-btn:hover { background: #e7f3fe; color: #2271b1; }
	.navai-dash-quick-btn .dashicons { font-size: 22px; width: 22px; height: 22px; }
	.navai-dash-badge { position: absolute; top: 4px; right: 4px; background: #d63638; color: #fff; font-size: 10px; font-weight: 700; border-radius: 10px; padding: 1px 6px; min-width: 18px; text-align: center; }

	/* 网站状态 */
	.navai-dash-status-bar { display: flex; gap: 16px; justify-content: space-around; margin-bottom: 12px; }
	.navai-dash-status-item { display: flex; flex-direction: column; align-items: center; gap: 4px; }
	.navai-dash-status-dot { width: 10px; height: 10px; border-radius: 50; }
	.navai-dash-status-item.published .navai-dash-status-dot { background: #00a32a; }
	.navai-dash-status-item.pending .navai-dash-status-dot { background: #dba617; }
	.navai-dash-status-item.draft .navai-dash-status-dot { background: #999; }
	.navai-dash-status-num { font-size: 20px; font-weight: 700; }
	.navai-dash-status-text { font-size: 11px; color: #646970; }
	.navai-dash-info-row { display: flex; flex-direction: column; gap: 6px; padding-top: 10px; border-top: 1px solid #f6f7f7; }
	.navai-dash-info-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #646970; }
	.navai-dash-info-item .dashicons { font-size: 16px; width: 16px; height: 16px; color: #2271b1; }

	/* 操作日志 */
	.navai-dash-log-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f6f7f7; font-size: 12px; }
	.navai-dash-log-item:last-child { border-bottom: none; }
	.navai-dash-log-time { color: #999; white-space: nowrap; width: 100px; flex-shrink: 0; }
	.navai-dash-log-action { color: #1d2327; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
	.navai-dash-log-empty { text-align: center; padding: 20px; color: #999; font-size: 13px; }

	/* 死链结果 */
	.navai-dash-deadlink-item { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f6f7f7; font-size: 12px; }
	.navai-dash-deadlink-url { color: #1d2327; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.navai-dash-deadlink-status { flex-shrink: 0; margin-left: 8px; font-weight: 600; }
	.navai-dash-deadlink-status.ok { color: #00a32a; }
	.navai-dash-deadlink-status.dead { color: #d63638; }
	.navai-dash-deadlink-status.checking { color: #2271b1; }

	/* 分类分布图 */
	.navai-dash-cat-chart-row { display: flex; align-items: center; gap: 12px; }
	.navai-dash-cat-chart-row canvas { flex-shrink: 0; width: 160px !important; height: 160px !important; }
	.navai-dash-cat-legend { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
	.navai-dash-cat-legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; }
	.navai-dash-cat-legend-color { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }
	.navai-dash-cat-legend-name { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1d2327; }
	.navai-dash-cat-legend-pct { flex-shrink: 0; color: #646970; font-weight: 600; }
	.navai-dash-cat-legend-empty { text-align: center; padding: 20px; color: #999; font-size: 13px; }

	/* spinning 动画 */
	@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
	.spinning { animation: spin 1s linear infinite; }
	</style>

	<script>
	(function($) {
		'use strict';

		var ajaxUrl = '<?php echo esc_js($ajax_url); ?>';
		var nonce = '<?php echo esc_js($nonce); ?>';

		// === Chart.js 轻量内联实现 ===
		// 如果没有 Chart.js，用 Canvas 手绘简易图表

		function drawLineChart(canvas, labels, datasets) {
			var ctx = canvas.getContext('2d');
			var dpr = window.devicePixelRatio || 1;
			var w = canvas.offsetWidth;
			var h = canvas.offsetHeight || 200;
			canvas.width = w * dpr;
			canvas.height = h * dpr;
			ctx.scale(dpr, dpr);
			ctx.clearRect(0, 0, w, h);

			var pad = { top: 10, right: 10, bottom: 30, left: 40 };
			var cw = w - pad.left - pad.right;
			var ch = h - pad.top - pad.bottom;

			// 合并所有数据求最大值
			var allVals = [];
			datasets.forEach(function(ds) { allVals = allVals.concat(ds.data); });
			var maxVal = Math.max.apply(null, allVals.concat([1]));
			var niceMax = Math.ceil(maxVal * 1.1);

			// 网格线 + Y轴标签
			ctx.strokeStyle = '#f0f0f1';
			ctx.fillStyle = '#646970';
			ctx.font = '10px sans-serif';
			ctx.lineWidth = 1;
			for (var i = 0; i <= 4; i++) {
				var y = pad.top + (ch / 4) * i;
				ctx.beginPath();
				ctx.moveTo(pad.left, y);
				ctx.lineTo(pad.left + cw, y);
				ctx.stroke();
				var val = Math.round(niceMax - (niceMax / 4) * i);
				ctx.textAlign = 'right';
				ctx.fillText(val, pad.left - 6, y + 3);
			}

			// X轴标签
			ctx.textAlign = 'center';
			var step = Math.ceil(labels.length / 8);
			labels.forEach(function(label, i) {
				if (i % step === 0 || i === labels.length - 1) {
					var x = pad.left + (cw / (labels.length - 1)) * i;
					ctx.fillText(label, x, h - pad.bottom + 14);
				}
			});

			// 画折线
			datasets.forEach(function(ds) {
				ctx.strokeStyle = ds.borderColor || '#2271b1';
				ctx.fillStyle = ds.backgroundColor || 'rgba(34,113,177,0.1)';
				ctx.lineWidth = 2;
				ctx.beginPath();
				ds.data.forEach(function(val, i) {
					var x = pad.left + (cw / (ds.data.length - 1)) * i;
					var y = pad.top + ch - (val / niceMax) * ch;
					if (i === 0) ctx.moveTo(x, y);
					else ctx.lineTo(x, y);
				});
				ctx.stroke();
				// 填充
				ctx.lineTo(pad.left + cw, pad.top + ch);
				ctx.lineTo(pad.left, pad.top + ch);
				ctx.closePath();
				ctx.fill();
				// 数据点
				ctx.fillStyle = ds.borderColor || '#2271b1';
				ds.data.forEach(function(val, i) {
					var x = pad.left + (cw / (ds.data.length - 1)) * i;
					var y = pad.top + ch - (val / niceMax) * ch;
					ctx.beginPath();
					ctx.arc(x, y, 2.5, 0, Math.PI * 2);
					ctx.fill();
				});
			});

			// 图例
			var legendY = 4;
			datasets.forEach(function(ds, i) {
				var legendX = pad.left + i * 100;
				ctx.fillStyle = ds.borderColor || '#2271b1';
				ctx.fillRect(legendX, legendY, 12, 3);
				ctx.fillStyle = '#646970';
				ctx.font = '11px sans-serif';
				ctx.textAlign = 'left';
				ctx.fillText(ds.label || '', legendX + 16, legendY + 6);
			});
		}

		function drawPieChart(canvas, labels, data, colors) {
			var ctx = canvas.getContext('2d');
			var dpr = window.devicePixelRatio || 1;
			var w = 160;
			var h = 160;
			canvas.width = w * dpr;
			canvas.height = h * dpr;
			ctx.scale(dpr, dpr);
			ctx.clearRect(0, 0, w, h);

			var cx = w / 2;
			var cy = h / 2;
			var r = Math.min(w, h) / 2 - 4;

			var total = data.reduce(function(a, b) { return a + b; }, 0);
			if (total === 0) {
				ctx.fillStyle = '#999';
				ctx.font = '13px sans-serif';
				ctx.textAlign = 'center';
				ctx.fillText('暂无数据', w / 2, h / 2);
				return;
			}

			var startAngle = -Math.PI / 2;
			data.forEach(function(val, i) {
				var angle = (val / total) * Math.PI * 2;
				ctx.beginPath();
				ctx.moveTo(cx, cy);
				ctx.arc(cx, cy, r, startAngle, startAngle + angle);
				ctx.closePath();
				ctx.fillStyle = colors[i % colors.length];
				ctx.fill();
				startAngle += angle;
			});

			// 中心白色圆（环形效果）
			ctx.beginPath();
			ctx.arc(cx, cy, r * 0.5, 0, Math.PI * 2);
			ctx.fillStyle = '#fff';
			ctx.fill();
		}

		var chartColors = ['#2271b1', '#00a32a', '#dba617', '#d63638', '#6366f1', '#996800', '#8eb4ff', '#00b8b8', '#ff6b6b', '#a78bfa', '#34d399', '#fbbf24'];

		// === 加载访问趋势 ===
		function loadTrend(period) {
			$.ajax({
				url: ajaxUrl, type: 'POST',
				data: { action: 'navai_dashboard_trend', nonce: nonce, period: period }
			}).done(function(resp) {
				if (resp.success) {
					var d = resp.data;
					drawLineChart(document.getElementById('navai-trend-chart'), d.labels, [
						{ label: '访问量', data: d.visits, borderColor: '#2271b1', backgroundColor: 'rgba(34,113,177,0.1)' },
						{ label: '点击量', data: d.clicks, borderColor: '#00a32a', backgroundColor: 'rgba(0,163,42,0.05)' }
					]);
				}
			});
		}
		loadTrend(7);

		$('.navai-dash-tab').on('click', function() {
			$('.navai-dash-tab').removeClass('active');
			$(this).addClass('active');
			loadTrend($(this).data('period'));
		});

		// === 加载热门网站 ===
		$.ajax({
			url: ajaxUrl, type: 'POST',
			data: { action: 'navai_dashboard_top_sites', nonce: nonce }
		}).done(function(resp) {
			if (resp.success && resp.data.length > 0) {
				var html = '';
				resp.data.forEach(function(site, i) {
					var rankClass = i === 0 ? 'gold' : (i === 1 ? 'silver' : (i === 2 ? 'bronze' : ''));
					var icon = site.icon ? '<img class="navai-dash-top-icon" src="' + site.icon + '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';"><span class="navai-dash-top-icon" style="display:none;background:#2271b1;color:#fff;border-radius:6px;align-items:center;justify-content:center;font-size:12px;">' + (site.title.charAt(0) || '?') + '</span>' : '<span class="navai-dash-top-icon" style="background:#2271b1;color:#fff;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;">' + (site.title.charAt(0) || '?') + '</span>';
					html += '<div class="navai-dash-top-site">' +
						'<span class="navai-dash-top-rank ' + rankClass + '">' + (i + 1) + '</span>' +
						icon +
						'<div class="navai-dash-top-info">' +
							'<div class="navai-dash-top-name">' + $('<div>').text(site.title).html() + '</div>' +
							'<div class="navai-dash-top-url">' + site.host + '</div>' +
						'</div>' +
						'<div class="navai-dash-top-views">' +
							'<div class="navai-dash-top-views-num">' + site.views + '</div>' +
							'<div class="navai-dash-top-views-label">浏览</div>' +
						'</div>' +
					'</div>';
				});
				$('#navai-top-sites').html(html);
			} else {
				$('#navai-top-sites').html('<div class="navai-dash-log-empty">暂无数据</div>');
			}
		});

		// === 加载分类分布 ===
		$.ajax({
			url: ajaxUrl, type: 'POST',
			data: { action: 'navai_dashboard_categories', nonce: nonce }
	}).done(function(resp) {
		if (resp.success) {
			var labels = resp.data.labels;
			var counts = resp.data.counts;
			drawPieChart(document.getElementById('navai-category-chart'), labels, counts, chartColors);

			// HTML 图例
			var total = counts.reduce(function(a, b) { return a + b; }, 0);
			var legendHtml = '';
			if (total > 0 && labels.length > 0) {
				labels.forEach(function(label, i) {
					var pct = ((counts[i] / total) * 100).toFixed(1);
					legendHtml += '<div class="navai-dash-cat-legend-item">' +
						'<span class="navai-dash-cat-legend-color" style="background:' + chartColors[i % chartColors.length] + ';"></span>' +
						'<span class="navai-dash-cat-legend-name">' + $('<div>').text(label).html() + '</span>' +
						'<span class="navai-dash-cat-legend-pct">' + counts[i] + ' (' + pct + '%)</span>' +
					'</div>';
				});
			} else {
				legendHtml = '<div class="navai-dash-cat-legend-empty">暂无数据</div>';
			}
			$('#navai-category-legend').html(legendHtml);
		}
	});

		// === 加载操作日志 ===
		$.ajax({
			url: ajaxUrl, type: 'POST',
			data: { action: 'navai_dashboard_logs', nonce: nonce }
		}).done(function(resp) {
			if (resp.success && resp.data.length > 0) {
				var html = '';
				resp.data.forEach(function(log) {
					html += '<div class="navai-dash-log-item">' +
						'<span class="navai-dash-log-time">' + log.time + '</span>' +
						'<span class="navai-dash-log-action">' + $('<div>').text(log.text).html() + '</span>' +
					'</div>';
				});
				$('#navai-recent-logs').html(html);
			} else {
				$('#navai-recent-logs').html('<div class="navai-dash-log-empty">暂无操作日志</div>');
			}
		});

		// === 死链检测 ===
		$('#navai-check-deadlinks').on('click', function() {
			var $btn = $(this);
			$btn.prop('disabled', true).html('<span class="dashicons spinning dashicons-update-alt"></span> 检测中...');
			$('#navai-deadlinks-result').html('<div class="navai-dash-loading"><span class="dashicons spinning dashicons-update-alt"></span> 正在检测（最多50个，可能需要30秒）...</div>');

			$.ajax({
				url: ajaxUrl, type: 'POST',
				timeout: 120000,
				data: { action: 'navai_dashboard_deadlinks', nonce: nonce }
			}).done(function(resp) {
				if (resp.success) {
					var d = resp.data;
					var html = '<div style="margin-bottom:8px;font-size:13px;">检测完成：' + d.total + ' 个网址，正常 ' + d.ok + ' 个，异常 ' + d.dead + ' 个</div>';
					if (d.results.length > 0) {
						d.results.forEach(function(r) {
							html += '<div class="navai-dash-deadlink-item">' +
								'<span class="navai-dash-deadlink-url">' + $('<div>').text(r.url).html() + '</span>' +
								'<span class="navai-dash-deadlink-status ' + (r.alive ? 'ok' : 'dead') + '">' + (r.alive ? '正常' : '异常(' + r.code + ')') + '</span>' +
							'</div>';
						});
					}
					$('#navai-deadlinks-result').html(html);
				} else {
					$('#navai-deadlinks-result').html('<p class="description">' + (resp.data.message || '检测失败') + '</p>');
				}
			}).fail(function() {
				$('#navai-deadlinks-result').html('<p class="description">检测超时或出错</p>');
			}).always(function() {
				$btn.prop('disabled', false).text('重新检测');
			});
		});

		// === 导出数据 ===
		$('#navai-dash-export').on('click', function(e) {
			e.preventDefault();
			window.location.href = ajaxUrl + '?action=navai_dashboard_export&nonce=' + nonce;
		});

	})(jQuery);
	</script>
	<?php
}

// ==================== AJAX 接口 ====================

/**
 * AJAX: 访问趋势数据
 */
function navai_dashboard_ajax_trend() {
	check_ajax_referer('navai_dashboard_nonce', 'nonce');
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => '权限不足'));
	}

	$period = isset($_POST['period']) ? intval($_POST['period']) : 7;
	$period = min(max($period, 1), 90);

	$history = get_option('navai_daily_stats', array());
	$labels = array();
	$visits = array();
	$clicks = array();

	for ($i = $period - 1; $i >= 0; $i--) {
		$date = wp_date('Y-m-d', strtotime("-$i days"));
		$label = wp_date('m/d', strtotime("-$i days"));
		$labels[] = $label;

		$day_data = isset($history[$date]) ? $history[$date] : array();
		$visits[] = isset($day_data['visits']) ? intval($day_data['visits']) : 0;
		$clicks[] = isset($day_data['clicks']) ? intval($day_data['clicks']) : 0;
	}

	wp_send_json_success(array(
		'labels' => $labels,
		'visits' => $visits,
		'clicks' => $clicks,
	));
}
add_action('wp_ajax_navai_dashboard_trend', 'navai_dashboard_ajax_trend');

/**
 * AJAX: 热门网站 Top 10
 */
function navai_dashboard_ajax_top_sites() {
	check_ajax_referer('navai_dashboard_nonce', 'nonce');
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => '权限不足'));
	}

	global $wpdb;
	$results = $wpdb->get_results(
		"SELECT p.ID, p.post_title, pm.meta_value AS views
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_post_views'
		 WHERE p.post_type = 'ai_tool' AND p.post_status = 'publish'
		 ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
		 LIMIT 10"
	);

	$sites = array();
	foreach ($results as $r) {
		$url = get_post_meta($r->ID, '_website_url', true);
		$icon = get_post_meta($r->ID, '_site_icon_url', true);
		$host = $url ? wp_parse_url($url, PHP_URL_HOST) : '';
		$sites[] = array(
			'title' => $r->post_title,
			'host' => $host ?: '',
			'icon' => $icon ?: '',
			'views' => intval($r->views),
		);
	}

	wp_send_json_success($sites);
}
add_action('wp_ajax_navai_dashboard_top_sites', 'navai_dashboard_ajax_top_sites');

/**
 * AJAX: 分类分布
 */
function navai_dashboard_ajax_categories() {
	check_ajax_referer('navai_dashboard_nonce', 'nonce');
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => '权限不足'));
	}

	// 获取所有一级分类（不限制 hide_empty，手动聚合子分类计数）
	$parent_cats = get_terms(array(
		'taxonomy' => 'ai_category',
		'hide_empty' => false,
		'parent' => 0,
		'number' => 0,
	));

	$category_data = array();

	if (!is_wp_error($parent_cats)) {
		foreach ($parent_cats as $parent) {
			// 聚合：一级分类自身计数 + 所有子分类计数
			$total_count = intval($parent->count);

			$children = get_terms(array(
				'taxonomy' => 'ai_category',
				'hide_empty' => false,
				'parent' => $parent->term_id,
				'number' => 0,
			));

			if (!is_wp_error($children)) {
				foreach ($children as $child) {
					$total_count += intval($child->count);
				}
			}

			if ($total_count > 0) {
				$category_data[] = array(
					'name' => $parent->name,
					'count' => $total_count,
				);
			}
		}

		// 按文章数排序
		usort($category_data, function($a, $b) {
			return $b['count'] - $a['count'];
		});

		// 取前12个
		$category_data = array_slice($category_data, 0, 12);
	}

	$labels = array();
	$counts = array();
	foreach ($category_data as $cat) {
		$labels[] = $cat['name'];
		$counts[] = intval($cat['count']);
	}

	wp_send_json_success(array(
		'labels' => $labels,
		'counts' => $counts,
	));
}
add_action('wp_ajax_navai_dashboard_categories', 'navai_dashboard_ajax_categories');

/**
 * AJAX: 最新操作日志
 */
function navai_dashboard_ajax_logs() {
	check_ajax_referer('navai_dashboard_nonce', 'nonce');
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => '权限不足'));
	}

	global $wpdb;
	$results = $wpdb->get_results(
		"SELECT post_title, post_type, post_status, post_modified, post_author, ID
		 FROM {$wpdb->posts}
		 WHERE post_type IN ('ai_tool', 'page') AND post_status IN ('publish', 'pending', 'draft', 'trash')
		 ORDER BY post_modified DESC
		 LIMIT 15"
	);

	$logs = array();
	foreach ($results as $r) {
		$author = get_the_author_meta('display_name', $r->post_author);
		$action = '';
		if ($r->post_status === 'publish') {
			$action = '发布';
		} elseif ($r->post_status === 'pending') {
			$action = '提交审核';
		} elseif ($r->post_status === 'draft') {
			$action = '编辑草稿';
		} elseif ($r->post_status === 'trash') {
			$action = '删除';
		}
		$type_label = $r->post_type === 'ai_tool' ? '网址' : '页面';
		$logs[] = array(
			'time' => wp_date('m-d H:i', strtotime($r->post_modified)),
			'text' => $author . ' ' . $action . ' ' . $type_label . '「' . $r->post_title . '」',
		);
	}

	wp_send_json_success($logs);
}
add_action('wp_ajax_navai_dashboard_logs', 'navai_dashboard_ajax_logs');

/**
 * AJAX: 死链检测
 */
function navai_dashboard_ajax_deadlinks() {
	check_ajax_referer('navai_dashboard_nonce', 'nonce');
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => '权限不足'));
	}

	global $wpdb;
	$sites = $wpdb->get_results(
		"SELECT p.ID, p.post_title, pm.meta_value AS url
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_website_url'
		 WHERE p.post_type = 'ai_tool' AND p.post_status = 'publish' AND pm.meta_value != ''
		 ORDER BY p.post_date DESC
		 LIMIT 50"
	);

	$results = array();
	$ok_count = 0;
	$dead_count = 0;

	foreach ($sites as $site) {
		$url = $site->url;
		$alive = false;
		$code = 0;

		$response = wp_remote_head($url, array(
			'timeout' => 8,
			'sslverify' => false,
			'redirection' => 3,
			'user-agent' => 'Mozilla/5.0 (compatible; NavAi-DeadLink-Checker/1.0)',
		));

		if (is_wp_error($response)) {
			// HEAD 失败，尝试 GET（部分网站不支持 HEAD）
			$response = wp_remote_get($url, array(
				'timeout' => 10,
				'sslverify' => false,
				'redirection' => 3,
			));
		}

		if (!is_wp_error($response)) {
			$code = wp_remote_retrieve_response_code($response);
			$alive = ($code >= 200 && $code < 400);
		}

		if ($alive) {
			$ok_count++;
		} else {
			$dead_count++;
			$results[] = array(
				'url' => $url,
				'title' => $site->post_title,
				'alive' => false,
				'code' => $code ?: '超时',
			);
		}
	}

	wp_send_json_success(array(
		'total' => count($sites),
		'ok' => $ok_count,
		'dead' => $dead_count,
		'results' => array_slice($results, 0, 20),
	));
}
add_action('wp_ajax_navai_dashboard_deadlinks', 'navai_dashboard_ajax_deadlinks');

/**
 * AJAX: 导出数据 CSV
 */
function navai_dashboard_ajax_export() {
	check_ajax_referer('navai_dashboard_nonce', 'nonce');
	if (!current_user_can('manage_options')) {
		wp_die('权限不足');
	}

	$stats = navai_dashboard_get_stats();

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="navai-dashboard-' . wp_date('Y-m-d') . '.csv"');
	header('Pragma: no-cache');
	header('Expires: 0');

	$out = fopen('php://output', 'w');
	// BOM for Excel UTF-8
	fprintf($out, "\xEF\xBB\xBF");

	fputcsv($out, array('指标', '数值'));
	fputcsv($out, array('收录网址总数', $stats['total_sites']));
	fputcsv($out, array('待审核网址数', $stats['pending_sites']));
	fputcsv($out, array('草稿数', $stats['draft_sites']));
	fputcsv($out, array('今日新增', $stats['today_new']));
	fputcsv($out, array('昨日新增', $stats['yesterday_new']));
	fputcsv($out, array('环比变化(%)', $stats['daily_change']));
	fputcsv($out, array('总点击量', $stats['total_clicks']));
	fputcsv($out, array('总浏览量', $stats['total_views']));
	fputcsv($out, array('今日访问', $stats['today_visits']));
	fputcsv($out, array('注册用户数', $stats['total_users']));
	fputcsv($out, array('分类数量', $stats['category_count']));
	fputcsv($out, array('待审核评论', $stats['pending_comments']));
	fputcsv($out, array('图标覆盖率(%)', $stats['icon_coverage']));
	fputcsv($out, array('有图标网址数', $stats['with_icon']));

	// 导出热门网站 Top 20
	global $wpdb;
	$top = $wpdb->get_results(
		"SELECT p.post_title, pm.meta_value AS views, p.ID
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_post_views'
		 WHERE p.post_type = 'ai_tool' AND p.post_status = 'publish'
		 ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
		 LIMIT 20"
	);

	fputcsv($out, array());
	fputcsv($out, array('热门网站排行', '浏览量', '点击量', '网址'));
	foreach ($top as $r) {
		$url = get_post_meta($r->ID, '_website_url', true);
		$clicks = get_post_meta($r->ID, '_click_count', true);
		fputcsv($out, array($r->post_title, $r->views, $clicks ?: 0, $url ?: ''));
	}

	fclose($out);
	exit;
}
add_action('wp_ajax_navai_dashboard_export', 'navai_dashboard_ajax_export');

/**
 * 在点击外链时记录每日点击数
 */
function navai_dashboard_track_daily_click() {
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'navai_nonce')) {
		return;
	}

	$date_key = wp_date('Y-m-d');
	$history = get_option('navai_daily_stats', array());
	$today_data = isset($history[$date_key]) ? $history[$date_key] : array('visits' => 0, 'clicks' => 0, 'new_sites' => 0);
	$today_data['clicks'] = (isset($today_data['clicks']) ? $today_data['clicks'] : 0) + 1;
	$history[$date_key] = $today_data;

	// 只保留最近90天
	$cutoff = wp_date('Y-m-d', strtotime('-90 days'));
	foreach ($history as $date => $data) {
		if ($date < $cutoff) {
			unset($history[$date]);
		}
	}
	update_option('navai_daily_stats', $history);
}
add_action('wp_ajax_navai_track_click', 'navai_dashboard_track_daily_click');
add_action('wp_ajax_nopriv_navai_track_click', 'navai_dashboard_track_daily_click');

/**
 * 在发布新网址时记录每日新增
 */
function navai_dashboard_track_new_site($new_status, $old_status, $post) {
	if ($post->post_type !== 'ai_tool') return;
	if ($new_status !== 'publish') return;
	if ($old_status === 'publish') return; // 不是新发布

	$date_key = wp_date('Y-m-d');
	$history = get_option('navai_daily_stats', array());
	$today_data = isset($history[$date_key]) ? $history[$date_key] : array('visits' => 0, 'clicks' => 0, 'new_sites' => 0);
	$today_data['new_sites'] = (isset($today_data['new_sites']) ? $today_data['new_sites'] : 0) + 1;
	$history[$date_key] = $today_data;
	update_option('navai_daily_stats', $history);
}
add_action('transition_post_status', 'navai_dashboard_track_new_site', 10, 3);
