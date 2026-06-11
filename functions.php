<?php
/**
 * NavAi 主题功能文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.26.82
 * @license GPL-2.0+
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ============================================================================
 * 1. 主题基础设置
 * ============================================================================
 */

/**
 * 主题初始化设置
 *
 * @return void
 */
function navai_theme_setup() {
    // 添加主题支持
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    add_theme_support('custom-logo', array(
        'height'      => 36,
        'width'       => 150,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('customize-selective-refresh-widgets');

    // 注册导航菜单
    register_nav_menus(array(
        'primary' => __('主导航菜单', 'navai'),
        'sidebar' => __('侧边栏分类菜单', 'navai'),
        'footer'  => __('底部链接菜单', 'navai'),
    ));

    // 设置内容宽度
    global $content_width;
    if (!isset($content_width)) {
        $content_width = 1200;
    }
}
add_action('after_setup_theme', 'navai_theme_setup');

/**
 * ============================================================================
 * 2. 脚本和样式加载
 * ============================================================================
 */

/**
 * 加载主题脚本和样式
 *
 * @return void
 */
function navai_enqueue_scripts() {
    // 主题版本号（用于缓存控制）
    $theme_version = wp_get_theme()->get('Version');

    // 主样式
    wp_enqueue_style(
        'navai-style',
        get_stylesheet_uri(),
        array(),
        $theme_version
    );

    // Lucide Icons (JS方式)
    wp_enqueue_script(
        'lucide-icons',
        'https://unpkg.com/lucide@latest/dist/umd/lucide.min.js',
        array(),
        null,
        true
    );

    // 主题主脚本
    wp_enqueue_script(
        'navai-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array('jquery'),
        $theme_version,
        true
    );

    // 本地化脚本 - 传递AJAX配置
    wp_localize_script('navai-script', 'navaiAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('navai_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'navai_enqueue_scripts');

/**
 * ============================================================================
 * 3. 侧边栏和小工具
 * ============================================================================
 */

/**
 * 注册侧边栏小工具区域
 *
 * @return void
 */
function navai_widgets_init() {
    register_sidebar(array(
        'name'          => __('侧边栏', 'navai'),
        'id'            => 'sidebar-1',
        'description'   => __('添加小工具到侧边栏区域', 'navai'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('底部小工具区域', 'navai'),
        'id'            => 'footer-widgets',
        'description'   => __('添加小工具到底部区域', 'navai'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-section-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'navai_widgets_init');

/**
 * ============================================================================
 * 4. 自定义文章类型 - AI工具
 * ============================================================================
 */

/**
 * 注册AI工具自定义文章类型
 *
 * @return void
 */
function navai_register_ai_tool_post_type() {
    $labels = array(
        'name'               => __('网址', 'navai'),
        'singular_name'      => __('网址', 'navai'),
        'menu_name'          => __('网址管理', 'navai'),
        'add_new'            => __('添加新网址', 'navai'),
        'add_new_item'       => __('添加新网址', 'navai'),
        'edit_item'          => __('编辑网址', 'navai'),
        'new_item'           => __('新网址', 'navai'),
        'view_item'          => __('查看网址', 'navai'),
        'search_items'       => __('搜索网址', 'navai'),
        'not_found'          => __('未找到网址', 'navai'),
        'not_found_in_trash' => __('回收站中未找到网址', 'navai'),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'navi'),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-admin-generic',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'comments'),
        'show_in_rest'        => true,
    );

    register_post_type('ai_tool', $args);
}
add_action('init', 'navai_register_ai_tool_post_type');

/**
 * 注册AI工具分类法
 *
 * @return void
 */
function navai_register_ai_tool_taxonomy() {
    $labels = array(
        'name'              => __('网址分类', 'navai'),
        'singular_name'     => __('网址分类', 'navai'),
        'search_items'      => __('搜索分类', 'navai'),
        'all_items'         => __('所有分类', 'navai'),
        'parent_item'       => __('父级分类', 'navai'),
        'parent_item_colon' => __('父级分类:', 'navai'),
        'edit_item'         => __('编辑分类', 'navai'),
        'update_item'       => __('更新分类', 'navai'),
        'add_new_item'      => __('添加新分类', 'navai'),
        'new_item_name'     => __('新分类名称', 'navai'),
        'menu_name'         => __('网址分类', 'navai'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'ai-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('ai_category', 'ai_tool', $args);
}
add_action('init', 'navai_register_ai_tool_taxonomy');

/**
 * ============================================================================
 * 5. 自定义字段（元数据框）
 * ============================================================================
 */

/**
 * 添加AI工具详情元数据框
 *
 * @return void
 */
function navai_add_ai_tool_meta_box() {
    add_meta_box(
        'ai_tool_details',
        __('网址详情', 'navai'),
        'navai_ai_tool_meta_box_callback',
        'ai_tool',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'navai_add_ai_tool_meta_box');

/**
 * 元数据框回调函数
 *
 * @param WP_Post $post 当前文章对象
 * @return void
 */
function navai_ai_tool_meta_box_callback($post) {
    wp_nonce_field('navai_ai_tool_meta', 'navai_ai_tool_meta_nonce');

    $website_url = get_post_meta($post->ID, '_website_url', true);
    $is_hot      = get_post_meta($post->ID, '_is_hot', true);
    $is_new      = get_post_meta($post->ID, '_is_new', true);
    ?>
    <style>
        .navai-meta-wrap { padding: 4px 0; }
        .navai-field { margin-bottom: 20px; }
        .navai-field label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #1d2327;
            margin-bottom: 6px;
        }
        .navai-field .description {
            font-size: 12px;
            color: #646970;
            margin-top: 4px;
        }
        .navai-url-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .navai-url-row input {
            flex: 1;
        }
        .navai-url-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid #dcdcde;
            background: #f6f7f7;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }
        .navai-url-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .navai-url-icon .dashicons {
            color: #8c8f94;
            font-size: 20px;
        }
        .navai-checkbox-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .navai-checkbox-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 400;
            font-size: 13px;
            cursor: pointer;
        }
        .navai-divider {
            border: none;
            border-top: 1px solid #dcdcde;
            margin: 20px 0;
        }
    </style>

    <div class="navai-meta-wrap">
        <!-- 隐藏字段：保存采集到的图标URL -->
        <input type="hidden" id="site_icon_url" name="site_icon_url" value="<?php echo esc_url(get_post_meta($post->ID, '_site_icon_url', true)); ?>">

        <!-- 官网地址 -->
        <div class="navai-field">
            <label for="website_url">官网地址</label>
            <div class="navai-url-row">
                <div class="navai-url-icon" id="url-icon-preview">
                    <span class="dashicons dashicons-globe"></span>
                </div>
                <input type="url" id="website_url" name="website_url"
                       value="<?php echo esc_url($website_url); ?>"
                       placeholder="https://example.com">
                <button type="button" id="fetch-site-info" class="button button-primary">
                    <span class="dashicons dashicons-download" style="vertical-align:middle;margin-top:-2px;"></span>
                    获取网址信息
                </button>
            </div>
            <p class="description">输入网址后点击"获取网址信息"自动抓取网站图标、名称和描述</p>
        </div>

        <hr class="navai-divider">

        <!-- 标记选项 -->
        <div class="navai-field">
            <label>标记选项</label>
            <div class="navai-checkbox-group">
                <label>
                    <input type="checkbox" name="is_hot" value="1" <?php checked($is_hot, '1'); ?>>
                    热门推荐
                </label>
                <label>
                    <input type="checkbox" name="is_new" value="1" <?php checked($is_new, '1'); ?>>
                    新上线
                </label>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 保存AI工具元数据
 *
 * @param int $post_id 文章ID
 * @return void
 */
function navai_save_ai_tool_meta($post_id) {
    // 安全检查
    if (!isset($_POST['navai_ai_tool_meta_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['navai_ai_tool_meta_nonce'], 'navai_ai_tool_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 保存官网地址
    if (isset($_POST['website_url'])) {
        update_post_meta($post_id, '_website_url', esc_url_raw($_POST['website_url']));
    }

    // 保存热门标记
    $is_hot = isset($_POST['is_hot']) ? '1' : '';
    update_post_meta($post_id, '_is_hot', $is_hot);

    // 保存新上线标记
    $is_new = isset($_POST['is_new']) ? '1' : '';
    update_post_meta($post_id, '_is_new', $is_new);

    // 保存网站图标URL
    if (isset($_POST['site_icon_url'])) {
        update_post_meta($post_id, '_site_icon_url', esc_url_raw($_POST['site_icon_url']));
    }
}
add_action('save_post_ai_tool', 'navai_save_ai_tool_meta');

/**
 * ============================================================================
 * 6. 后台设置页面
 * ============================================================================
 */

/**
 * 添加主题设置菜单（两级）
 *
 * @return void
 */
function navai_add_admin_menu() {
    // 顶级菜单
    add_menu_page(
        __('主题设置', 'navai'),
        __('主题设置', 'navai'),
        'manage_options',
        'navai-settings',
        'navai_general_settings_page',
        'dashicons-admin-generic',
        60
    );

    // 子菜单 - 通用设置
    add_submenu_page(
        'navai-settings',
        __('通用设置', 'navai'),
        __('通用设置', 'navai'),
        'manage_options',
        'navai-settings',
        'navai_general_settings_page'
    );

    // 子菜单 - 页脚设置
    add_submenu_page(
        'navai-settings',
        __('页脚设置', 'navai'),
        __('页脚设置', 'navai'),
        'manage_options',
        'navai-footer-settings',
        'navai_footer_settings_page'
    );

    // 网址审核子菜单（在网址管理主菜单下）
    add_submenu_page(
        'edit.php?post_type=ai_tool',
        __('网址审核', 'navai'),
        __('网址审核', 'navai'),
        'manage_options',
        'navai-site-review',
        'navai_site_review_page'
    );
}
add_action('admin_menu', 'navai_add_admin_menu');

/**
 * 通用设置页面
 *
 * @return void
 */
function navai_general_settings_page() {
    // 保存设置
    if (isset($_POST['navai_save_general']) && check_admin_referer('navai_general_nonce')) {
        update_option('navai_logo_text', sanitize_text_field($_POST['logo_text']));
        update_option('navai_logo_domain', sanitize_text_field($_POST['logo_domain']));
        update_option('navai_logo_url', esc_url_raw($_POST['logo_url']));
        update_option('navai_favicon_url', esc_url_raw($_POST['favicon_url']));
        update_option('navai_ranking_count', absint($_POST['ranking_count']));
        update_option('navai_sidebar_ad', wp_kses_post($_POST['sidebar_ad']));
        echo '<div class="notice notice-success"><p>' . esc_html__('设置已保存', 'navai') . '</p></div>';
    }

    // 获取当前设置
    $logo_text      = get_option('navai_logo_text', '发现AI');
    $logo_domain    = get_option('navai_logo_domain', 'FAXIANAI.COM');
    $logo_url       = get_option('navai_logo_url', '');
    $favicon_url    = get_option('navai_favicon_url', '');
    $ranking_count  = get_option('navai_ranking_count', 10);
    $sidebar_ad     = get_option('navai_sidebar_ad', '');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('通用设置', 'navai'); ?></h1>
        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field('navai_general_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="logo_text"><?php _e('Logo文字', 'navai'); ?></label></th>
                    <td>
                        <input type="text" id="logo_text" name="logo_text"
                               value="<?php echo esc_attr($logo_text); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="logo_domain"><?php _e('Logo域名', 'navai'); ?></label></th>
                    <td>
                        <input type="text" id="logo_domain" name="logo_domain"
                               value="<?php echo esc_attr($logo_domain); ?>"
                               placeholder="例如：FAXIANAI.COM"
                               class="regular-text">
                        <p class="description"><?php _e('显示在Logo下方的小字域名', 'navai'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="logo_url"><?php _e('Logo图片', 'navai'); ?></label></th>
                    <td>
                        <input type="url" id="logo_url" name="logo_url"
                               value="<?php echo esc_url($logo_url); ?>"
                               class="regular-text" placeholder="输入图片URL或点击上传">
                        <input type="button" class="button navai-upload-btn" data-target="logo_url" value="<?php esc_attr_e('上传图片', 'navai'); ?>">
                        <?php if ($logo_url) : ?>
                        <div class="navai-preview" style="margin-top:8px;">
                            <img src="<?php echo esc_url($logo_url); ?>" style="max-width:120px;max-height:60px;border:1px solid #ddd;border-radius:4px;">
                        </div>
                        <?php endif; ?>
                        <p class="description"><?php _e('留空则使用文字Logo', 'navai'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="favicon_url"><?php _e('Favicon', 'navai'); ?></label></th>
                    <td>
                        <input type="url" id="favicon_url" name="favicon_url"
                               value="<?php echo esc_url($favicon_url); ?>"
                               class="regular-text" placeholder="输入图片URL或点击上传">
                        <input type="button" class="button navai-upload-btn" data-target="favicon_url" value="<?php esc_attr_e('上传图片', 'navai'); ?>">
                        <?php if ($favicon_url) : ?>
                        <div class="navai-preview" style="margin-top:8px;">
                            <img src="<?php echo esc_url($favicon_url); ?>" style="max-width:32px;max-height:32px;border:1px solid #ddd;border-radius:4px;">
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="ranking_count"><?php _e('排行榜数量', 'navai'); ?></label></th>
                    <td>
                        <input type="number" id="ranking_count" name="ranking_count"
                               value="<?php echo esc_attr($ranking_count); ?>"
                               class="small-text" min="1" max="50">
                        <p class="description"><?php _e('详情页右侧排行榜显示的网址数量（默认10）', 'navai'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="sidebar_ad"><?php _e('侧边栏广告代码', 'navai'); ?></label></th>
                    <td>
                        <textarea id="sidebar_ad" name="sidebar_ad" rows="6" cols="50" class="large-text code"><?php echo esc_textarea($sidebar_ad); ?></textarea>
                        <p class="description"><?php _e('在详情页右侧排行榜下方显示的广告代码，支持HTML/JS（如百度联盟、Google AdSense等）', 'navai'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('保存设置', 'navai'), 'primary', 'navai_save_general'); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.navai-upload-btn').on('click', function(e) {
            e.preventDefault();
            var targetId = $(this).data('target');
            var frame = wp.media({
                title: '<?php esc_html_e("选择图片", "navai"); ?>',
                button: { text: '<?php esc_html_e("使用此图片", "navai"); ?>' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#' + targetId).val(attachment.url);
                // 更新预览
                var $preview = $('#' + targetId).closest('td').find('.navai-preview');
                if ($preview.length) {
                    $preview.html('<img src="' + attachment.url + '" style="max-width:120px;max-height:60px;border:1px solid #ddd;border-radius:4px;">');
                } else {
                    $('#' + targetId).after('<div class="navai-preview" style="margin-top:8px;"><img src="' + attachment.url + '" style="max-width:120px;max-height:60px;border:1px solid #ddd;border-radius:4px;"></div>');
                }
            });
            frame.open();
        });
    });
    </script>
    <?php
}

/**
 * 页脚设置页面
 *
 * @return void
 */
function navai_footer_settings_page() {
    // 保存设置
    if (isset($_POST['navai_save_footer']) && check_admin_referer('navai_footer_nonce')) {
        update_option('navai_footer_desc', sanitize_text_field($_POST['footer_desc']));
        update_option('navai_footer_copyright', sanitize_text_field($_POST['footer_copyright']));
        update_option('navai_footer_icp', sanitize_text_field($_POST['footer_icp']));
        update_option('navai_footer_gongan', sanitize_text_field($_POST['footer_gongan']));
        echo '<div class="notice notice-success"><p>' . esc_html__('设置已保存', 'navai') . '</p></div>';
    }

    // 获取当前设置
    $footer_desc        = get_option('navai_footer_desc', '发现AI，专业AI导航网站，一站式AI导航！');
    $footer_copyright   = get_option('navai_footer_copyright', '');
    $footer_icp         = get_option('navai_footer_icp', '');
    $footer_gongan      = get_option('navai_footer_gongan', '');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('页脚设置', 'navai'); ?></h1>
        <p class="description"><?php _e('底部链接请通过「外观 → 菜单 → 底部链接菜单」管理。', 'navai'); ?></p>
        <form method="post" action="">
            <?php wp_nonce_field('navai_footer_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="footer_desc"><?php _e('页脚描述', 'navai'); ?></label></th>
                    <td>
                        <input type="text" id="footer_desc" name="footer_desc"
                               value="<?php echo esc_attr($footer_desc); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="footer_copyright"><?php _e('版权信息', 'navai'); ?></label></th>
                    <td>
                        <input type="text" id="footer_copyright" name="footer_copyright"
                               value="<?php echo esc_attr($footer_copyright); ?>"
                               placeholder="留空则使用默认：Copyright © 年份 网站名"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="footer_icp"><?php _e('ICP备案号', 'navai'); ?></label></th>
                    <td>
                        <input type="text" id="footer_icp" name="footer_icp"
                               value="<?php echo esc_attr($footer_icp); ?>"
                               placeholder="例如：苏ICP备2023012627号"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="footer_gongan"><?php _e('公安备案号', 'navai'); ?></label></th>
                    <td>
                        <input type="text" id="footer_gongan" name="footer_gongan"
                               value="<?php echo esc_attr($footer_gongan); ?>"
                               placeholder="例如：苏公网安备32011402012166号"
                               class="regular-text">
                    </td>
                </tr>
            </table>
            <?php submit_button(__('保存设置', 'navai'), 'primary', 'navai_save_footer'); ?>
        </form>
    </div>
    <?php
}

/**
 * ============================================================================
 * 7. 辅助函数
 * ============================================================================
 */

/**
 * 获取Logo HTML
 *
 * @return string Logo HTML
 */
function navai_get_logo() {
    $logo_url = get_option('navai_logo_url', '');

    if ($logo_url) {
        return '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="site-logo-img">';
    }

    return '';
}

/**
 * 获取Logo文字
 *
 * @return string Logo文字
 */
function navai_get_logo_text() {
    return get_option('navai_logo_text', '发现AI');
}

/**
 * 获取Logo域名
 *
 * @return string Logo域名
 */
function navai_get_logo_domain() {
    return get_option('navai_logo_domain', 'FAXIANAI.COM');
}

/**
 * 获取Favicon
 *
 * @return string Favicon URL
 */
function navai_get_favicon() {
    $favicon_url = get_option('navai_favicon_url', '');

    if ($favicon_url) {
        return $favicon_url;
    }

    return '';
}

/**
 * 获取页脚内容
 *
 * @return string 页脚内容
 */
function navai_get_footer_content() {
    return get_option('navai_footer_content', '');
}

/**
 * 获取AI工具数量
 *
 * @return int AI工具数量
 */
function navai_get_ai_count() {
    $count = wp_count_posts('ai_tool');
    return $count->publish;
}

/**
 * ============================================================================
 * 8. 点击计数功能
 * ============================================================================
 */

/**
 * 增加点击计数
 *
 * @param int $post_id 文章ID
 * @return void
 */
function navai_increment_click_count($post_id) {
    $count = get_post_meta($post_id, '_click_count', true);
    $count = $count ? intval($count) + 1 : 1;
    update_post_meta($post_id, '_click_count', $count);
}

/**
 * 获取点击计数
 *
 * @param int $post_id 文章ID
 * @return int 点击次数
 */
function navai_get_click_count($post_id) {
    $count = get_post_meta($post_id, '_click_count', true);
    return $count ? intval($count) : 0;
}

/**
 * ============================================================================
 * 9. AJAX处理
 * ============================================================================
 */

/**
 * 搜索AI工具 AJAX
 *
 * @return void
 */
function navai_ajax_search() {
    check_ajax_referer('navai_nonce', 'nonce');

    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    if (empty($search_term) || strlen($search_term) < 2) {
        wp_send_json_success(array());
    }

    $args = array(
        'post_type'      => 'ai_tool',
        'posts_per_page' => 20,
        's'              => $search_term,
    );

    $query = new WP_Query($args);
    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = array(
                'id'      => get_the_ID(),
                'title'   => get_the_title(),
                'excerpt' => wp_trim_words(get_the_excerpt(), 20),
                'url'     => get_permalink(),
                'icon'    => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_navai_search', 'navai_ajax_search');
add_action('wp_ajax_nopriv_navai_search', 'navai_ajax_search');

/**
 * 自动采集网站信息 AJAX
 *
 * @return void
 */
function navai_ajax_fetch_site_info() {
    check_ajax_referer('navai_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

    if (empty($url)) {
        wp_send_json_error(array('message' => '请输入网址'));
    }

    // 确保URL有协议
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }

    // SSRF 防护：禁止访问内网地址
    $parsed = parse_url($url);
    $host = isset($parsed['host']) ? strtolower($parsed['host']) : '';
    $ip = gethostbyname($host);
    $blocked_ranges = array(
        array('127.0.0.0', '8'),     // 127.0.0.0/8
        array('10.0.0.0', '8'),      // 10.0.0.0/8
        array('172.16.0.0', '12'),    // 172.16.0.0/12
        array('192.168.0.0', '16'),   // 192.168.0.0/16
        array('0.0.0.0', '8'),       // 0.0.0.0/8
        array('169.254.0.0', '16'),   // 链路本地
    );
    foreach ($blocked_ranges as $range) {
        if (navai_ip_in_range($ip, $range[0], $range[1])) {
            wp_send_json_error(array('message' => '不允许访问内网地址'));
        }
    }
    // 也检查 host 是否为 localhost 或纯 IP
    if ($host === 'localhost' || preg_match('/^[\d.]+$/', $host)) {
        wp_send_json_error(array('message' => '不允许访问内网地址'));
    }

    // 使用WordPress HTTP API获取网站内容（禁止重定向到内网）
    $response = wp_remote_get($url, array(
        'timeout'     => 10,
        'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'sslverify'   => true,
        'redirection' => 0,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => '无法访问该网站：' . $response->get_error_message()));
    }

    $body = wp_remote_retrieve_body($response);
    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code !== 200) {
        wp_send_json_error(array('message' => '网站返回错误状态码：' . $status_code));
    }

    // 解析HTML获取标题和描述
    $title = '';
    $description = '';
    $icon_url = '';

    // 转换编码为UTF-8
    $charset = '';
    if (preg_match('/<meta[^>]*charset=["\']?([^"\';\s>]+)/i', $body, $m)) {
        $charset = strtoupper(trim($m[1]));
    }
    if ($charset && $charset !== 'UTF-8' && $charset !== 'UTF8') {
        $body = mb_convert_encoding($body, 'UTF-8', $charset);
    }

    // 获取标题
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $body, $matches)) {
        $title = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        // 清理标题中的分隔符和网站名
        $title = preg_replace('/\s*[\|\-\–\—_·]\s*.*/', '', $title);
        $title = preg_replace('/.*\s*[\|\-\–\—_·]\s*/', '', $title);
        // 去掉末尾的特殊字符
        $title = rtrim($title, ' ?！?…·_ -—–');
        $title = trim($title);
    }

    // 获取meta description（兼容多种属性顺序和引号）
    if (preg_match('/<meta[^>]*\sname\s*=\s*["\']description["\'][^>]*\scontent\s*=\s*["\']([^"\']*)["\']/i', $body, $matches)) {
        $description = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
    } elseif (preg_match('/<meta[^>]*\scontent\s*=\s*["\']([^"\']*)["\'][^>]*\sname\s*=\s*["\']description["\']/i', $body, $matches)) {
        $description = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
    }

    // 获取og:description作为备选
    if (empty($description)) {
        if (preg_match('/<meta[^>]*\sproperty\s*=\s*["\']og:description["\'][^>]*\scontent\s*=\s*["\']([^"\']*)["\']/i', $body, $matches)) {
            $description = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        } elseif (preg_match('/<meta[^>]*\scontent\s*=\s*["\']([^"\']*)["\'][^>]*\sproperty\s*=\s*["\']og:description["\']/i', $body, $matches)) {
            $description = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        }
    }

    // 获取favicon
    $parsed_url = parse_url($url);
    $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];

    // 尝试从HTML中获取favicon
    if (preg_match('/<link[^>]*rel=["\'](?:shortcut\s+)?icon["\'][^>]*href=["\']([^"\']*)["\'][^>]*>/i', $body, $matches)) {
        $icon_url = $matches[1];
        if (strpos($icon_url, 'http') !== 0) {
            $icon_url = $base_url . (strpos($icon_url, '/') === 0 ? '' : '/') . $icon_url;
        }
    } elseif (preg_match('/<link[^>]*href=["\']([^"\']*)["\'][^>]*rel=["\'](?:shortcut\s+)?icon["\'][^>]*>/i', $body, $matches)) {
        $icon_url = $matches[1];
        if (strpos($icon_url, 'http') !== 0) {
            $icon_url = $base_url . (strpos($icon_url, '/') === 0 ? '' : '/') . $icon_url;
        }
    }

    // 如果没有找到favicon，尝试默认路径
    if (empty($icon_url)) {
        $icon_url = $base_url . '/favicon.ico';
    }

    // 检查favicon是否存在
    $icon_response = wp_remote_head($icon_url, array(
        'timeout'    => 10,
        'sslverify'  => false,
    ));

    if (is_wp_error($icon_response) || wp_remote_retrieve_response_code($icon_response) !== 200) {
        $icon_url = '';
    }

    wp_send_json_success(array(
        'title'       => $title,
        'description' => $description,
        'icon_url'    => $icon_url,
        'url'         => $url,
    ));
}
add_action('wp_ajax_navai_fetch_site_info', 'navai_ajax_fetch_site_info');

/**
 * 上传远程图片到媒体库
 *
 * @param string $image_url 图片URL
 * @param int    $post_id   文章ID
 * @return int|WP_Error 附件ID或错误
 */
function navai_upload_remote_image($image_url, $post_id = 0) {
    if (empty($image_url)) {
        return new WP_Error('empty_url', '图片URL为空');
    }

    // 安全校验：禁止内网地址
    if (!navai_is_valid_external_url($image_url)) {
        return new WP_Error('invalid_url', '不允许访问该地址');
    }

    // 只允许图片协议
    $parsed = parse_url($image_url);
    if (!isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), array('http', 'https'), true)) {
        return new WP_Error('invalid_scheme', '只允许HTTP/HTTPS协议');
    }

    // 下载图片
    $response = wp_remote_get($image_url, array(
        'timeout'    => 10,
        'sslverify'  => true,
        'redirection' => 0,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $image_data = wp_remote_retrieve_body($response);
    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code !== 200) {
        return new WP_Error('http_error', '获取图片失败，HTTP状态码：' . $status_code);
    }

    if (empty($image_data)) {
        return new WP_Error('empty_image', '无法获取图片数据');
    }

    // 严格校验文件类型（通过文件内容，而非扩展名）
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->buffer($image_data);
    $allowed_mimes = array(
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/x-icon' => 'ico',
        'image/svg+xml' => 'svg',
        'image/webp' => 'webp',
    );

    if (!isset($allowed_mimes[$mime_type])) {
        return new WP_Error('invalid_type', '不支持的图片类型：' . $mime_type);
    }

    // 获取文件名
    $filename = basename(parse_url($image_url, PHP_URL_PATH));
    $ext = $allowed_mimes[$mime_type];
    if (empty($filename) || !preg_match('/\.\w+$/', $filename)) {
        $filename = 'site-icon.' . $ext;
    }

    // 上传目录
    $upload_dir = wp_upload_dir();
    $unique_filename = wp_unique_filename($upload_dir['path'], $filename);
    $file_path = $upload_dir['path'] . '/' . $unique_filename;

    // 保存文件
    if (!file_put_contents($file_path, $image_data)) {
        return new WP_Error('save_error', '保存图片失败');
    }

    // 准备附件数据
    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $mime_type,
        'post_title'     => sanitize_file_name($filename),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    // 插入附件
    $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);

    if (is_wp_error($attach_id)) {
        return $attach_id;
    }

    // 生成缩略图
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}

/**
 * ============================================================================
 * 10. 短代码
 * ============================================================================
 */

/**
 * 热门AI工具短代码
 *
 * @param array $atts 短代码属性
 * @return string HTML输出
 */
function navai_hot_ai_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count' => 8,
    ), $atts);

    $args = array(
        'post_type'      => 'ai_tool',
        'posts_per_page' => intval($atts['count']),
        'meta_key'       => '_is_hot',
        'meta_value'     => '1',
    );

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        echo '<div class="card-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/content', 'ai-card');
        }
        echo '</div>';
        wp_reset_postdata();
    }

    return ob_get_clean();
}
add_shortcode('hot_ai', 'navai_hot_ai_shortcode');

/**
 * 按分类显示AI工具短代码
 *
 * @param array $atts 短代码属性
 * @return string HTML输出
 */
function navai_category_ai_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'count'    => 8,
    ), $atts);

    if (empty($atts['category'])) {
        return '';
    }

    $args = array(
        'post_type'      => 'ai_tool',
        'posts_per_page' => intval($atts['count']),
        'tax_query'      => array(
            array(
                'taxonomy' => 'ai_category',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($atts['category']),
            ),
        ),
    );

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        echo '<div class="card-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/content', 'ai-card');
        }
        echo '</div>';
        wp_reset_postdata();
    }

    return ob_get_clean();
}
add_shortcode('category_ai', 'navai_category_ai_shortcode');

