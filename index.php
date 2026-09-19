<?php
/**
 * 首页模板 - 完全复刻faxianai.com样式
 *
 * @package NavAi
 * @author 老九
 * @version 1.1.1
 */

if ( ! defined('ABSPATH')) {
	exit;
}

/**
 * 获取分类图标
 */
if (!function_exists('navai_get_section_icon')) {
function navai_get_section_icon($cat_name) {
	$name = strtolower($cat_name);
	
	if (strpos($name, '热门') !== false || strpos($name, 'top') !== false) {
		return 'flame';
	}
	if (strpos($name, '图像') !== false || strpos($name, '图片') !== false || strpos($name, '绘画') !== false) {
		return 'image';
	}
	if (strpos($name, '视频') !== false || strpos($name, '影视') !== false) {
		return 'video';
	}
	if (strpos($name, '写作') !== false || strpos($name, '文本') !== false || strpos($name, '文案') !== false) {
		return 'pen-tool';
	}
	if (strpos($name, '办公') !== false || strpos($name, '文档') !== false) {
		return 'briefcase';
	}
	if (strpos($name, '对话') !== false || strpos($name, '聊天') !== false || strpos($name, '助手') !== false) {
		return 'message-circle';
	}
	if (strpos($name, '编程') !== false || strpos($name, '开发') !== false || strpos($name, '代码') !== false) {
		return 'code-2';
	}
	if (strpos($name, '设计') !== false || strpos($name, 'ui') !== false || strpos($name, 'ux') !== false) {
		return 'palette';
	}
	if (strpos($name, '音频') !== false || strpos($name, '音乐') !== false || strpos($name, '声音') !== false) {
		return 'music';
	}
	if (strpos($name, '搜索') !== false) {
		return 'search';
	}
	if (strpos($name, '翻译') !== false) {
		return 'languages';
	}
	if (strpos($name, '学习') !== false || strpos($name, '教育') !== false || strpos($name, '课程') !== false) {
		return 'graduation-cap';
	}
	if (strpos($name, '数据') !== false || strpos($name, '分析') !== false) {
		return 'bar-chart-2';
	}
	if (strpos($name, '营销') !== false || strpos($name, '推广') !== false || strpos($name, 'seo') !== false) {
		return 'trending-up';
	}
	if (strpos($name, '生活') !== false || strpos($name, '日常') !== false) {
		return 'coffee';
	}
	if (strpos($name, '游戏') !== false || strpos($name, '娱乐') !== false) {
		return 'gamepad-2';
	}
	if (strpos($name, '健康') !== false || strpos($name, '医疗') !== false || strpos($name, '医学') !== false) {
		return 'heart-pulse';
	}
	if (strpos($name, '金融') !== false || strpos($name, '理财') !== false || strpos($name, '投资') !== false) {
		return 'landmark';
	}
	if (strpos($name, '法律') !== false || strpos($name, '律师') !== false) {
		return 'scale';
	}
	if (strpos($name, '电商') !== false || strpos($name, '购物') !== false) {
		return 'shopping-bag';
	}
	if (strpos($name, '社交') !== false || strpos($name, '媒体') !== false) {
		return 'share-2';
	}
	if (strpos($name, '新闻') !== false || strpos($name, '资讯') !== false) {
		return 'newspaper';
	}
	if (strpos($name, '3d') !== false || strpos($name, '建模') !== false || strpos($name, '模型') !== false) {
		return 'box';
	}
	if (strpos($name, 'ppt') !== false || strpos($name, '演示') !== false || strpos($name, '幻灯片') !== false) {
		return 'presentation';
	}
	if (strpos($name, '思维') !== false || strpos($name, '导图') !== false || strpos($name, '脑图') !== false) {
		return 'git-branch';
	}
	if (strpos($name, '笔记') !== false || strpos($name, '记录') !== false) {
		return 'sticky-note';
	}
	if (strpos($name, '阅读') !== false || strpos($name, '书籍') !== false) {
		return 'book-open';
	}
	if (strpos($name, '邮件') !== false || strpos($name, '邮箱') !== false) {
		return 'mail';
	}
	if (strpos($name, '天气') !== false) {
		return 'cloud-sun';
	}
	if (strpos($name, '旅行') !== false || strpos($name, '旅游') !== false) {
		return 'plane';
	}
	if (strpos($name, '美食') !== false || strpos($name, '菜谱') !== false) {
		return 'utensils';
	}
	if (strpos($name, '健身') !== false || strpos($name, '运动') !== false) {
		return 'dumbbell';
	}
	if (strpos($name, '宠物') !== false || strpos($name, '动物') !== false) {
		return 'cat';
	}
	if (strpos($name, '儿童') !== false || strpos($name, '育儿') !== false) {
		return 'baby';
	}
	if (strpos($name, '星座') !== false || strpos($name, '占卜') !== false) {
		return 'sparkles';
	}
	if (strpos($name, '简历') !== false || strpos($name, '求职') !== false || strpos($name, '招聘') !== false) {
		return 'file-text';
	}
	if (strpos($name, '论文') !== false || strpos($name, '学术') !== false) {
		return 'scroll-text';
	}
	if (strpos($name, '总结') !== false || strpos($name, '摘要') !== false) {
		return 'clipboard-list';
	}
	if (strpos($name, '抠图') !== false || strpos($name, '去背景') !== false) {
		return 'scissors';
	}
	if (strpos($name, '换脸') !== false || strpos($name, '人脸') !== false) {
		return 'scan-face';
	}
	if (strpos($name, '变声') !== false || strpos($name, '声音') !== false) {
		return 'mic';
	}
	if (strpos($name, '字幕') !== false || strpos($name, '歌词') !== false) {
		return 'subtitles';
	}
	if (strpos($name, '配音') !== false || strpos($name, '朗读') !== false) {
		return 'volume-2';
	}
	if (strpos($name, '修图') !== false || strpos($name, '美化') !== false) {
		return 'wand-2';
	}
	if (strpos($name, '压缩') !== false || strpos($name, '解压') !== false) {
		return 'archive';
	}
	if (strpos($name, '转换') !== false || strpos($name, '格式') !== false) {
		return 'refresh-cw';
	}
	if (strpos($name, '下载') !== false) {
		return 'download';
	}
	if (strpos($name, '检测') !== false || strpos($name, '查重') !== false) {
		return 'shield-check';
	}
	if (strpos($name, '修复') !== false || strpos($name, '恢复') !== false) {
		return 'wrench';
	}
	if (strpos($name, '增强') !== false || strpos($name, '高清') !== false) {
		return 'zoom-in';
	}
	if (strpos($name, '生成') !== false || strpos($name, '创建') !== false) {
		return 'sparkles';
	}
	if (strpos($name, '预测') !== false || strpos($name, '预报') !== false) {
		return 'eye';
	}
	if (strpos($name, '推荐') !== false || strpos($name, '精选') !== false) {
		return 'thumbs-up';
	}
	if (strpos($name, '排行') !== false || strpos($name, '榜单') !== false) {
		return 'trophy';
	}
	if (strpos($name, '新出') !== false || strpos($name, '最新') !== false) {
		return 'sparkles';
	}
	if (strpos($name, '大厂') !== false || strpos($name, '知名') !== false) {
		return 'building-2';
	}
	if (strpos($name, '开源') !== false || strpos($name, '免费') !== false) {
		return 'github';
	}
	if (strpos($name, '国产') !== false || strpos($name, '国内') !== false) {
		return 'flag';
	}
	if (strpos($name, '国外') !== false || strpos($name, '海外') !== false) {
		return 'globe';
	}
	if (strpos($name, '综合') !== false || strpos($name, '全部') !== false || strpos($name, '其他') !== false) {
		return 'layers';
	}
	if (strpos($name, '社区') !== false || strpos($name, '论坛') !== false) {
		return 'users';
	}
	if (strpos($name, '手册') !== false || strpos($name, '指南') !== false || strpos($name, '教程') !== false) {
		return 'book-open';
	}
	if (strpos($name, '提示词') !== false || strpos($name, 'prompt') !== false) {
		return 'terminal';
	}
	if (strpos($name, '智能体') !== false || strpos($name, 'agent') !== false) {
		return 'bot';
	}
	if (strpos($name, '浏览器') !== false) {
		return 'globe';
	}
	if (strpos($name, '导航') !== false) {
		return 'compass';
	}
	
	return 'folder-open';
}
}

