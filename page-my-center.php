<?php
/**
 * Template Name: 我的中心
 * Description: 显示用户的收藏和评分记录
 *
 * @package NavAi
 * @version 1.29.66
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
get_sidebar();
?>

<div class="main-wrapper">
    <div class="container">
        <div class="main-content" style="min-height: 60vh;">
            <?php
            // 输出个人中心内容（包含收藏和评分两个标签页）
            echo do_shortcode('[navai_my_center]');
            ?>
        </div>
    </div>
</div>

<?php
get_footer();
