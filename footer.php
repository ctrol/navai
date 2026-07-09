<?php
/**
 * 底部模板文件 - 复刻faxianai.com样式
 *
 * @package NavAi
 * @author 老九
 * @version 1.28.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 获取页脚设置
$footer_desc        = get_option('navai_footer_desc', '发现AI，专业AI导航网站，一站式AI导航！');
$footer_copyright   = get_option('navai_footer_copyright', '');
$footer_icp         = get_option('navai_footer_icp', '');
$footer_gongan      = get_option('navai_footer_gongan', '');
?>
            </div><!-- .main-wrapper -->
        </div><!-- .container -->
    </main><!-- .site-main -->

    <!-- 底部 - 复刻faxianai.com -->
    <footer class="site-footer">
        <div class="footer-container">
            <!-- 描述文字 -->
            <?php if ($footer_desc) : ?>
            <p class="footer-desc"><?php echo esc_html($footer_desc); ?></p>
            <?php endif; ?>

            <!-- 导航链接 - 从底部链接菜单获取 -->
            <?php if (has_nav_menu('footer')) : ?>
            <div class="footer-nav-links">
                <?php
                $footer_menu_items = wp_get_nav_menu_items(wp_get_nav_menu_name('footer'));
                if ($footer_menu_items) :
                    $first = true;
                    foreach ($footer_menu_items as $item) :
                        if (!$first) echo '<span class="footer-dot">·</span>';
                ?>
                <a href="<?php echo esc_url($item->url); ?>"><?php echo esc_html($item->title); ?></a>
                <?php
                        $first = false;
                    endforeach;
                endif;
                ?>
            </div>
            <?php endif; ?>

            <!-- 版权信息 -->
            <div class="footer-copyright">
                <?php if ($footer_copyright) : ?>
                <span><?php echo esc_html($footer_copyright); ?></span>
                <?php else : ?>
                <span>Copyright &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></span>
                <?php endif; ?>
                <?php if ($footer_icp) : ?>
                    <span class="footer-dot">·</span>
                    <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener"><?php echo esc_html($footer_icp); ?></a>
                <?php endif; ?>
                <?php if ($footer_gongan) : ?>
                    <span class="footer-dot">·</span>
                    <span><?php echo esc_html($footer_gongan); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<!-- 返回顶部按钮 -->
<button id="back-to-top" class="back-to-top" aria-label="<?php esc_attr_e('返回顶部', 'navai'); ?>">
    <i data-lucide="chevron-up"></i>
</button>

<!-- 移动端菜单 -->
<div id="mobile-menu" class="mobile-menu">
    <div class="mobile-menu-header">
        <span class="mobile-menu-title"><?php bloginfo('name'); ?></span>
        <button class="mobile-menu-close" aria-label="<?php esc_attr_e('关闭菜单', 'navai'); ?>">
            <i data-lucide="x"></i>
        </button>
    </div>
    <nav class="mobile-menu-nav">
        <?php
        if (has_nav_menu('primary')) {
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'mobile-menu-list',
            ));
        }
        ?>
    </nav>
</div>
<div class="mobile-menu-overlay"></div>

<?php wp_footer(); ?>

<!-- Lucide Icons 初始化 - 等待脚本加载完成 -->
<script>
(function() {
    function initLucide() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        } else {
            setTimeout(initLucide, 100);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLucide);
    } else {
        initLucide();
    }
})();
</script>

</body>
</html>
