<?php
/**
 * 分类归档模板文件 - 参考faxianai.com分类页
 *
 * @package NavAi
 * @author 老九
 * @version 1.26.82
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

get_header();
get_sidebar();
?>

<div class="main-content">
    <?php
    $term = get_queried_object();
    $category_name = $term ? $term->name : __('AI工具', 'navai');

    // 获取子分类
    $child_categories = get_terms(array(
        'taxonomy'   => 'ai_category',
        'parent'     => $term->term_id,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));
    $has_children = !empty($child_categories) && !is_wp_error($child_categories);

    // 获取分类图标
    $section_icon = 'folder-open';
    if (function_exists('navai_get_section_icon')) {
        $section_icon = navai_get_section_icon($category_name);
    }
    ?>

    <!-- 子分类Tab（含一级分类名称） -->
    <div class="subcategory-tabs">
        <button class="subcategory-tab tab-parent" data-filter="all">
            <span class="section-icon">
                <i data-lucide="<?php echo esc_attr($section_icon); ?>"></i>
            </span>
            <?php echo esc_html($category_name); ?>
        </button>
        <?php if ($has_children) : ?>
        <?php $first_child = true; foreach ($child_categories as $child) : ?>
        <button class="subcategory-tab<?php if ($first_child) : ?> active<?php $first_child = false; endif; ?>" data-filter="<?php echo esc_attr($child->term_id); ?>">
            <?php echo esc_html($child->name); ?>
        </button>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (have_posts()) : ?>
    <!-- 网址网格 -->
    <div class="sites-grid">
        <?php while (have_posts()) : the_post(); ?>
            <?php
            $post_id     = get_the_ID();
            $website_url = get_post_meta($post_id, '_website_url', true);
            $site_icon_url = get_post_meta($post_id, '_site_icon_url', true);
            $icon_color  = get_post_meta($post_id, '_icon_color', true);

            if (empty($icon_color)) {
                $icon_color = wp_rand(1, 8);
            }

            $thumbnail = get_the_post_thumbnail_url($post_id, 'thumbnail');
            $excerpt   = wp_trim_words(get_the_excerpt(), 12);

            // 获取该文章所属的分类ID
            $post_terms = get_the_terms($post_id, 'ai_category');
            $term_ids = array();
            if (!empty($post_terms) && !is_wp_error($post_terms)) {
                foreach ($post_terms as $t) {
                    $term_ids[] = $t->term_id;
                }
            }
            ?>

            <?php
            // 内联 AI 卡片模板（原 template-parts/content-ai-card.php）
            $card_post_id     = get_the_ID();
            $card_website_url = get_post_meta($card_post_id, '_website_url', true);
            $card_site_icon   = get_post_meta($card_post_id, '_site_icon_url', true);
            $card_icon_color  = get_post_meta($card_post_id, '_icon_color', true);
            if (empty($card_icon_color)) {
                $card_icon_color = wp_rand(1, 8);
            }
            $card_thumbnail = get_the_post_thumbnail_url($card_post_id, 'thumbnail');
            $card_excerpt   = wp_trim_words(get_the_excerpt(), 12);
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
        <i data-lucide="inbox"></i>
        <p><?php _e('该分类下暂无AI工具', 'navai'); ?></p>
    </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