get_header();
get_sidebar();
?>

<div class="main-wrapper">
	<div class="container">
		<div class="main-content">
			<!-- 推荐位区域 -->
			<?php
			$rec_query = navai_get_recommend_sites(1, 6);
			if ($rec_query->have_posts()) :
			?>
			<div class="navai-recommend-section" style="margin-bottom:32px;">
				<h2 class="navai-recommend-title" style="font-size:18px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
					<i data-lucide="star" style="width:20px;height:20px;color:#f59e0b;"></i>
					<?php esc_html_e('热门推荐', 'navai'); ?>
				</h2>
				<div class="navai-recommend-grid" style="display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;min-width:0;">
				<?php while ($rec_query->have_posts()) : $rec_query->the_post();
					get_template_part('template-parts/content-ai-card', null, array('recommend' => true));
				endwhile; wp_reset_postdata(); ?>
				</div>
			</div>
			<?php endif; ?>

			<!-- 桌面端搜索区域 -->
	<div class="desktop-search-section">
		<!-- 搜索类型标签 -->
		<div class="search-tabs">
			<button type="button" class="search-tab active" data-mode="search"><?php esc_html_e('搜索', 'navai'); ?></button>
			<button type="button" class="search-tab" data-mode="image"><?php esc_html_e('图片', 'navai'); ?></button>
			<button type="button" class="search-tab" data-mode="site"><?php esc_html_e('站内', 'navai'); ?></button>
			<button type="button" class="search-tab" data-mode="deepseek"><?php esc_html_e('DeepSeek搜索', 'navai'); ?></button>
		</div>

		<!-- 搜索框 -->
		<div class="mobile-search-box">
			<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-box" id="search-form-desktop">
				<input type="search" name="s" class="search-input" id="search-input-desktop" placeholder="<?php echo esc_attr__('百度一下', 'navai'); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
				<button type="submit" aria-label="<?php esc_attr_e('搜索', 'navai'); ?>" id="search-submit-desktop">
					<i data-lucide="search"></i>
				</button>
			</form>
		</div>

		<!-- 搜索引擎选择 -->
		<div class="search-engines" id="search-engines-container-desktop">
			<a href="https://www.baidu.com/s?wd=" class="search-engine active" data-placeholder="<?php echo esc_attr__('百度一下', 'navai'); ?>"><?php esc_html_e('百度', 'navai'); ?></a>
			<a href="https://www.bing.com/search?q=" class="search-engine" data-placeholder="<?php echo esc_attr__('必应搜索', 'navai'); ?>">Bing</a>
			<a href="https://www.google.com/search?q=" class="search-engine" data-placeholder="<?php echo esc_attr__('Google一下', 'navai'); ?>">Google</a>
			<a href="https://so.toutiao.com/search?keyword=" class="search-engine" data-placeholder="<?php echo esc_attr__('头条搜索', 'navai'); ?>"><?php esc_html_e('头条', 'navai'); ?></a>
		</div>
	</div>

	<?php
	// 清除分类缓存，确保获取最新数据
	wp_cache_delete('all_ai_categories', 'terms');
	
	// 获取所有一级分类
	$parent_categories = get_terms(array(
		'taxonomy'   => 'ai_category',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'update_term_meta_cache' => false, // 不更新元数据缓存
	));

	// 应用自定义排序（与侧边栏保持一致）
	if ( ! empty($parent_categories) && !is_wp_error($parent_categories)) {
		$custom_order = get_option('navai_category_order', '');
		if ( ! empty($custom_order)) {
			$order_ids = array_map('intval', array_filter(array_map('trim', explode(',', $custom_order))));
			if ( ! empty($order_ids)) {
				$ordered   = array();
				$unordered = array();
				// 按自定义顺序排列
				foreach ($order_ids as $id) {
					foreach ($parent_categories as $cat) {
						if ($cat->term_id == $id) {
							$ordered[] = $cat;
							break;
						}
					}
				}
				// 将未在自定义排序中的分类放到末尾
				foreach ($parent_categories as $cat) {
					if ( ! in_array($cat->term_id, $order_ids)) {
						$unordered[] = $cat;
					}
				}
				$parent_categories = array_merge($ordered, $unordered);
			}
		}
	}

	if ( ! empty($parent_categories) && !is_wp_error($parent_categories)) :
		foreach ($parent_categories as $parent_cat) :
			// 获取子分类
			$child_categories = get_terms(array(
				'taxonomy'   => 'ai_category',
				'parent'     => $parent_cat->term_id,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'update_term_meta_cache' => false, // 不更新元数据缓存
			));
			$has_children = !empty($child_categories) && !is_wp_error($child_categories);

			// 收集子分类 term_id
			$child_ids = array();
			if ($has_children) {
				foreach ($child_categories as $child) {
					$child_ids[] = (int) $child->term_id;
				}
			}

			// 收集该一级分类及全部后代 term_id（父分类"all" 数据范围）
			$cat_ids = array($parent_cat->term_id);
			$all_descendants = get_term_children($parent_cat->term_id, 'ai_category');
			if (!is_wp_error($all_descendants) && !empty($all_descendants)) {
				$cat_ids = array_merge($cat_ids, $all_descendants);
			}
			$cat_ids = array_values(array_unique(array_map('intval', $cat_ids)));

			// ---- 内嵌数据：把「all + 每个子分类」前 20 条卡片一次性算好，
			//      用 wp_cache 做请求内复用，避免和下方首屏查询重复跑 WP_Query ----
			$navai_embed = array(
				'all'   => array( 'cards_html' => '', 'total_pages' => 1, 'no_results' => true, 'pagination' => '' ),
				'subcat' => array(), // key = subcat_term_id
			);

			// 1) all（父分类及全部后代，前 20）
			$all_result = navai_build_category_cards($parent_cat->term_id, 1, 20);
			$navai_embed['all'] = $all_result;

			// 2) 每个直接子分类（各取前 20）
			foreach ($child_ids as $cid) {
				$navai_embed['subcat'][$cid] = navai_build_category_cards($cid, 1, 20);
			}
	?>

	<!-- 分类区块 -->
	<section class="category-section" id="cat-<?php echo esc_attr($parent_cat->slug); ?>">
		<?php
		// 首页子分类Tab的链接目标（分类页URL）
		$home_category_url = get_term_link($parent_cat);
		if (is_wp_error($home_category_url)) {
			$home_category_url = home_url('/');
		}

		// 提前解析 all 数据（供 Tab 行末尾“更多”按钮判断 + 下方首屏渲染共用）
		$all_cards    = isset($navai_embed['all']) ? $navai_embed['all'] : array();
		$all_html     = isset($all_cards['cards_html']) ? $all_cards['cards_html'] : '';
		$all_total    = isset($all_cards['total_pages']) ? (int) $all_cards['total_pages'] : 1;
		$all_noresult = isset($all_cards['no_results']) ? $all_cards['no_results'] : true;
		$all_found    = isset($all_cards['found_posts']) ? (int) $all_cards['found_posts'] : 0;
		?>
		<!-- 子分类Tab（含一级分类名称） -->
		<div class="subcategory-tabs" data-parent="<?php echo esc_attr($parent_cat->term_id); ?>">
			<a href="<?php echo esc_url($home_category_url); ?>" class="subcategory-tab tab-parent active" data-filter="all">
				<span class="section-icon">
					<i data-lucide="<?php echo esc_attr(navai_get_section_icon($parent_cat->name)); ?>"></i>
				</span>
				<?php echo esc_html($parent_cat->name); ?>
			</a>
			<?php if ($has_children) : ?>
			<?php foreach ($child_categories as $child) : ?>
			<a href="<?php echo esc_url(add_query_arg('subcat', $child->term_id, $home_category_url)); ?>" class="subcategory-tab" data-filter="<?php echo esc_attr($child->term_id); ?>">
				<?php echo esc_html($child->name); ?>
			</a>
			<?php endforeach; ?>
			<?php endif; ?>
			<?php if ($all_found > 20) : ?>
			<a href="<?php echo esc_url($home_category_url); ?>" class="more-detail-btn" title="<?php esc_attr_e('查看该分类下全部网站', 'navai'); ?>">
				<?php esc_html_e('更多', 'navai'); ?>
			</a>
			<?php endif; ?>
		</div>

		<?php if (!$all_noresult && $all_html !== '') : ?>
		<!-- 网址网格（all 首屏，20 条） -->
		<div class="sites-grid"><?php echo $all_html; // 已通过 esc_url/esc_attr/esc_html 安全转义 ?>
		</div>
		<?php else : ?>
		<div class="no-sites">
			<p><?php esc_html_e('该分类下暂无网站', 'navai'); ?></p>
		</div>
		<?php endif; ?>

		<!-- 内嵌数据：供 main.js 在点击 Tab 时直接读取渲染，零网络、零等待 -->
		<script type="application/json" class="navai-tab-data" data-parent="<?php echo esc_attr($parent_cat->term_id); ?>">
