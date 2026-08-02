<?php
/**
 * 侧边栏模板文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.29.22
 */

if ( ! defined('ABSPATH')) {
	exit;
}

// 清除分类缓存，确保获取最新数据
wp_cache_delete('all_ai_categories', 'terms');

// 获取所有网址分类（包括一级和二级分类）
$all_categories = get_terms(array(
	'taxonomy' => 'ai_category',
	'orderby' => 'name',
	'order' => 'ASC',
	'hide_empty' => false,
	'update_term_meta_cache' => false,
));

// 分离一级分类和二级分类
$categories = array();
$child_categories = array();

if ( ! empty($all_categories) && !is_wp_error($all_categories)) {
	foreach ($all_categories as $cat) {
		if ($cat->parent == 0) {
			$categories[] = $cat;
		} else {
			$child_categories[$cat->parent][] = $cat;
		}
	}
}

// 应用自定义排序
$custom_order = get_option('navai_category_order', '');
if ( ! empty($custom_order) && !empty($categories)) {
	$order_ids = array_map('intval', array_filter(array_map('trim', explode(',', $custom_order))));
	if ( ! empty($order_ids)) {
		$ordered = array();
		$unordered = array();
		// 按自定义顺序排列
		foreach ($order_ids as $id) {
			foreach ($categories as $cat) {
				if ($cat->term_id == $id) {
					$ordered[] = $cat;
					break;
				}
			}
		}
		// 将未在自定义排序中的分类放到末尾
		foreach ($categories as $cat) {
			if (!in_array($cat->term_id, $order_ids)) {
				$unordered[] = $cat;
			}
		}
		$categories = array_merge($ordered, $unordered);
	}
}

