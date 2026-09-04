<?php
/**
 * 搜索结果模板文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.29.22
 */

// 防止直接访问
if ( ! defined('ABSPATH')) {
	exit;
}

get_header();
get_sidebar();
?>

<div class="main-content" style="flex: 1;">
	<div class="section-header">
		<h2 class="section-title">
			<i data-lucide="search"></i>
			<?php printf(esc_html__('搜索结果: %s', 'navai'), get_search_query()); ?>
		</h2>
	</div>

	<?php if (have_posts()) : ?>
		<div class="card-grid">
			<?php while (have_posts()) : the_post(); ?>
				<?php
				$post_id   = get_the_ID();
				$post_type = get_post_type();

				if ('ai_tool' === $post_type ) {
					$website_url = get_post_meta($post_id, '_website_url', true);
					$site_icon   = get_post_meta($post_id, '_site_icon_url', true);
					$icon_color  = get_post_meta($post_id, '_icon_color', true);

					if (empty($icon_color)) {
						$icon_color = wp_rand(1, 8);
					}

					$thumbnail = get_the_post_thumbnail_url($post_id, 'thumbnail');
					$excerpt   = wp_trim_words(get_the_excerpt(), 15);

					// 标题截断：超过8个字时截断并添加省略号
					$full_title = get_the_title();
					if (mb_strlen($full_title, 'UTF-8') > 8) {
						$short_title = mb_substr($full_title, 0, 8, 'UTF-8') . '...';
					} else {
						$short_title = $full_title;
					}
				?>
					<a href="<?php echo $website_url ? esc_url($website_url) : esc_url(get_permalink()); ?>"
                       class="ai-card"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="<?php echo esc_attr($full_title); ?>">
						<div class="ai-card-icon<?php if (!$site_icon && !$thumbnail) : ?> color-<?php echo esc_attr($icon_color); ?><?php endif; ?>">
							<?php if ($site_icon) : ?>
								<img src="<?php echo esc_url($site_icon); ?>" alt="<?php echo esc_attr($full_title); ?>" data-icon-color="<?php echo esc_attr($icon_color); ?>" onerror="var p=this.parentElement;p.classList.add('color-<?php echo esc_attr($icon_color); ?>');this.style.display='none';this.nextElementSibling.style.display='flex';">
								<span style="display:none;align-items:center;justify-content:center;width:100%;height:100%;"><?php echo esc_html(mb_substr($full_title, 0, 1, 'UTF-8')); ?></span>
							<?php elseif ($thumbnail) : ?>
								<img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($full_title); ?>" data-icon-color="<?php echo esc_attr($icon_color); ?>" onerror="var p=this.parentElement;p.classList.add('color-<?php echo esc_attr($icon_color); ?>');this.style.display='none';this.nextElementSibling.style.display='flex';">
								<span style="display:none;align-items:center;justify-content:center;width:100%;height:100%;"><?php echo esc_html(mb_substr($full_title, 0, 1, 'UTF-8')); ?></span>
							<?php else : ?>
								<?php echo esc_html(mb_substr($full_title, 0, 1, 'UTF-8')); ?>
							<?php endif; ?>
						</div>
						<div class="ai-card-content">
							<h3 class="ai-card-name"><?php echo esc_html($short_title); ?></h3>
							<p class="ai-card-desc"><?php echo esc_html($excerpt); ?></p>
						</div>
					</a>
				<?php } else { ?>
					<?php
						$other_full_title = get_the_title();
						$other_short_title = mb_strlen($other_full_title, 'UTF-8') > 8
							? mb_substr($other_full_title, 0, 8, 'UTF-8') . '...'
							: $other_full_title;
					?>
					<a href="<?php the_permalink(); ?>" class="ai-card" title="<?php echo esc_attr($other_full_title); ?>">
						<div class="ai-card-icon color-1">
							<?php echo esc_html(mb_substr($other_full_title, 0, 1, 'UTF-8')); ?>
						</div>
						<div class="ai-card-content">
							<h3 class="ai-card-name"><?php echo esc_html($other_short_title); ?></h3>
							<p class="ai-card-desc"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 15)); ?></p>
						</div>
					</a>
				<?php } ?>
			<?php endwhile; ?>
		</div>

		<!-- 分页 -->
		<nav class="pagination" aria-label="<?php esc_attr_e('分页导航', 'navai'); ?>">
			<?php
			echo paginate_links(array(
				'prev_text' => '<i data-lucide="chevron-left"></i>',
				'next_text' => '<i data-lucide="chevron-right"></i>',
			));
			?>
		</nav>

	<?php else : ?>
		<div class="no-results">
			<i data-lucide="search-x"></i>
			<p><?php esc_html_e('未找到相关结果', 'navai'); ?></p>
			<p class="no-results-hint"><?php esc_html_e('请尝试其他关键词', 'navai'); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