<?php echo json_encode($navai_embed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
		</script>
	</section>

	<?php endforeach; ?>

<?php else : ?>
	<div class="no-categories">
		<i data-lucide="folder-x"></i>
		<p><?php esc_html_e('暂无网址分类，请在后台添加', 'navai'); ?></p>
	</div>
<?php endif; ?>
</div>

<?php get_footer(); ?>
<!-- NavAi 评分交互脚本 -->
<script>
(function($) {
    // 评分点击
    $(document).on('click', '.navai-star', function() {
        var $star = $(this);
        var $container = $star.closest('.navai-stars');
        var rating = parseInt($star.data('value'));
        var postEl = $container.closest('.ai-card, .detail-section');
        var postId = postEl ? postEl.dataset.postId : '';
        if (!postId) return;
        $.post(navaiAjax.ajaxurl, {
            action: 'navai_submit_rating',
            post_id: postId,
            rating: rating,
            nonce: navaiAjax.rating_nonce
        }, function(res) {
            if (res.success) {
                $container.html(res.data.stars).data('rating', res.data.avg);
                var text = $container.closest('.ai-card-rating').find('.ai-card-rating-text');
                if (text.length) text.html(res.data.avg+'<small>('+(res.data.count||'')+')');
            } else {
                alert(res.data && res.data.message ? res.data.message : navaiAjax.str_error);
            }
        }).fail(function() {
            alert(navaiAjax.str_error);
        });
    });
    // 书签收藏
    $(document).on('click', '.navai-bookmark-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var postId = $btn.data('post-id');
        if (!postId) return;
        $.post(navaiAjax.ajaxurl, {
            action: 'navai_toggle_bookmark',
            post_id: postId,
            nonce: navaiAjax.bookmark_nonce
        }, function(res) {
            if (res.success) {
                var icon = $btn.find('i');
                if (res.data.action === 'added') {
                    icon.removeClass('bookmark-outline').addClass('bookmark-filled');
                    $btn.css('color', '#E53935');
                } else {
                    icon.removeClass('bookmark-filled').addClass('bookmark-outline');
                    $btn.css('color', '');
                }
            } else {
                alert(res.data && res.data.message ? res.data.message : navaiAjax.str_error);
            }
        }).fail(function() {
            alert(navaiAjax.str_error);
        });
    });
})(jQuery);
</script>