// 分类图标映射函数
if (!function_exists('navai_get_category_icon')) {
function navai_get_category_icon($cat_name, $cat_id = 0, $cat_slug = '') {
	$name = strtolower($cat_name);

	// ============ 精确关键词匹配（优先） ============
	
	// 热门相关
	if (strpos($name, '热门') !== false || strpos($name, 'top') !== false) {
		return array('icon' => 'flame', 'color' => '#ff6b6b');
	}
	// 图像/图片/绘画
	if (strpos($name, '图像') !== false || strpos($name, '图片') !== false || strpos($name, '绘画') !== false || strpos($name, '画图') !== false) {
		return array('icon' => 'image', 'color' => '#4ecdc4');
	}
	// 视频/影音/影视
	if (strpos($name, '视频') !== false || strpos($name, '影音') !== false || strpos($name, '影视') !== false || strpos($name, '电影') !== false) {
		return array('icon' => 'video', 'color' => '#a55eea');
	}
	// 音频/音乐
	if (strpos($name, '音频') !== false || strpos($name, '音乐') !== false) {
		return array('icon' => 'music', 'color' => '#00d2d3');
	}
	// 写作/文本/文案
	if (strpos($name, '写作') !== false || strpos($name, '文本') !== false || strpos($name, '文案') !== false || strpos($name, '文章') !== false) {
		return array('icon' => 'pen-tool', 'color' => '#26de81');
	}
	// 编程/代码/开发
	if (strpos($name, '编程') !== false || strpos($name, '代码') !== false || strpos($name, '开发') !== false) {
		return array('icon' => 'code-2', 'color' => '#5f27cd');
	}
	// 设计/UI
	if (strpos($name, '设计') !== false || strpos($name, 'ui') !== false) {
		return array('icon' => 'palette', 'color' => '#ff9ff3');
	}
	// 对话/聊天/助手
	if (strpos($name, '对话') !== false || strpos($name, '聊天') !== false || strpos($name, '助手') !== false) {
		return array('icon' => 'message-circle', 'color' => '#45aaf2');
	}
	// 办公/文档
	if (strpos($name, '办公') !== false || strpos($name, '文档') !== false) {
		return array('icon' => 'briefcase', 'color' => '#fd9644');
	}
	// 搜索
	if (strpos($name, '搜索') !== false) {
		return array('icon' => 'search', 'color' => '#ff6b6b');
	}
	// 翻译
	if (strpos($name, '翻译') !== false) {
		return array('icon' => 'languages', 'color' => '#54a0ff');
	}
	// 学习/教育
	if (strpos($name, '学习') !== false || strpos($name, '教育') !== false || strpos($name, '课程') !== false) {
		return array('icon' => 'graduation-cap', 'color' => '#5f27cd');
	}
	// 导航/站点
	if (strpos($name, '导航') !== false || strpos($name, '网址') !== false || strpos($name, '收藏') !== false) {
		return array('icon' => 'compass', 'color' => '#06b6d4');
	}
	// 数据/分析
	if (strpos($name, '数据') !== false || strpos($name, '分析') !== false) {
		return array('icon' => 'bar-chart-2', 'color' => '#00d2d3');
	}
	// 营销/推广
	if (strpos($name, '营销') !== false || strpos($name, '推广') !== false) {
		return array('icon' => 'trending-up', 'color' => '#ff9f43');
	}
	// 游戏/娱乐
	if (strpos($name, '游戏') !== false || strpos($name, '娱乐') !== false) {
		return array('icon' => 'gamepad-2', 'color' => '#a29bfe');
	}
	// 健康/医疗
	if (strpos($name, '健康') !== false || strpos($name, '医疗') !== false) {
		return array('icon' => 'heart-pulse', 'color' => '#ff6b6b');
	}
	// 金融/理财
	if (strpos($name, '金融') !== false || strpos($name, '理财') !== false) {
		return array('icon' => 'landmark', 'color' => '#26de81');
	}
	// 电商/购物
	if (strpos($name, '电商') !== false || strpos($name, '购物') !== false) {
		return array('icon' => 'shopping-bag', 'color' => '#ff9f43');
	}
	// 社交
	if (strpos($name, '社交') !== false) {
		return array('icon' => 'share-2', 'color' => '#54a0ff');
	}
	// 新闻/资讯
	if (strpos($name, '新闻') !== false || strpos($name, '资讯') !== false) {
		return array('icon' => 'newspaper', 'color' => '#ff6b6b');
	}
	// 3D/建模
	if (strpos($name, '3d') !== false || strpos($name, '建模') !== false) {
		return array('icon' => 'box', 'color' => '#a29bfe');
	}
	// PPT/演示
	if (strpos($name, 'ppt') !== false || strpos($name, '演示') !== false) {
		return array('icon' => 'presentation', 'color' => '#ff9f43');
	}
	// 思维导图
	if (strpos($name, '思维') !== false || strpos($name, '导图') !== false) {
		return array('icon' => 'git-branch', 'color' => '#00d2d3');
	}
	// 笔记
	if (strpos($name, '笔记') !== false || strpos($name, '记录') !== false) {
		return array('icon' => 'sticky-note', 'color' => '#feca57');
	}
	// 阅读/书籍
	if (strpos($name, '阅读') !== false || strpos($name, '书籍') !== false) {
		return array('icon' => 'book-open', 'color' => '#5f27cd');
	}
	// 邮件
	if (strpos($name, '邮件') !== false || strpos($name, '邮箱') !== false) {
		return array('icon' => 'mail', 'color' => '#54a0ff');
	}
	// 天气
	if (strpos($name, '天气') !== false) {
		return array('icon' => 'cloud-sun', 'color' => '#feca57');
	}
	// 旅行/旅游
	if (strpos($name, '旅行') !== false || strpos($name, '旅游') !== false) {
		return array('icon' => 'plane', 'color' => '#00d2d3');
	}
	// 美食/菜谱
	if (strpos($name, '美食') !== false || strpos($name, '菜谱') !== false) {
		return array('icon' => 'utensils', 'color' => '#ff6b6b');
	}
	// 健身/运动
	if (strpos($name, '健身') !== false || strpos($name, '运动') !== false) {
		return array('icon' => 'dumbbell', 'color' => '#26de81');
	}
	// 宠物
	if (strpos($name, '宠物') !== false) {
		return array('icon' => 'cat', 'color' => '#ff9ff3');
	}
	// 儿童/育儿
	if (strpos($name, '儿童') !== false || strpos($name, '育儿') !== false) {
		return array('icon' => 'baby', 'color' => '#ff9ff3');
	}
	// 简历/求职
	if (strpos($name, '简历') !== false || strpos($name, '求职') !== false || strpos($name, '招聘') !== false) {
		return array('icon' => 'file-text', 'color' => '#54a0ff');
	}
	// 论文/学术
	if (strpos($name, '论文') !== false || strpos($name, '学术') !== false) {
		return array('icon' => 'scroll-text', 'color' => '#5f27cd');
	}
	// 抠图
	if (strpos($name, '抠图') !== false) {
		return array('icon' => 'scissors', 'color' => '#ff6b6b');
	}
	// 换脸/人脸
	if (strpos($name, '换脸') !== false || strpos($name, '人脸') !== false) {
		return array('icon' => 'scan-face', 'color' => '#ff9ff3');
	}
	// 字幕
	if (strpos($name, '字幕') !== false) {
		return array('icon' => 'subtitles', 'color' => '#feca57');
	}
	// 修图/美化
	if (strpos($name, '修图') !== false || strpos($name, '美化') !== false) {
		return array('icon' => 'wand-2', 'color' => '#ff9ff3');
	}
	// 压缩
	if (strpos($name, '压缩') !== false) {
		return array('icon' => 'archive', 'color' => '#778ca3');
	}
	// 转换
	if (strpos($name, '转换') !== false || strpos($name, '格式') !== false) {
		return array('icon' => 'refresh-cw', 'color' => '#00d2d3');
	}
	// 下载/源码
	if (strpos($name, '下载') !== false || strpos($name, '源码') !== false) {
		return array('icon' => 'download', 'color' => '#26de81');
	}
	// 检测/查重
	if (strpos($name, '检测') !== false || strpos($name, '查重') !== false) {
		return array('icon' => 'shield-check', 'color' => '#26de81');
	}
	// 增强/高清
	if (strpos($name, '增强') !== false || strpos($name, '高清') !== false) {
		return array('icon' => 'zoom-in', 'color' => '#54a0ff');
	}
	// 排行榜
	if (strpos($name, '排行') !== false || strpos($name, '榜单') !== false) {
		return array('icon' => 'trophy', 'color' => '#feca57');
	}
	// 新出/最新
	if (strpos($name, '新出') !== false || strpos($name, '最新') !== false) {
		return array('icon' => 'sparkles', 'color' => '#ff9ff3');
	}
	// 大厂/品牌
	if (strpos($name, '大厂') !== false || strpos($name, '品牌') !== false) {
		return array('icon' => 'building-2', 'color' => '#54a0ff');
	}
	// 开源/免费
	if (strpos($name, '开源') !== false || strpos($name, '免费') !== false) {
		return array('icon' => 'github', 'color' => '#778ca3');
	}
	// 国产
	if (strpos($name, '国产') !== false || strpos($name, '国内') !== false) {
		return array('icon' => 'flag', 'color' => '#ff6b6b');
	}
	// 国外/海外
	if (strpos($name, '国外') !== false || strpos($name, '海外') !== false) {
		return array('icon' => 'globe', 'color' => '#54a0ff');
	}
	// 综合/全部
	if (strpos($name, '综合') !== false || strpos($name, '全部') !== false || strpos($name, '其他') !== false) {
		return array('icon' => 'layers', 'color' => '#778ca3');
	}
	// 操作手册/教程
	if (strpos($name, '手册') !== false || strpos($name, '教程') !== false) {
		return array('icon' => 'book-marked', 'color' => '#5f27cd');
	}
	// 智能体/Agent
	if (strpos($name, '智能体') !== false || strpos($name, 'agent') !== false) {
		return array('icon' => 'bot', 'color' => '#6366f1');
	}
	// 提示词
	if (strpos($name, '提示词') !== false || strpos($name, 'prompt') !== false) {
		return array('icon' => 'message-square-text', 'color' => '#ff9f43');
	}
	// 社区/论坛
	if (strpos($name, '社区') !== false || strpos($name, '论坛') !== false) {
		return array('icon' => 'users', 'color' => '#26de81');
	}
	// 手机
	if (strpos($name, '手机') !== false || strpos($name, 'app') !== false) {
		return array('icon' => 'smartphone', 'color' => '#45aaf2');
	}
	// 电脑/PC
	if (strpos($name, '电脑') !== false || strpos($name, 'pc') !== false) {
		return array('icon' => 'monitor', 'color' => '#8b5cf6');
	}
	// 电视
	if (strpos($name, '电视') !== false) {
		return array('icon' => 'tv', 'color' => '#a55eea');
	}
	// 站长
	if (strpos($name, '站长') !== false || strpos($name, '网页') !== false) {
		return array('icon' => 'globe-2', 'color' => '#06b6d4');
	}
	// 科学上网/代理
	if (strpos($name, '科学上网') !== false || strpos($name, '代理') !== false || strpos($name, 'vpn') !== false) {
		return array('icon' => 'shield', 'color' => '#f97316');
	}
	// 生活/日常
	if (strpos($name, '生活') !== false || strpos($name, '日常') !== false) {
		return array('icon' => 'coffee', 'color' => '#ff6b6b');
	}
	// 法律
	if (strpos($name, '法律') !== false) {
		return array('icon' => 'scale', 'color' => '#778ca3');
	}

	// ============ 基于ID哈希的兜底分配 ============
	// 确保每个未匹配的分类都有不同图标，且同一分类始终返回相同图标
	
	$icons_pool = array(
		'atom', 'badge-check', 'blocks', 'book-copy', 'bottle',
		'bug', 'calendar', 'camera', 'captcha', 'chart-bar',
		'chrome', 'clipboard-check', 'cloud', 'cog', 'command',
		'cpu', 'database', 'diff', 'dna', 'drama',
		'file-code', 'fingerprint', 'folder-code', 'gauge',
		'hard-drive', 'hexagon', 'key', 'lamp-desk',
		'layout-dashboard', 'lightbulb', 'link', 'lock',
		'map', 'megaphone', 'milk', 'moon', 'mountain',
		'network', 'orbit', 'package', 'paperclip',
		'puzzle', 'radar', 'rocket', 'rss', 'satellite',
		'scan', 'server', 'settings-2', 'signal', 'sitemap',
		'snowflake', 'split', 'sprout', 'stamp',
		'target', 'terminal', 'timer', 'toggle-left',
		'traffic-cone', 'tree-deciduous', 'truck',
		'umbrella', 'undo-2', 'unplug', 'upload', 'verified',
		'wallet', 'zap'
	);
	
	$colors_pool = array(
		'#6366f1', '#8b5cf6', '#a55eea', '#ec4899', '#f43f5e',
		'#f97316', '#eab308', '#22c55e', '#14b8a6', '#06b6d4',
		'#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a29bfe',
	);
	
	// 使用 slug + id 生成稳定哈希，确保同一分类每次结果一致
	$seed = $cat_slug . '_' . $cat_id;
	$hash = crc32($seed);
	$icon_index = abs($hash) % count($icons_pool);
	$color_index = abs($hash >> 8) % count($colors_pool);
	
	return array(
		'icon' => $icons_pool[$icon_index],
		'color' => $colors_pool[$color_index],
	);
}
}

