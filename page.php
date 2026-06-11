<?php
/**
 * 页面模板文件
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

<div class="main-content" style="flex: 1;">
    <?php while (have_posts()) : the_post(); ?>
        <article id="page-<?php the_ID(); ?>" <?php post_class('page-content'); ?>>
            <div class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </div>

            <div class="page-body">
                <?php the_content(); ?>
            </div>

            <?php
            // 如果启用了评论
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
