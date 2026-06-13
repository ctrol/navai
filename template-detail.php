<?php
/**
 * Template Name: 详情页模板
 * 拷贝网站详情页布局，包含排行榜、浮动菜单、评论等
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

get_sidebar();

// 确保全局 $post 对象已设置
if (have_posts()) {
    the_post();
}

$post_id   = get_the_ID();

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

            <!-- 评论区域 -->
            <div class="detail-comments" id="comments">
                <?php
                // 内联评论模板
                if (post_password_required()) {
                    return;
                }

                $current_user = wp_get_current_user();
                $is_logged_in = is_user_logged_in();
                ?>

                <div id="comments-area" class="comments-area">

                    <?php if (have_comments()) : ?>
                        <h3 class="comments-title">
                            <?php
                            $comment_count = get_comments_number();
                            if ($comment_count == 1) {
                                echo '1条评论';
                            } else {
                                printf('%s条评论', number_format_i18n($comment_count));
                            }
                            ?>
                        </h3>

                        <ol class="comment-list">
                            <?php
                            wp_list_comments(array(
                                'style'       => 'ol',
                                'short_ping'  => true,
                                'avatar_size' => 48,
                            ));
                            ?>
                        </ol>

                        <?php
                        the_comments_pagination(array(
                            'prev_text' => '<span class="nav-prev">&larr; 上一页</span>',
                            'next_text' => '<span class="nav-next">下一页 &rarr;</span>',
                        ));
                        ?>

                    <?php endif; ?>

                    <?php
                    $commenter = wp_get_current_commenter();
                    $req = get_option('require_name_email');
                    $aria_req = ($req ? " aria-required='true'" : '');
                    ?>

                    <div id="respond" class="comment-respond">
                        <h3 id="reply-title" class="comment-reply-title">发表评论</h3>
                        <form action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>" method="post" id="commentform" class="comment-form">
                            <p class="comment-form-comment">
                                <label for="comment">评论内容</label>
                                <textarea id="comment" name="comment" cols="45" rows="4" aria-required="true" placeholder="输入评论内容..."></textarea>
                            </p>

                            <?php if (!$is_logged_in) : ?>
                            <div class="comment-form-row">
                                <p class="comment-form-author">
                                    <label for="author">昵称<?php echo ($req ? '<span class="required">*</span>' : ''); ?></label>
                                    <input id="author" name="author" type="text" value="<?php echo esc_attr($commenter['comment_author']); ?>" size="30" <?php echo $aria_req; ?> placeholder="昵称" />
                                </p>
                                <p class="comment-form-email">
                                    <label for="email">邮箱<?php echo ($req ? '<span class="required">*</span>' : ''); ?></label>
                                    <input id="email" name="email" type="email" value="<?php echo esc_attr($commenter['comment_author_email']); ?>" size="30" <?php echo $aria_req; ?> placeholder="邮箱" />
                                </p>
                            </div>
                            <?php else : ?>
                            <div class="comment-form-logged-in">
                                <p>您已登录为 <strong><?php echo esc_html($current_user->display_name); ?></strong>。<?php wp_loginout(get_permalink()); ?></p>
                            </div>
                            <?php endif; ?>

                            <p class="form-submit">
                                <button type="submit" class="submit">发表评论</button>
                                <?php comment_id_fields(); ?>
                            </p>
                            <?php do_action('comment_form', get_the_ID()); ?>
                        </form>
                    </div>

                </div>
            </div>
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
                    <?php echo $sidebar_ad; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- 浮动式菜单 -->
<div class="floating-menu" id="floating-menu">
    <button class="floating-menu-toggle" id="floating-menu-toggle" aria-label="打开菜单">
        <i data-lucide="plus"></i>
    </button>
    <div class="floating-menu-items">
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