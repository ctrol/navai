<?php
/**
 * 分类归档模板文件 - 参考faxianai.com分类页
 *
 * @package NavAi
 * @author 老九
 * @version 1.1.1
 */

// 防止直接访问
if ( ! defined('ABSPATH')) {
	exit;
}

get_header();
get_sidebar();

// 获取当前分类
$term          = get_queried_object();
$category_name = $term ? $term->name : __('AI工具', 'navai');

// 获取子分类
$child_categories = get_terms(array(
	'taxonomy'   => 'ai_category',
	'parent'     => $term ? $term->term_id : 0,
	'hide_empty' => false,
	'orderby'    => 'name',
	'order'      => 'ASC',
));
$has_children = ! empty($child_categories) && ! is_wp_error($child_categories);

// 当前激活的子分类（来自URL参数 subcat）
$active_subcat = isset($_GET['subcat']) ? absint($_GET['subcat']) : 0;
$active_term   = $term;
$is_subcat_page = false;

// 验证子分类存在、未被删除，并明确按当前 Tab 指向的分类自身数据查询
if ($active_subcat > 0) {
	$subcat_term = get_term($active_subcat, 'ai_category');
	if ($subcat_term && !is_wp_error($subcat_term)) {
		$active_term    = $subcat_term;
		$is_subcat_page = true;
	}
}

// 获取分类图标
$section_icon = 'folder-open';
if (function_exists('navai_get_section_icon')) {
	$section_icon = navai_get_section_icon($category_name);
}

// 当前分类URL（用于构建Tab链接）
$category_url = $term ? get_term_link($term) : home_url('/');
if (is_wp_error($category_url)) {
	$category_url = home_url('/');
}

// 当前激活 Tab 对应的分类 URL：父分类使用原分类页 URL，子分类必须附加 subcat 参数
$active_tab_url = $category_url;
if ($is_subcat_page) {
	$active_tab_url = add_query_arg('subcat', $active_subcat, $category_url);
}

// 使用 navai_build_category_cards() 构建当前 Tab 的卡片数据（带 object cache）
// 父分类：target = $term->term_id；子分类：target = $active_subcat
$target_term_id = $is_subcat_page ? $active_subcat : ($term ? $term->term_id : 0);
$paged          = max(1, get_query_var('paged') ? get_query_var('paged') : 1);

if (function_exists('navai_build_category_cards') && $target_term_id > 0) {
	$cards_result = navai_build_category_cards($target_term_id, $paged, 75);
	$cards_html   = $cards_result['cards_html'];
	$total_pages  = $cards_result['total_pages'];
	$no_results   = $cards_result['no_results'];
} else {
	// Fallback：辅助函数不存在时（理论上不会发生）
	$cards_html = '';
	$total_pages = 1;
	$no_results  = true;
}
?>

<div class="main-content">
	<!-- 子分类Tab（含一级分类名称） -->
	<!-- data-parent + data-current-subcat 供 JS AJAX 切换使用 -->
	<div class="subcategory-tabs"
	     data-parent="<?php echo esc_attr($term ? $term->term_id : 0); ?>"
	     data-current-subcat="<?php echo esc_attr($active_subcat); ?>">
		<a href="<?php echo esc_url($category_url); ?>"
		   class="subcategory-tab tab-parent<?php if (!$is_subcat_page) : ?> active<?php endif; ?>"
		   data-filter="all">
			<span class="section-icon">
				<i data-lucide="<?php echo esc_attr($section_icon); ?>"></i>
			</span>
			<?php echo esc_html($category_name); ?>
		</a>
		<?php if ($has_children) : ?>
		<?php foreach ($child_categories as $child) : ?>
		<a href="<?php echo esc_url(add_query_arg('subcat', $child->term_id, $category_url)); ?>"
		   class="subcategory-tab<?php if ($active_subcat === (int) $child->term_id) : ?> active<?php endif; ?>"
		   data-filter="<?php echo esc_attr($child->term_id); ?>">
			<?php echo esc_html($child->name); ?>
		</a>
		<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<!-- 卡片容器：JS AJAX 切换时替换此 div 内容 -->
	<div id="navai-cards-container">
	<?php if (!$no_results && $cards_html !== '') : ?>
	<!-- 网址网格 -->
	<div class="sites-grid">
		<?php echo $cards_html; ?>
	</div>

	<!-- 分页 -->
	<?php if ($total_pages > 1) : ?>
	<nav class="pagination" aria-label="<?php esc_attr_e('分页导航', 'navai'); ?>">
		<?php
		echo paginate_links(array(
			'prev_text'  => '<i data-lucide="chevron-left"></i>',
			'next_text'  => '<i data-lucide="chevron-right"></i>',
			'total'      => $total_pages,
			'current'    => $paged,
			'add_args'   => $is_subcat_page ? array('subcat' => $active_subcat) : array(),
		));
		?>
	</nav>
	<?php endif; ?>

	<?php else : ?>
	<div class="no-results">
		<i data-lucide="inbox"></i>
		<p><?php esc_html_e('该分类下暂无AI工具', 'navai'); ?></p>
	</div>
	<?php endif; ?>
	</div><!-- /#navai-cards-container -->
</div>

<?php get_footer(); ?>
