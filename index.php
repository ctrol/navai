<?php
/**
 * 首页模板 - 完全复刻faxianai.com样式
 *
 * @package NavAi
 * @author 老九
 * @version 1.26.90
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 获取分类图标
 */
if (!function_exists('navai_get_section_icon')) {
function navai_get_section_icon($cat_name) {
    $name = strtolower($cat_name);
    
    if (strpos($name, '热门') !== false || strpos($name, 'top') !== false) {
        return 'flame';
    }
    if (strpos($name, '图像') !== false || strpos($name, '图片') !== false || strpos($name, '绘画') !== false) {
        return 'image';
    }
    if (strpos($name, '视频') !== false || strpos($name, '影视') !== false) {
        return 'video';
    }
    if (strpos($name, '写作') !== false || strpos($name, '文本') !== false || strpos($name, '文案') !== false) {
        return 'pen-tool';
    }
    if (strpos($name, '办公') !== false || strpos($name, '文档') !== false) {
        return 'briefcase';
    }
    if (strpos($name, '对话') !== false || strpos($name, '聊天') !== false || strpos($name, '助手') !== false) {
        return 'message-circle';
    }
    if (strpos($name, '编程') !== false || strpos($name, '开发') !== false || strpos($name, '代码') !== false) {
        return 'code-2';
    }
    if (strpos($name, '设计') !== false || strpos($name, 'ui') !== false || strpos($name, 'ux') !== false) {
        return 'palette';
    }
    if (strpos($name, '音频') !== false || strpos($name, '音乐') !== false || strpos($name, '声音') !== false) {
        return 'music';
    }
    if (strpos($name, '搜索') !== false) {
        return 'search';
    }
    if (strpos($name, '翻译') !== false) {
        return 'languages';
    }
    if (strpos($name, '学习') !== false || strpos($name, '教育') !== false || strpos($name, '课程') !== false) {
        return 'graduation-cap';
    }
    if (strpos($name, '数据') !== false || strpos($name, '分析') !== false) {
        return 'bar-chart-2';
    }
    if (strpos($name, '营销') !== false || strpos($name, '推广') !== false || strpos($name, 'seo') !== false) {
        return 'trending-up';
    }
    if (strpos($name, '生活') !== false || strpos($name, '日常') !== false) {
        return 'coffee';
    }
    if (strpos($name, '游戏') !== false || strpos($name, '娱乐') !== false) {
        return 'gamepad-2';
    }
    if (strpos($name, '健康') !== false || strpos($name, '医疗') !== false || strpos($name, '医学') !== false) {
        return 'heart-pulse';
    }
    if (strpos($name, '金融') !== false || strpos($name, '理财') !== false || strpos($name, '投资') !== false) {
        return 'landmark';
    }
    if (strpos($name, '法律') !== false || strpos($name, '律师') !== false) {
        return 'scale';
    }
    if (strpos($name, '电商') !== false || strpos($name, '购物') !== false) {
        return 'shopping-bag';
    }
    if (strpos($name, '社交') !== false || strpos($name, '媒体') !== false) {
        return 'share-2';
    }
    if (strpos($name, '新闻') !== false || strpos($name, '资讯') !== false) {
        return 'newspaper';
    }
    if (strpos($name, '3d') !== false || strpos($name, '建模') !== false || strpos($name, '模型') !== false) {
        return 'box';
    }
    if (strpos($name, 'ppt') !== false || strpos($name, '演示') !== false || strpos($name, '幻灯片') !== false) {
        return 'presentation';
    }
    if (strpos($name, '思维') !== false || strpos($name, '导图') !== false || strpos($name, '脑图') !== false) {
        return 'git-branch';
    }
    if (strpos($name, '笔记') !== false || strpos($name, '记录') !== false) {
        return 'sticky-note';
    }
    if (strpos($name, '阅读') !== false || strpos($name, '书籍') !== false) {
        return 'book-open';
    }
    if (strpos($name, '邮件') !== false || strpos($name, '邮箱') !== false) {
        return 'mail';
    }
    if (strpos($name, '天气') !== false) {
        return 'cloud-sun';
    }
    if (strpos($name, '旅行') !== false || strpos($name, '旅游') !== false) {
        return 'plane';
    }
    if (strpos($name, '美食') !== false || strpos($name, '菜谱') !== false) {
        return 'utensils';
    }
    if (strpos($name, '健身') !== false || strpos($name, '运动') !== false) {
        return 'dumbbell';
    }
    if (strpos($name, '宠物') !== false || strpos($name, '动物') !== false) {
        return 'cat';
    }
    if (strpos($name, '儿童') !== false || strpos($name, '育儿') !== false) {
        return 'baby';
    }
    if (strpos($name, '星座') !== false || strpos($name, '占卜') !== false) {
        return 'sparkles';
    }
    if (strpos($name, '简历') !== false || strpos($name, '求职') !== false || strpos($name, '招聘') !== false) {
        return 'file-text';
    }
    if (strpos($name, '论文') !== false || strpos($name, '学术') !== false) {
        return 'scroll-text';
    }
    if (strpos($name, '总结') !== false || strpos($name, '摘要') !== false) {
        return 'clipboard-list';
    }
    if (strpos($name, '抠图') !== false || strpos($name, '去背景') !== false) {
        return 'scissors';
    }
    if (strpos($name, '换脸') !== false || strpos($name, '人脸') !== false) {
        return 'scan-face';
    }
    if (strpos($name, '变声') !== false || strpos($name, '声音') !== false) {
        return 'mic';
    }
    if (strpos($name, '字幕') !== false || strpos($name, '歌词') !== false) {
        return 'subtitles';
    }
    if (strpos($name, '配音') !== false || strpos($name, '朗读') !== false) {
        return 'volume-2';
    }
    if (strpos($name, '修图') !== false || strpos($name, '美化') !== false) {
        return 'wand-2';
    }
    if (strpos($name, '压缩') !== false || strpos($name, '解压') !== false) {
        return 'archive';
    }
    if (strpos($name, '转换') !== false || strpos($name, '格式') !== false) {
        return 'refresh-cw';
    }
    if (strpos($name, '下载') !== false) {
        return 'download';
    }
    if (strpos($name, '检测') !== false || strpos($name, '查重') !== false) {
        return 'shield-check';
    }
    if (strpos($name, '修复') !== false || strpos($name, '恢复') !== false) {
        return 'wrench';
    }
    if (strpos($name, '增强') !== false || strpos($name, '高清') !== false) {
        return 'zoom-in';
    }
    if (strpos($name, '生成') !== false || strpos($name, '创建') !== false) {
        return 'sparkles';
    }
    if (strpos($name, '预测') !== false || strpos($name, '预报') !== false) {
        return 'eye';
    }
    if (strpos($name, '推荐') !== false || strpos($name, '精选') !== false) {
        return 'thumbs-up';
    }
    if (strpos($name, '排行') !== false || strpos($name, '榜单') !== false) {
        return 'trophy';
    }
    if (strpos($name, '新出') !== false || strpos($name, '最新') !== false) {
        return 'sparkles';
    }
    if (strpos($name, '大厂') !== false || strpos($name, '知名') !== false) {
        return 'building-2';
    }
    if (strpos($name, '开源') !== false || strpos($name, '免费') !== false) {
        return 'github';
    }
    if (strpos($name, '国产') !== false || strpos($name, '国内') !== false) {
        return 'flag';
    }
    if (strpos($name, '国外') !== false || strpos($name, '海外') !== false) {
        return 'globe';
    }
    if (strpos($name, '综合') !== false || strpos($name, '全部') !== false || strpos($name, '其他') !== false) {
        return 'layers';
    }
    if (strpos($name, '社区') !== false || strpos($name, '论坛') !== false) {
        return 'users';
    }
    if (strpos($name, '手册') !== false || strpos($name, '指南') !== false || strpos($name, '教程') !== false) {
        return 'book-open';
    }
    if (strpos($name, '提示词') !== false || strpos($name, 'prompt') !== false) {
        return 'terminal';
    }
    if (strpos($name, '智能体') !== false || strpos($name, 'agent') !== false) {
        return 'bot';
    }
    if (strpos($name, '浏览器') !== false) {
        return 'globe';
    }
    if (strpos($name, '导航') !== false) {
        return 'compass';
    }
    
    return 'folder-open';
}
}

