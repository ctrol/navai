<?php
/**
 * AI工具卡片模板
 *
 * @package Navai
 */

$card_post_id     = get_the_ID();
$card_website_url = get_post_meta($card_post_id, '_website_url', true);
$card_site_icon   = get_post_meta($card_post_id, '_site_icon_url', true);
$card_icon_color  = get_post_meta($card_post_id, '_icon_color', true);
if (empty($card_icon_color)) {
	$card_icon_color = wp_rand(1, 8);
}
$card_thumbnail = get_the_post_thumbnail_url($card_post_id, 'thumbnail');
$card_excerpt   = wp_trim_words(get_the_excerpt(), 12);
// 评分数据
$card_rating_total = (int) get_post_meta($card_post_id, '_rating_total', true);
$card_rating_count = (int) get_post_meta($card_post_id, '_rating_count', true);
$card_avg_rating   = $card_rating_count > 0 ? round($card_rating_total / $card_rating_count, 1) : 0;
$card_recommend    = (int) get_post_meta($card_post_id, '_recommend_level', true);

// 标题截断：超过8个字时截断并添加省略号
$card_full_title = get_the_title();
if (mb_strlen($card_full_title, 'UTF-8') > 8) {
	$card_title = mb_substr($card_full_title, 0, 8, 'UTF-8') . '...';
} else {
	$card_title = $card_full_title;
}

// 获取该文章所属的分类ID（用于筛选）
$post_terms = get_the_terms($card_post_id, 'ai_category');
$term_ids = array();
if ( ! empty($post_terms) && !is_wp_error($post_terms)) {
	foreach ($post_terms as $t) {
		$term_ids[] = $t->term_id;
	}
}
?>
<div class="ai-card" data-post-id="<?php echo esc_attr($card_post_id); ?>" data-terms="<?php echo esc_attr(implode(',', $term_ids)); ?>">
	<a href="<?php echo $card_website_url ? esc_url($card_website_url) : esc_url(get_permalink()); ?>"
       class="ai-card-left"
       target="_blank"
       rel="noopener noreferrer"
       title="<?php echo esc_attr($card_full_title); ?>">
		<div class="ai-card-icon<?php if (!$card_site_icon && !$card_thumbnail) : ?> color-<?php echo esc_attr($card_icon_color); ?><?php endif; ?>">
			<?php if ($card_site_icon) : ?>
				<img src="<?php echo esc_url($card_site_icon); ?>" alt="<?php echo esc_attr($card_full_title); ?>" data-icon-color="<?php echo esc_attr($card_icon_color); ?>" onerror="var p=this.parentElement;p.classList.add('color-<?php echo esc_attr($card_icon_color); ?>');this.style.display='none';this.nextElementSibling.style.display='flex';">
				<span style="display:none;align-items:center;justify-content:center;width:100%;height:100%;"><?php echo esc_html(mb_substr($card_full_title, 0, 1, 'UTF-8')); ?></span>
			<?php elseif ($card_thumbnail) : ?>
				<img src="<?php echo esc_url($card_thumbnail); ?>" alt="<?php echo esc_attr($card_full_title); ?>" data-icon-color="<?php echo esc_attr($card_icon_color); ?>" onerror="var p=this.parentElement;p.classList.add('color-<?php echo esc_attr($card_icon_color); ?>');this.style.display='none';this.nextElementSibling.style.display='flex';">
				<span style="display:none;align-items:center;justify-content:center;width:100%;height:100%;"><?php echo esc_html(mb_substr($card_full_title, 0, 1, 'UTF-8')); ?></span>
			<?php else : ?>
				<?php echo esc_html(mb_substr($card_full_title, 0, 1, 'UTF-8')); ?>
			<?php endif; ?>
		</div>
	</a>
	<a href="<?php echo esc_url(get_permalink()); ?>"
       class="ai-card-right"
       rel="noopener noreferrer"
       title="<?php echo esc_attr($card_full_title); ?>">
		<h3 class="ai-card-name"><?php echo esc_html($card_title); ?></h3>
		<p class="ai-card-desc"><?php echo esc_html($card_excerpt); ?></p>
		<div class="ai-card-meta">
			<?php if ($card_recommend >= 2) : ?>
			<span class="ai-card-badge ai-card-badge-recommend" title="置顶推荐">置顶</span>
			<?php elseif ($card_recommend >= 1) : ?>
			<span class="ai-card-badge ai-card-badge-hot" title="推荐工具">推荐</span>
			<?php endif; ?>
			<?php if ($card_avg_rating > 0) : ?>
			<span class="ai-card-rating" data-avg="<?php echo esc_attr($card_avg_rating); ?>">
				<?php for ($r = 1; $r <= 5; $r++) : ?>
					<i class="fa<?php echo $r <= round($card_avg_rating) ? ' solid' : ' regular'; ?> fa-star" style="font-size:11px;color:<?php echo $r <= round($card_avg_rating) ? '#f59e0b' : '#d1d5db'; ?>"></i>
				<?php endfor; ?>
				<span class="ai-card-rating-text"><?php echo esc_html($card_avg_rating); ?><small>(<?php echo esc_html($card_rating_count); ?>)</small></span>
			</span>
			<?php endif; ?>
		</div>
	</a>
</div>
