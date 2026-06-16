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

// 获取该文章所属的分类ID（用于筛选）
$post_terms = get_the_terms($card_post_id, 'ai_category');
$term_ids = array();
if (!empty($post_terms) && !is_wp_error($post_terms)) {
    foreach ($post_terms as $t) {
        $term_ids[] = $t->term_id;
    }
}
?>
<div class="ai-card" data-terms="<?php echo esc_attr(implode(',', $term_ids)); ?>">
    <a href="<?php echo $card_website_url ? esc_url($card_website_url) : esc_url(get_permalink()); ?>"
       class="ai-card-left"
       target="_blank"
       rel="noopener noreferrer"
       title="<?php echo esc_attr(get_the_title()); ?>">
        <div class="ai-card-icon<?php if (!$card_site_icon && !$card_thumbnail) : ?> color-<?php echo esc_attr($card_icon_color); ?><?php endif; ?>">
            <?php if ($card_site_icon) : ?>
                <img src="<?php echo esc_url($card_site_icon); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <span style="display:none;"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
            <?php elseif ($card_thumbnail) : ?>
                <img src="<?php echo esc_url($card_thumbnail); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
            <?php else : ?>
                <?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?>
            <?php endif; ?>
        </div>
    </a>
    <a href="<?php echo esc_url(get_permalink()); ?>"
       class="ai-card-right"
       rel="noopener noreferrer"
       title="<?php echo esc_attr(get_the_title()); ?>">
        <h3 class="ai-card-name"><?php the_title(); ?></h3>
        <p class="ai-card-desc"><?php echo esc_html($card_excerpt); ?></p>
    </a>
</div>