get_header();
get_sidebar();
?>

<div class="main-content">
    <!-- 桌面端搜索区域 -->
    <div class="desktop-search-section">
        <!-- 搜索类型标签 -->
        <div class="search-tabs">
            <button type="button" class="search-tab active" data-mode="search">搜索</button>
            <button type="button" class="search-tab" data-mode="image">图片</button>
            <button type="button" class="search-tab" data-mode="site">站内</button>
            <button type="button" class="search-tab" data-mode="deepseek">DeepSeek搜索</button>
        </div>

        <!-- 搜索框 -->
        <div class="mobile-search-box">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-box" id="search-form-desktop">
                <input type="search" name="s" class="search-input" id="search-input-desktop" placeholder="百度一下" value="<?php echo get_search_query(); ?>">
                <button type="submit" aria-label="搜索" id="search-submit-desktop">
                    <i data-lucide="search"></i>
                </button>
            </form>
        </div>

        <!-- 搜索引擎选择 -->
        <div class="search-engines" id="search-engines-container-desktop">
            <a href="https://www.baidu.com/s?wd=" class="search-engine active" data-placeholder="百度一下">百度</a>
            <a href="https://www.bing.com/search?q=" class="search-engine" data-placeholder="必应搜索">Bing</a>
            <a href="https://www.google.com/search?q=" class="search-engine" data-placeholder="Google一下">Google</a>
            <a href="https://so.toutiao.com/search?keyword=" class="search-engine" data-placeholder="头条搜索">头条</a>
        </div>
    </div>

    <?php
    // 清除分类缓存，确保获取最新数据
    wp_cache_delete('all_ai_categories', 'terms');
    
    // 获取所有一级分类
    $parent_categories = get_terms(array(
        'taxonomy'   => 'ai_category',
        'parent'     => 0,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'update_term_meta_cache' => false, // 不更新元数据缓存
    ));

    if (!empty($parent_categories) && !is_wp_error($parent_categories)) :
        foreach ($parent_categories as $parent_cat) :
            // 获取子分类
            $child_categories = get_terms(array(
                'taxonomy'   => 'ai_category',
                'parent'     => $parent_cat->term_id,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
                'update_term_meta_cache' => false, // 不更新元数据缓存
            ));
            $has_children = !empty($child_categories) && !is_wp_error($child_categories);
            
            // 查询该分类下的网址
            $cat_ids = array($parent_cat->term_id);
            if ($has_children) {
                foreach ($child_categories as $child) {
                    $cat_ids[] = $child->term_id;
                }
            }
            
            // 每行4个，最多5行 = 20个
            $sites_query = new WP_Query(array(
                'post_type'      => 'ai_tool',
                'posts_per_page' => 20,
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'ai_category',
                        'field'    => 'term_id',
                        'terms'    => $cat_ids,
                    ),
                ),
                'orderby' => 'date',
                'order'   => 'DESC',
            ));
    ?>

    <!-- 分类区块 -->
    <section class="category-section" id="cat-<?php echo esc_attr($parent_cat->slug); ?>">
        <!-- 子分类Tab（含一级分类名称） -->
        <div class="subcategory-tabs">
            <button class="subcategory-tab tab-parent" data-filter="all">
                <span class="section-icon">
                    <i data-lucide="<?php echo esc_attr(navai_get_section_icon($parent_cat->name)); ?>"></i>
                </span>
                <?php echo esc_html($parent_cat->name); ?>
            </button>
            <?php if ($has_children) : ?>
            <?php $first_child = true; foreach ($child_categories as $child) : ?>
            <button class="subcategory-tab<?php if ($first_child) : ?> active<?php $first_child = false; endif; ?>" data-filter="<?php echo esc_attr($child->term_id); ?>">
                <?php echo esc_html($child->name); ?>
            </button>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($sites_query->have_posts()) : ?>
        <!-- 网址网格 -->
        <div class="sites-grid">
            <?php
            $rank = 0;
            while ($sites_query->have_posts() && $rank < 20) :
                $sites_query->the_post();
                $rank++;
                $post_id = get_the_ID();
                $website_url = get_post_meta($post_id, '_website_url', true);
                $site_icon_url = get_post_meta($post_id, '_site_icon_url', true);
                $icon_color = get_post_meta($post_id, '_icon_color', true);
                if (empty($icon_color)) {
                    $icon_color = wp_rand(1, 8);
                }
                
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

        <?php if ($sites_query->found_posts > 20) : ?>
        <div class="more-sites">
            <a href="<?php echo esc_url(get_term_link($parent_cat)); ?>" class="btn-more">
                查看全部 <?php echo $sites_query->found_posts; ?> 个网站 <i data-lucide="arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

        <?php else : ?>
        <div class="no-sites">
            <p>该分类下暂无网站</p>
        </div>
        <?php endif; ?>
    </section>

    <?php endforeach; ?>

<?php else : ?>
    <div class="no-categories">
        <i data-lucide="folder-x"></i>
        <p>暂无网址分类，请在后台添加</p>
    </div>
<?php endif; ?>
</div>

<?php get_footer(); ?>
