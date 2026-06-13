<?php
/**
 * 搜索结果模板文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.26.87
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="main-content" style="flex: 1;">
    <div class="section-header">
        <h2 class="section-title">
            <i data-lucide="search"></i>
            <?php printf(__('搜索结果: %s', 'navai'), get_search_query()); ?>
        </h2>
    </div>

    <?php if (have_posts()) : ?>
        <div class="card-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php
                $post_id   = get_the_ID();
                $post_type = get_post_type();

                if ($post_type === 'ai_tool') {
                    $website_url = get_post_meta($post_id, '_website_url', true);
                    $icon_color  = get_post_meta($post_id, '_icon_color', true);

                    if (empty($icon_color)) {
                        $icon_color = wp_rand(1, 8);
                    }

                    $thumbnail = get_the_post_thumbnail_url($post_id, 'thumbnail');
                    $excerpt   = wp_trim_words(get_the_excerpt(), 15);
                ?>
                    <a href="<?php echo $website_url ? esc_url($website_url) : esc_url(get_permalink()); ?>"
                       class="ai-card"
                       target="_blank"
                       rel="noopener noreferrer">
                        <div class="ai-card-icon color-<?php echo esc_attr($icon_color); ?>">
                            <?php if ($thumbnail) : ?>
                                <img src="<?php echo esc_url($thumbnail); ?>"
                                     alt="<?php echo esc_attr(get_the_title()); ?>">
                            <?php else : ?>
                                <?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="ai-card-content">
                            <h3 class="ai-card-name"><?php the_title(); ?></h3>
                            <p class="ai-card-desc"><?php echo esc_html($excerpt); ?></p>
                        </div>
                    </a>
                <?php } else { ?>
                    <a href="<?php the_permalink(); ?>" class="ai-card">
                        <div class="ai-card-icon color-1">
                            <?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?>
                        </div>
                        <div class="ai-card-content">
                            <h3 class="ai-card-name"><?php the_title(); ?></h3>
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
            <p><?php _e('未找到相关结果', 'navai'); ?></p>
            <p class="no-results-hint"><?php _e('请尝试其他关键词', 'navai'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