// 获取当前分类
$current_id = 0;
if (is_tax('ai_category')) {
	$current_term = get_queried_object();
	if ($current_term) {
		$current_id = $current_term->term_id;
	}
}
?>

<!-- 移动端侧边栏遮罩 -->
<div class="sidebar-overlay"></div>

<!-- 收起状态的图标栏（侧边栏收起时显示一级菜单图标） -->
<div class="sidebar-collapsed-bar" id="sidebar-collapsed-bar" aria-label="<?php esc_attr_e('展开侧边栏', 'navai'); ?>">
	<a href="<?php echo esc_url(home_url('/')); ?>" class="collapsed-bar-item" title="<?php esc_attr_e('首页', 'navai'); ?>">
		<i data-lucide="home" style="color: #ff6b6b;"></i>
	</a>
	<?php if ( ! empty($categories) && !is_wp_error($categories)) : ?>
		<?php foreach ($categories as $cat) : ?>
			<?php
			$cat_info = navai_get_category_icon($cat->name, $cat->term_id, $cat->slug);
			$cat_link = is_front_page() ? '#cat-' . $cat->slug : get_term_link($cat);
			if (is_wp_error($cat_link)) {
				$cat_link = '#';
			}
			?>
			<a href="<?php echo esc_url($cat_link); ?>" class="collapsed-bar-item" title="<?php echo esc_attr($cat->name); ?>" data-cat-id="<?php echo esc_attr($cat->term_id); ?>">
				<i data-lucide="<?php echo esc_attr($cat_info['icon']); ?>" style="color: <?php echo esc_attr($cat_info['color']); ?>"></i>
			</a>
		<?php endforeach; ?>
	<?php endif; ?>
	<!-- 展开按钮 -->
	<div class="collapsed-bar-expand" id="collapsed-bar-expand">
		<i data-lucide="chevron-right"></i>
	</div>
