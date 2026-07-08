<?php
/**
 * 单篇文章模板文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.28.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// 确保全局 $post 对象已设置
if (have_posts()) {
    the_post();
}

$post_id   = get_the_ID();
$post_type = get_post_type();

// 初始化默认值
$website_url   = '';
$site_icon_url = '';
$is_hot        = false;
$is_new        = false;
$categories    = false;

// 如果是AI工具类型
if ($post_type === 'ai_tool') {
    $website_url   = get_post_meta($post_id, '_website_url', true);
    $site_icon_url = get_post_meta($post_id, '_site_icon_url', true);
    $is_hot        = get_post_meta($post_id, '_is_hot', true);
    $is_new        = get_post_meta($post_id, '_is_new', true);
    $categories    = get_the_terms($post_id, 'ai_category');
}

$thumbnail = get_the_post_thumbnail_url($post_id, 'large');

// 获取排行榜数量
$ranking_count = get_option('navai_ranking_count', 10);

// 获取排行榜数据（按浏览量排序）
$ranking_args = array(
    'post_type'      => 'ai_tool',
    'posts_per_page' => $ranking_count,
    'post__not_in'   => array($post_id),
    'meta_key'       => '_post_views',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
);
$ranking_query = new WP_Query($ranking_args);
// 如果没有浏览量数据，按发布日期排序
if (!$ranking_query->have_posts()) {
    $ranking_args = array(
        'post_type'      => 'ai_tool',
        'posts_per_page' => $ranking_count,
        'post__not_in'   => array($post_id),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $ranking_query = new WP_Query($ranking_args);
}
?>

<div class="main-content detail-page" style="flex: 1;">

    <!-- 顶部信息栏 -->
    <div class="detail-header">
        <div class="detail-header-main">
            <h1 class="detail-title"><?php the_title(); ?></h1>

            <div class="detail-meta">
                <span class="detail-meta-item">
                    <i data-lucide="clock"></i>
                    <?php echo get_the_date('Y年n月j日'); ?>发布
                </span>
            </div>

            <p class="detail-subtitle"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 30)); ?></p>

            <div class="detail-info-table">
                <div class="detail-info-row">
                    <div class="detail-info-label">收录时间：</div>
                    <div class="detail-info-value"><?php echo get_the_date('Y-m-d'); ?></div>
                </div>
            </div>

            <?php if (!empty($website_url)) : ?>
            <div class="detail-actions">
                <a href="<?php echo esc_url($website_url); ?>" class="detail-btn-primary" target="_blank" rel="noopener noreferrer">
                    打开网站 <i data-lucide="external-link"></i>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($categories && !is_wp_error($categories)) : ?>
            <div class="detail-tags">
                <?php foreach ($categories as $cat) : ?>
                <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="detail-tag" rel="tag">
                    <i data-lucide="folder"></i><?php echo esc_html($cat->name); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- 右侧预览图 + 广告 -->
        <div class="detail-preview-wrap">
            <!-- 广告位 -->
            <?php
            $content_ad = get_option('navai_content_ad', '');
            if (!empty($content_ad)) :
            ?>
            <div class="detail-header-ad">
                <div class="detail-header-ad-inner">
                    <?php echo wp_kses_post($content_ad); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 右侧预览图 -->
            <div class="detail-preview">
                <div class="detail-preview-body">
                    <div class="detail-favicon">
                        <?php if ($site_icon_url) : ?>
                            <img src="<?php echo esc_url($site_icon_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="16" height="16">
                        <?php else : ?>
                            <i data-lucide="globe" style="width:16px;height:16px;"></i>
                        <?php endif; ?>
                        <span><?php echo esc_html(get_the_title()); ?></span>
                    </div>
                    <div class="detail-screenshot">
                        <?php if (!empty($website_url)) : ?>
                            <img src="https://s0.wp.com/mshots/v1/<?php echo esc_url($website_url); ?>?w=456&h=300" alt="<?php echo esc_attr(get_the_title()); ?>网页截图" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" loading="lazy">
                            <div class="detail-screenshot-placeholder" style="display:none;">
                                <i data-lucide="image" style="width:48px;height:48px;color:var(--gray-300);"></i>
                            </div>
                        <?php elseif ($thumbnail) : ?>
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr(get_the_title()); ?>网页截图">
                        <?php else : ?>
                            <div class="detail-screenshot-placeholder">
                                <i data-lucide="image" style="width:48px;height:48px;color:var(--gray-300);"></i>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($website_url)) : ?>
                        <a href="<?php echo esc_url($website_url); ?>" class="detail-preview-btn" target="_blank" rel="noopener noreferrer">
                            打开网站
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 正文 + 右侧排行榜 -->
    <div class="detail-body">
        <div class="detail-main">
            <!-- 正文内容 -->
            <div class="detail-content">
                <div class="detail-content-body">
                    <div class="detail-article">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <!-- 相关推荐 -->
            <?php
            if ($categories && !is_wp_error($categories)) {
                $related_args = array(
                    'post_type'      => 'ai_tool',
                    'posts_per_page' => 8,
                    'post__not_in'   => array($post_id),
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'ai_category',
                            'field'    => 'term_id',
                            'terms'    => wp_list_pluck($categories, 'term_id'),
                        ),
                    ),
                );

                $related_query = new WP_Query($related_args);

                if ($related_query->have_posts()) :
            ?>
            <div class="detail-related">
                <h3 class="detail-related-title">
                    <i data-lucide="tag"></i>
                    <?php echo esc_html(get_the_title()); ?>相关导航
                </h3>
                <div class="detail-related-grid">
                    <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                        <?php
                        $related_id      = get_the_ID();
                        $related_url     = get_post_meta($related_id, '_website_url', true);
                        $related_icon    = get_post_meta($related_id, '_site_icon_url', true);
                        $related_thumb   = get_the_post_thumbnail_url($related_id, 'thumbnail');
                        $related_excerpt = wp_trim_words(get_the_excerpt(), 15);
                        ?>
                        <div class="detail-related-item" title="<?php echo esc_attr($related_excerpt); ?>">
                            <a href="<?php echo $related_url ? esc_url($related_url) : esc_url(get_permalink()); ?>"
                               class="detail-related-icon<?php if (!$related_icon && !$related_thumb) : ?> no-bg<?php endif; ?>"
                               target="_blank"
                               rel="noopener noreferrer">
                                <?php if ($related_icon) : ?>
                                    <img src="<?php echo esc_url($related_icon); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <span style="display:none;"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
                                <?php elseif ($related_thumb) : ?>
                                    <img src="<?php echo esc_url($related_thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                <?php else : ?>
                                    <span><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="detail-related-body">
                                <h4 class="detail-related-name"><?php the_title(); ?></h4>
                                <p class="detail-related-desc"><?php echo esc_html($related_excerpt); ?></p>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            </div>
            <?php
                endif;
            }
            ?>

            <!-- 评论区域 -->
            <?php if (comments_open() || get_comments_number()) : ?>
            <div class="detail-comments" id="comments">
                <?php comments_template(); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- 右侧浮动排行榜 -->
        <?php if ($ranking_query->have_posts()) : ?>
        <div class="detail-sidebar">
            <div class="detail-sidebar-sticky">
                <div class="detail-ranking">
                    <div class="detail-ranking-header">
                        <h3 class="detail-ranking-title">
                            <i data-lucide="trophy"></i>
                            排行榜
                        </h3>
                    </div>
                    <div class="detail-ranking-list">
                        <?php
                        $rank = 1;
                        while ($ranking_query->have_posts()) : $ranking_query->the_post();
                            $rank_id     = get_the_ID();
                            $rank_url    = get_post_meta($rank_id, '_website_url', true);
                            $rank_icon   = get_post_meta($rank_id, '_site_icon_url', true);
                            $rank_thumb  = get_the_post_thumbnail_url($rank_id, 'thumbnail');
                            $rank_desc   = wp_trim_words(get_the_excerpt(), 20);
                        ?>
                        <div class="detail-ranking-item" title="<?php echo esc_attr($rank_desc); ?>">
                            <span class="detail-ranking-num"><?php echo $rank; ?></span>
                            <a href="<?php echo $rank_url ? esc_url($rank_url) : esc_url(get_permalink()); ?>"
                               class="detail-ranking-icon<?php if (!$rank_icon && !$rank_thumb) : ?> no-bg<?php endif; ?>"
                               target="_blank"
                               rel="noopener noreferrer">
                                <?php if ($rank_icon) : ?>
                                    <img src="<?php echo esc_url($rank_icon); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <span style="display:none;"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
                                <?php elseif ($rank_thumb) : ?>
                                    <img src="<?php echo esc_url($rank_thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                <?php else : ?>
                                    <span><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="detail-ranking-info">
                                <h4 class="detail-ranking-name"><?php the_title(); ?></h4>
                                <p class="detail-ranking-desc"><?php echo esc_html($rank_desc); ?></p>
                            </a>
                        </div>
                        <?php $rank++; endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>

                <!-- 侧边栏广告 -->
                <?php
                $sidebar_ad = get_option('navai_sidebar_ad', '');
                if (!empty($sidebar_ad)) :
                ?>
                <div class="detail-sidebar-ad">
                    <?php echo wp_kses_post($sidebar_ad); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php get_sidebar(); ?>

<!-- 浮动式菜单 -->
<div class="floating-menu" id="floating-menu">
    <button class="floating-menu-toggle" id="floating-menu-toggle" aria-label="打开菜单">
        <i data-lucide="plus"></i>
    </button>
    <div class="floating-menu-items">
        <?php if (!empty($website_url)) : ?>
        <a href="<?php echo esc_url($website_url); ?>" class="floating-menu-item" target="_blank" rel="noopener noreferrer" title="访问网站">
            <i data-lucide="external-link"></i>
            <span class="floating-menu-label">访问网站</span>
        </a>
        <?php endif; ?>
        <a href="javascript:void(0);" class="floating-menu-item" id="copy-link-btn" title="复制链接">
            <i data-lucide="link"></i>
            <span class="floating-menu-label">复制链接</span>
        </a>
        <a href="javascript:void(0);" class="floating-menu-item" id="share-btn" title="分享">
            <i data-lucide="share-2"></i>
            <span class="floating-menu-label">分享</span>
        </a>
        <a href="#comments" class="floating-menu-item" title="评论">
            <i data-lucide="message-circle"></i>
            <span class="floating-menu-label">评论</span>
        </a>
        <a href="#comments" class="floating-menu-item" title="返回顶部" id="back-to-top-fab">
            <i data-lucide="chevron-up"></i>
            <span class="floating-menu-label">返回顶部</span>
        </a>
    </div>
</div>

<?php get_footer(); ?>