/**
 * ============================================================================
 * 11. 主题激活钩子
 * ============================================================================
 */

/**
 * 主题激活时创建默认数据
 *
 * @return void
 */
function navai_theme_activation() {
    // 创建默认页面
    $pages = array(
        '首页'     => array('slug' => 'home', 'content' => ''),
        '在线工具' => array('slug' => 'online-tools', 'content' => ''),
        '今日热榜' => array('slug' => 'hot-list', 'content' => ''),
        'AI排行榜' => array('slug' => 'ai-ranking', 'content' => ''),
    );

    foreach ($pages as $title => $data) {
        $page = get_page_by_path($data['slug']);
        if (!$page) {
            wp_insert_post(array(
                'post_title'   => $title,
                'post_name'    => $data['slug'],
                'post_content' => $data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ));
        }
    }

    // 创建默认AI分类（确保分类法已注册）
    if (taxonomy_exists('ai_category')) {
        $categories = array(
            '大热门AI',
            '图像AI',
            '视频AI',
            '写作AI',
            '办公AI',
            '对话AI',
            '编程AI',
            '设计AI',
            '音频AI',
            '搜索AI',
            '翻译AI',
            '学习AI',
            '数据分析AI',
            '营销AI',
            '生活AI',
            '游戏AI',
            '健康AI',
            '金融AI',
            '法律AI',
            '电商AI',
            '社交AI',
            '新闻AI',
            '3D建模AI',
            'PPT演示AI',
            '思维导图AI',
            '笔记AI',
            '阅读AI',
            '邮件AI',
            '天气AI',
            '旅行AI',
            '美食AI',
            '健身AI',
            '宠物AI',
            '儿童AI',
            '星座AI',
            '简历AI',
            '论文AI',
            '总结AI',
            '抠图AI',
            '换脸AI',
            '变声AI',
            '字幕AI',
            '配音AI',
            '修图AI',
            '压缩转换AI',
            '下载AI',
            '检测查重AI',
            '修复增强AI',
            '生成AI',
            '预测AI',
            '推荐AI',
            '排行榜AI',
            '新出AI',
            '大厂AI',
            '开源AI',
            '国产AI',
            '国外AI',
            '综合AI',
        );

        foreach ($categories as $cat_name) {
            if (!term_exists($cat_name, 'ai_category')) {
                wp_insert_term($cat_name, 'ai_category');
            }
        }
    }

    // 设置首页为静态页面
    $home_page = get_page_by_path('home');
    if ($home_page) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_page->ID);
    }

    // 刷新重写规则
    flush_rewrite_rules();
}