</div>

<!-- 展开按钮（侧边栏收起时的浮动按钮 - 保留兼容） -->
<div class="sidebar-expand-btn" id="sidebar-expand-btn" aria-label="<?php esc_attr_e('展开侧边栏', 'navai'); ?>">
	<i data-lucide="chevron-right"></i>
</div>

<aside class="sidebar" role="complementary">
	<nav class="sidebar-nav" aria-label="<?php esc_attr_e('分类导航', 'navai'); ?>">
		<!-- 首页 -->
		<a href="<?php echo esc_url(home_url('/')); ?>" class="sidebar-item <?php echo is_front_page() ? 'active' : ''; ?>">
			<i data-lucide="home" style="color: #ff6b6b;"></i>
			<span><?php esc_html_e('首页', 'navai'); ?></span>
		</a>

		<!-- 分类导航 -->
		<?php if ( ! empty($categories) && !is_wp_error($categories)) : ?>
			<?php foreach ($categories as $cat) : ?>
				<?php
				$cat_info = navai_get_category_icon($cat->name, $cat->term_id, $cat->slug);
				// 从已获取的分类中查找子分类
				$children = isset($child_categories[$cat->term_id]) ? $child_categories[$cat->term_id] : array();
				$has_children = !empty($children);
				$is_active = ($current_id === $cat->term_id);
				?>
				<div class="sidebar-item-wrapper <?php echo $has_children ? 'has-children' : ''; ?>">
					<?php if (is_front_page()) : ?>
						<?php if ($has_children) : ?>
							<a href="#cat-<?php echo esc_attr($cat->slug); ?>" class="sidebar-item sidebar-anchor <?php echo $is_active ? 'active' : ''; ?>" data-cat-id="<?php echo esc_attr($cat->term_id); ?>">
								<i data-lucide="<?php echo esc_attr($cat_info['icon']); ?>" style="color: <?php echo esc_attr($cat_info['color']); ?>"></i>
								<span><?php echo esc_html($cat->name); ?></span>
								<i data-lucide="chevron-down" class="sidebar-arrow"></i>
							</a>
							<div class="sidebar-submenu">
								<?php foreach ($children as $child) :
									$child_info = navai_get_category_icon($child->name, $child->term_id, $child->slug);
								?>
									<a href="<?php echo esc_url(get_term_link($child)); ?>" class="sidebar-submenu-item <?php echo ($current_id === $child->term_id) ? 'active' : ''; ?>">
										<i data-lucide="<?php echo esc_attr($child_info['icon']); ?>" style="color: <?php echo esc_attr($child_info['color']); ?>"></i>
										<span><?php echo esc_html($child->name); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<a href="#cat-<?php echo esc_attr($cat->slug); ?>" class="sidebar-item sidebar-anchor <?php echo $is_active ? 'active' : ''; ?>" data-cat-id="<?php echo esc_attr($cat->term_id); ?>">
								<i data-lucide="<?php echo esc_attr($cat_info['icon']); ?>" style="color: <?php echo esc_attr($cat_info['color']); ?>"></i>
								<span><?php echo esc_html($cat->name); ?></span>
							</a>
						<?php endif; ?>
					<?php else : ?>
						<div class="sidebar-item <?php echo $is_active ? 'active' : ''; ?>" data-cat-id="<?php echo esc_attr($cat->term_id); ?>">
							<i data-lucide="<?php echo esc_attr($cat_info['icon']); ?>" style="color: <?php echo esc_attr($cat_info['color']); ?>"></i>
							<span><?php echo esc_html($cat->name); ?></span>
							<?php if ($has_children) : ?>
								<i data-lucide="chevron-down" class="sidebar-arrow"></i>
							<?php endif; ?>
						</div>
						<?php if ($has_children) : ?>
							<div class="sidebar-submenu">
								<?php foreach ($children as $child) :
									$child_info = navai_get_category_icon($child->name, $child->term_id, $child->slug);
								?>
									<a href="<?php echo esc_url(get_term_link($child)); ?>" class="sidebar-submenu-item <?php echo ($current_id === $child->term_id) ? 'active' : ''; ?>">
										<i data-lucide="<?php echo esc_attr($child_info['icon']); ?>" style="color: <?php echo esc_attr($child_info['color']); ?>"></i>
										<span><?php echo esc_html($child->name); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<a href="<?php echo esc_url(get_term_link($cat)); ?>" class="sidebar-item-link"></a>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>

		<!-- 底部操作按钮 -->
	<div class="sidebar-actions">
		<?php if (is_user_logged_in()) : ?>
			<a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="sidebar-action-btn sidebar-action-logout">
				<i data-lucide="log-out"></i>
				<span><?php esc_html_e('退出', 'navai'); ?></span>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="sidebar-action-btn sidebar-action-login">
				<i data-lucide="log-in"></i>
				<span><?php esc_html_e('登录', 'navai'); ?></span>
			</a>
			<?php if (get_option('users_can_register')) : ?>
			<a href="<?php echo esc_url(wp_registration_url()); ?>" class="sidebar-action-btn sidebar-action-register">
				<i data-lucide="user-plus"></i>
				<span><?php esc_html_e('注册', 'navai'); ?></span>
			</a>
			<?php endif; ?>
		<?php endif; ?>
		<a href="<?php echo esc_url(home_url('/submit')); ?>" class="sidebar-action-btn sidebar-action-submit">
			<i data-lucide="upload"></i>
			<span><?php esc_html_e('提交', 'navai'); ?></span>
		</a>
	</div>

		<!-- 收起按钮 -->
	<div class="sidebar-collapse">
		<i data-lucide="chevron-left"></i>
		<span><?php esc_html_e('收起', 'navai'); ?></span>
	</div>
	</nav>
</aside>