/**
 * 主题激活钩子处理
 *
 * @return void
 */
function navai_activate_theme() {
    // 使用 transient 确保只执行一次
    if (get_transient('navai_activated')) {
        return;
    }
    set_transient('navai_activated', true, 30);
    add_action('admin_init', 'navai_theme_activation');
}
add_action('after_switch_theme', 'navai_activate_theme');

/**
 * ============================================================================
 * 12. 自动采集功能 - 后台JS
 * ============================================================================
 */

/**
 * 添加后台采集脚本
 *
 * @param string $hook 当前页面hook
 * @return void
 */
function navai_admin_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }

    global $post_type;
    if ('ai_tool' !== $post_type) {
        return;
    }

    // 内联 admin-fetch.js 脚本
    wp_add_inline_script('jquery', '
        (function($) {
            "use strict";
            $(document).ready(function() {
                var $fetchBtn = $("#fetch-site-info");
                var $urlInput = $("#website_url");
                var $titleInput = $("#title");
                var $contentInput = $("#content");

                if (!$fetchBtn.length) return;

                $fetchBtn.on("click", function(e) {
                    e.preventDefault();
                    var url = $urlInput.val().trim();
                    if (!url) {
                        alert("请先输入网址");
                        $urlInput.focus();
                        return;
                    }
                    var originalText = $fetchBtn.html();
                    $fetchBtn.html("<i class=\'dashicons dashicons-update-alt spinning\' style=\'vertical-align: middle;\'></i> 采集中...");
                    $fetchBtn.prop("disabled", true);
                    $.ajax({
                        url: "' . admin_url('admin-ajax.php') . '",
                        type: "POST",
                        data: {
                            action: "navai_fetch_site_info",
                            nonce: "' . wp_create_nonce('navai_nonce') . '",
                            url: url
                        },
                        success: function(response) {
                            if (response.success) {
                                var data = response.data;
                                if (data.title) {
                                    if (wp.data && wp.data.select("core/editor")) {
                                        wp.data.dispatch("core/editor").editPost({ title: data.title });
                                    }
                                    if ($titleInput.length) $titleInput.val(data.title);
                                }
                                if (data.description) {
                                    if (wp.data && wp.data.select("core/editor")) {
                                        wp.data.dispatch("core/editor").editPost({ content: data.description });
                                    }
                                    if (typeof tinymce !== "undefined" && tinymce.get("content")) {
                                        tinymce.get("content").setContent(data.description);
                                    } else if ($contentInput.length) {
                                        $contentInput.val(data.description);
                                    }
                                }
                                var $urlIcon = $("#url-icon-preview");
                                if (data.icon_url) {
                                    $urlIcon.html("<img src=\'" + data.icon_url + "\' onerror=\'this.parentElement.innerHTML=\"<span class=\\\\\\\'dashicons dashicons-globe\\\\\\\'></span>\"\'>");
                                    $("#site_icon_url").val(data.icon_url);
                                }
                                alert("采集成功！标题和描述已自动填充。");
                            } else {
                                alert("采集失败：" + (response.data.message || "未知错误"));
                            }
                        },
                        error: function(xhr, status, error) {
                            alert("采集出错：" + error);
                        },
                        complete: function() {
                            $fetchBtn.html(originalText);
                            $fetchBtn.prop("disabled", false);
                        }
                    });
                });
                var style = document.createElement("style");
                style.textContent = "@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .spinning { animation: spin 1s linear infinite; }";
                document.head.appendChild(style);
            });
        })(jQuery);
    ');

    // 通用设置页面加载媒体上传器
    if (isset($_GET['page']) && $_GET['page'] === 'navai-settings') {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'navai_admin_scripts');

/**
 * ============================================================================
 * 13. 修改固定链接结构
 * ============================================================================
 */

/**
 * 自定义文章类型链接 - 使用post_id
 *
 * @param string  $post_link 文章链接
 * @param WP_Post $post      文章对象
 * @return string 修改后的链接
 */
function navai_custom_post_link($post_link, $post) {
    if ('ai_tool' === $post->post_type) {
        return home_url('/navi/' . $post->ID . '.html');
    }
    return $post_link;
}
add_filter('post_type_link', 'navai_custom_post_link', 10, 2);

/**
 * 添加重写规则（使用文章ID+.html格式）
 *
 * @return void
 */
function navai_custom_rewrite_rules() {
    add_rewrite_rule(
        '^navi/([0-9]+)\.html?$',
        'index.php?post_type=ai_tool&p=$matches[1]',
        'top'
    );
}
add_action('init', 'navai_custom_rewrite_rules', 20);

/**
 * 强制ai_tool文章类型评论始终打开
 *
 * @param bool $open    评论是否打开
 * @param int  $post_id 文章ID
 * @return bool
 */
function navai_force_comments_open($open, $post_id) {
    $post = get_post($post_id);
    if ($post && $post->post_type === 'ai_tool') {
        return true;
    }
    return $open;
}
add_filter('comments_open', 'navai_force_comments_open', 10, 2);

/**
 * ============================================================================
 * 14. 侧边栏子分类z-index修复
 * ============================================================================
 */

/**
 * 添加侧边栏子菜单样式
 *
 * @return void
 */
function navai_sidebar_submenu_styles() {
    ?>
    <style type="text/css">
        .sidebar-item-wrapper {
            position: relative;
        }
        
        .sidebar-item-wrapper.has-children {
            z-index: 1;
        }
        
        .sidebar-item-wrapper.has-children:hover {
            z-index: 100;
        }
        
        .sidebar-submenu {
            z-index: 101;
        }
    </style>
    <?php
}
add_action('wp_head', 'navai_sidebar_submenu_styles');

/**
 * ============================================================================
 * 15. 网址审核页面
 * ============================================================================
 */

/**
 * 检查IP是否在CIDR范围内（用于SSRF防护）
 *
 * @param string $ip       要检查的IP
 * @param string $range    网络范围起始IP
 * @param int    $cidr     CIDR前缀长度
 * @return bool
 */
function navai_ip_in_range($ip, $range, $cidr) {
    if (!function_exists('ip2long')) {
        return false;
    }
    $ip_long = ip2long($ip);
    $range_long = ip2long($range);
    if ($ip_long === false || $range_long === false) {
        return false;
    }
    $mask = -1 << (32 - $cidr);
    return (($ip_long & $mask) === ($range_long & $mask));
}

/**
 * 验证URL是否为合法的外部URL（非内网）
 *
 * @param string $url URL地址
 * @return bool
 */
function navai_is_valid_external_url($url) {
    $parsed = parse_url($url);
    $host = isset($parsed['host']) ? strtolower($parsed['host']) : '';
    if (empty($host)) {
        return false;
    }
    if ($host === 'localhost') {
        return false;
    }
    if (preg_match('/^[\d.]+$/', $host)) {
        return false;
    }
    $ip = gethostbyname($host);
    $blocked_ranges = array(
        array('127.0.0.0', '8'),
        array('10.0.0.0', '8'),
        array('172.16.0.0', '12'),
        array('192.168.0.0', '16'),
        array('0.0.0.0', '8'),
        array('169.254.0.0', '16'),
    );
    foreach ($blocked_ranges as $range) {
        if (navai_ip_in_range($ip, $range[0], $range[1])) {
            return false;
        }
    }
    return true;
}

/**
 * 网址审核页面
 *
 * @return void
 */
function navai_site_review_page() {
    global $wpdb;

    // 处理审核操作
    if (isset($_POST['navai_review_action']) && check_admin_referer('navai_review_nonce')) {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $action_type = sanitize_text_field($_POST['navai_review_action']);

        if ($post_id) {
            switch ($action_type) {
                case 'approve':
                    wp_update_post(array(
                        'ID'          => $post_id,
                        'post_status' => 'publish',
                    ));
                    update_post_meta($post_id, '_submit_status', 'approved');
                    echo '<div class="notice notice-success"><p>网站已通过审核并发布</p></div>';
                    break;

                case 'reject':
                    wp_update_post(array(
                        'ID'          => $post_id,
                        'post_status' => 'draft',
                    ));
                    update_post_meta($post_id, '_submit_status', 'rejected');
                    echo '<div class="notice notice-warning"><p>网站已拒绝</p></div>';
                    break;

                case 'update':
                    $site_name = sanitize_text_field($_POST['site_name']);
                    $site_url = esc_url_raw($_POST['site_url']);
                    $site_desc = wp_kses_post($_POST['site_desc']);
                    $site_category = isset($_POST['site_category']) ? array_map('intval', $_POST['site_category']) : array();
                    $site_tags = sanitize_text_field($_POST['site_tags']);
                    $site_icon_url = esc_url_raw($_POST['site_icon_url']);

                    // post_status 白名单校验
                    $allowed_statuses = array('pending', 'publish', 'draft');
                    $site_status = isset($_POST['site_status']) ? sanitize_text_field($_POST['site_status']) : 'pending';
                    if (!in_array($site_status, $allowed_statuses, true)) {
                        $site_status = 'pending';
                    }

                    wp_update_post(array(
                        'ID'           => $post_id,
                        'post_title'   => $site_name,
                        'post_content' => $site_desc,
                        'post_status'  => $site_status,
                    ));

                    update_post_meta($post_id, '_website_url', $site_url);
                    update_post_meta($post_id, '_site_icon_url', $site_icon_url);
                    update_post_meta($post_id, '_site_tags', $site_tags);

                    if (!empty($site_category)) {
                        wp_set_object_terms($post_id, $site_category, 'ai_category');
                    }

                    if ($site_status === 'publish') {
                        update_post_meta($post_id, '_submit_status', 'approved');
                    } elseif ($site_status === 'draft') {
                        update_post_meta($post_id, '_submit_status', 'rejected');
                    } else {
                        update_post_meta($post_id, '_submit_status', 'pending');
                    }

                    echo '<div class="notice notice-success"><p>网站信息已更新</p></div>';
                    break;
            }
        }
    }

    // 获取当前筛选状态
    $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';

    // 获取待审核网站列表（仅外部提交的网站）
    $args = array(
        'post_type'      => 'ai_tool',
        'posts_per_page' => 20,
        'paged'          => isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1,
        'meta_query'     => array(
            array(
                'key'     => '_submit_user',
                'compare' => 'EXISTS',
            ),
        ),
    );

    if ($filter_status === 'pending') {
        $args['post_status'] = 'pending';
    } elseif ($filter_status === 'approved') {
        $args['post_status'] = 'publish';
        $args['meta_query'][] = array(
            'key'     => '_submit_status',
            'value'   => 'approved',
            'compare' => '=',
        );
    } elseif ($filter_status === 'rejected') {
        $args['post_status'] = 'draft';
        $args['meta_query'][] = array(
            'key'     => '_submit_status',
            'value'   => 'rejected',
            'compare' => '=',
        );
    } else {
        $args['post_status'] = array('pending', 'publish', 'draft');
    }

    $query = new WP_Query($args);

    // 获取各状态数量（仅外部提交的网站）
    $pending_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p 
         INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
         WHERE p.post_type = %s AND p.post_status = 'pending' AND pm.meta_key = '_submit_user'",
        'ai_tool'
    ));
    $publish_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p 
         INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id 
         INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id 
         WHERE p.post_type = %s AND p.post_status = 'publish' AND pm1.meta_key = '_submit_user' AND pm2.meta_key = '_submit_status' AND pm2.meta_value = 'approved'",
        'ai_tool'
    ));
    $rejected_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p 
         INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id 
         INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id 
         WHERE p.post_type = %s AND p.post_status = 'draft' AND pm1.meta_key = '_submit_user' AND pm2.meta_key = '_submit_status' AND pm2.meta_value = 'rejected'",
        'ai_tool'
    ));
    $total_count = $pending_count + $publish_count + $rejected_count;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('网址审核', 'navai'); ?></h1>

        <!-- 筛选标签 -->
        <ul class="subsubsub">
            <li><a href="<?php echo admin_url('edit.php?post_type=ai_tool&page=navai-site-review&filter_status=all'); ?>" class="<?php echo $filter_status === 'all' ? 'current' : ''; ?>">全部 <span class="count">(<?php echo intval($total_count); ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url('edit.php?post_type=ai_tool&page=navai-site-review&filter_status=pending'); ?>" class="<?php echo $filter_status === 'pending' ? 'current' : ''; ?>">待审核 <span class="count">(<?php echo intval($pending_count); ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url('edit.php?post_type=ai_tool&page=navai-site-review&filter_status=approved'); ?>" class="<?php echo $filter_status === 'approved' ? 'current' : ''; ?>">已收录 <span class="count">(<?php echo intval($publish_count); ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url('edit.php?post_type=ai_tool&page=navai-site-review&filter_status=rejected'); ?>" class="<?php echo $filter_status === 'rejected' ? 'current' : ''; ?>">已拒绝 <span class="count">(<?php echo intval($rejected_count); ?>)</span></a></li>
        </ul>

        <?php if ($query->have_posts()) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="60">ID</th>
                    <th width="40">图标</th>
                    <th>网站名称</th>
                    <th>网址</th>
                    <th>提交用户</th>
                    <th>提交时间</th>
                    <th>状态</th>
                    <th width="180">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                $post_id = get_the_ID();
                $website_url = get_post_meta($post_id, '_website_url', true);
                $site_icon_url = get_post_meta($post_id, '_site_icon_url', true);
                $submit_user = get_post_meta($post_id, '_submit_user', true);
                $submit_time = get_post_meta($post_id, '_submit_time', true);
                $current_status = get_post_status($post_id);
                $submit_status = get_post_meta($post_id, '_submit_status', true);

                $user_info = $submit_user ? get_userdata($submit_user) : null;
                $user_name = $user_info ? $user_info->display_name : get_the_author();

                if ($current_status === 'pending') {
                    $status_label = '待审核';
                    $status_class = 'status-pending';
                } elseif ($current_status === 'publish') {
                    $status_label = '已收录';
                    $status_class = 'status-approved';
                } else {
                    $status_label = '已拒绝';
                    $status_class = 'status-rejected';
                }
                ?>
                <tr>
                    <td><?php echo $post_id; ?></td>
                    <td>
                        <?php if ($site_icon_url) : ?>
                            <img src="<?php echo esc_url($site_icon_url); ?>" alt="" style="width:32px;height:32px;border-radius:4px;">
                        <?php else : ?>
                            <span class="dashicons dashicons-globe" style="font-size:28px;color:#ccc;"></span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php the_title(); ?></strong></td>
                    <td><a href="<?php echo esc_url($website_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($website_url); ?></a></td>
                    <td><?php echo esc_html($user_name); ?></td>
                    <td><?php echo $submit_time ? esc_html($submit_time) : get_the_date('Y-m-d H:i'); ?></td>
                    <td><span class="navai-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></td>
                    <td>
                        <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="button button-small">编辑</a>
                        <?php if ($current_status === 'pending') : ?>
                            <form method="post" action="" style="display:inline;">
                                <?php wp_nonce_field('navai_review_nonce'); ?>
                                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                <button type="submit" name="navai_review_action" value="approve" class="button button-small button-primary">通过</button>
                                <button type="submit" name="navai_review_action" value="reject" class="button button-small">拒绝</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- 分页 -->
        <?php
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) :
        ?>
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php
                echo paginate_links(array(
                    'base'      => add_query_arg('paged', '%#%'),
                    'format'    => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total'     => $total_pages,
                    'current'   => $args['paged'],
                ));
                ?>
            </div>
        </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

        <?php else : ?>
        <p>暂无需要审核的网站</p>
        <?php endif; ?>
    </div>

    <style>
    .navai-status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-approved {
        background: #d4edda;
        color: #155724;
    }
    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }
    </style>
    <?php
}
