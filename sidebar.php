<?php
/**
 * 侧边栏模板文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.26.87
 */

if (!defined('ABSPATH')) {
    exit;
}

// 清除分类缓存，确保获取最新数据
wp_cache_delete('all_ai_categories', 'terms');

// 获取所有网址分类（包括一级和二级分类）
$all_categories = get_terms(array(
    'taxonomy' => 'ai_category',
    'orderby' => 'name',
    'order' => 'ASC',
    'hide_empty' => false,
    'update_term_meta_cache' => false,
));

// 分离一级分类和二级分类
$categories = array();
$child_categories = array();

if (!empty($all_categories) && !is_wp_error($all_categories)) {
    foreach ($all_categories as $cat) {
        if ($cat->parent == 0) {
            $categories[] = $cat;
        } else {
            $child_categories[$cat->parent][] = $cat;
        }
    }
}

// 分类图标映射函数
function navai_get_category_icon($cat_name) {
    $name = strtolower($cat_name);
    
    // 热门相关
    if (strpos($name, '热门') !== false || strpos($name, 'top') !== false || strpos($name, '推荐') !== false) {
        return array('icon' => 'flame', 'color' => '#ff6b6b');
    }
    // 图像/图片
    if (strpos($name, '图像') !== false || strpos($name, '图片') !== false || strpos($name, '绘画') !== false || strpos($name, '画图') !== false) {
        return array('icon' => 'image', 'color' => '#4ecdc4');
    }
    // 视频
    if (strpos($name, '视频') !== false || strpos($name, '影视') !== false) {
        return array('icon' => 'video', 'color' => '#a55eea');
    }
    // 写作/文本
    if (strpos($name, '写作') !== false || strpos($name, '文本') !== false || strpos($name, '文案') !== false || strpos($name, '文章') !== false) {
        return array('icon' => 'pen-tool', 'color' => '#26de81');
    }
    // 办公
    if (strpos($name, '办公') !== false || strpos($name, '文档') !== false || strpos($name, '表格') !== false) {
        return array('icon' => 'briefcase', 'color' => '#fd9644');
    }
    // 对话/聊天
    if (strpos($name, '对话') !== false || strpos($name, '聊天') !== false || strpos($name, '助手') !== false) {
        return array('icon' => 'message-circle', 'color' => '#45aaf2');
    }
    // 编程/开发
    if (strpos($name, '编程') !== false || strpos($name, '开发') !== false || strpos($name, '代码') !== false) {
        return array('icon' => 'code-2', 'color' => '#5f27cd');
    }
    // 设计
    if (strpos($name, '设计') !== false || strpos($name, 'ui') !== false || strpos($name, 'ux') !== false) {
        return array('icon' => 'palette', 'color' => '#ff9ff3');
    }
    // 音频/音乐
    if (strpos($name, '音频') !== false || strpos($name, '音乐') !== false || strpos($name, '声音') !== false) {
        return array('icon' => 'music', 'color' => '#00d2d3');
    }
    // 搜索
    if (strpos($name, '搜索') !== false) {
        return array('icon' => 'search', 'color' => '#ff6b6b');
    }
    // 翻译
    if (strpos($name, '翻译') !== false) {
        return array('icon' => 'languages', 'color' => '#54a0ff');
    }
    // 学习/教育
    if (strpos($name, '学习') !== false || strpos($name, '教育') !== false || strpos($name, '课程') !== false) {
        return array('icon' => 'graduation-cap', 'color' => '#5f27cd');
    }
    // 数据分析
    if (strpos($name, '数据') !== false || strpos($name, '分析') !== false) {
        return array('icon' => 'bar-chart-2', 'color' => '#00d2d3');
    }
    // 营销
    if (strpos($name, '营销') !== false || strpos($name, '推广') !== false || strpos($name, 'seo') !== false) {
        return array('icon' => 'trending-up', 'color' => '#ff9f43');
    }
    // 生活
    if (strpos($name, '生活') !== false || strpos($name, '日常') !== false) {
        return array('icon' => 'coffee', 'color' => '#ff6b6b');
    }
    // 游戏
    if (strpos($name, '游戏') !== false || strpos($name, '娱乐') !== false) {
        return array('icon' => 'gamepad-2', 'color' => '#a29bfe');
    }
    // 健康/医疗
    if (strpos($name, '健康') !== false || strpos($name, '医疗') !== false || strpos($name, '医学') !== false) {
        return array('icon' => 'heart-pulse', 'color' => '#ff6b6b');
    }
    // 金融
    if (strpos($name, '金融') !== false || strpos($name, '理财') !== false || strpos($name, '投资') !== false) {
        return array('icon' => 'landmark', 'color' => '#26de81');
    }
    // 法律
    if (strpos($name, '法律') !== false || strpos($name, '律师') !== false) {
        return array('icon' => 'scale', 'color' => '#778ca3');
    }
    // 电商
    if (strpos($name, '电商') !== false || strpos($name, '购物') !== false || strpos($name, '淘宝') !== false) {
        return array('icon' => 'shopping-bag', 'color' => '#ff9f43');
    }
    // 社交媒体
    if (strpos($name, '社交') !== false || strpos($name, '媒体') !== false) {
        return array('icon' => 'share-2', 'color' => '#54a0ff');
    }
    // 新闻
    if (strpos($name, '新闻') !== false || strpos($name, '资讯') !== false) {
        return array('icon' => 'newspaper', 'color' => '#ff6b6b');
    }
    // 3D/建模
    if (strpos($name, '3d') !== false || strpos($name, '建模') !== false || strpos($name, '模型') !== false) {
        return array('icon' => 'box', 'color' => '#a29bfe');
    }
    // 演示/PPT
    if (strpos($name, 'ppt') !== false || strpos($name, '演示') !== false || strpos($name, '幻灯片') !== false) {
        return array('icon' => 'presentation', 'color' => '#ff9f43');
    }
    // 思维导图
    if (strpos($name, '思维') !== false || strpos($name, '导图') !== false || strpos($name, '脑图') !== false) {
        return array('icon' => 'git-branch', 'color' => '#00d2d3');
    }
    // 笔记
    if (strpos($name, '笔记') !== false || strpos($name, '记录') !== false) {
        return array('icon' => 'sticky-note', 'color' => '#feca57');
    }
    // 阅读
    if (strpos($name, '阅读') !== false || strpos($name, '书籍') !== false || strpos($name, '电子书') !== false) {
        return array('icon' => 'book-open', 'color' => '#5f27cd');
    }
    // 邮件
    if (strpos($name, '邮件') !== false || strpos($name, '邮箱') !== false) {
        return array('icon' => 'mail', 'color' => '#54a0ff');
    }
    // 天气
    if (strpos($name, '天气') !== false) {
        return array('icon' => 'cloud-sun', 'color' => '#feca57');
    }
    // 旅行
    if (strpos($name, '旅行') !== false || strpos($name, '旅游') !== false || strpos($name, '出行') !== false) {
        return array('icon' => 'plane', 'color' => '#00d2d3');
    }
    // 美食
    if (strpos($name, '美食') !== false || strpos($name, '菜谱') !== false || strpos($name, '烹饪') !== false) {
        return array('icon' => 'utensils', 'color' => '#ff6b6b');
    }
    // 健身
    if (strpos($name, '健身') !== false || strpos($name, '运动') !== false) {
        return array('icon' => 'dumbbell', 'color' => '#26de81');
    }
    // 宠物
    if (strpos($name, '宠物') !== false || strpos($name, '动物') !== false) {
        return array('icon' => 'cat', 'color' => '#ff9ff3');
    }
    // 儿童/育儿
    if (strpos($name, '儿童') !== false || strpos($name, '育儿') !== false || strpos($name, '宝宝') !== false) {
        return array('icon' => 'baby', 'color' => '#ff9ff3');
    }
    // 星座/占卜
    if (strpos($name, '星座') !== false || strpos($name, '占卜') !== false || strpos($name, '塔罗') !== false) {
        return array('icon' => 'sparkles', 'color' => '#a29bfe');
    }
    // 简历
    if (strpos($name, '简历') !== false || strpos($name, '求职') !== false || strpos($name, '招聘') !== false) {
        return array('icon' => 'file-text', 'color' => '#54a0ff');
    }
    // 论文/学术
    if (strpos($name, '论文') !== false || strpos($name, '学术') !== false || strpos($name, '研究') !== false) {
        return array('icon' => 'scroll-text', 'color' => '#5f27cd');
    }
    // 总结
    if (strpos($name, '总结') !== false || strpos($name, '摘要') !== false) {
        return array('icon' => 'clipboard-list', 'color' => '#00d2d3');
    }
    // 抠图/去背景
    if (strpos($name, '抠图') !== false || strpos($name, '去背景') !== false || strpos($name, '背景') !== false) {
        return array('icon' => 'scissors', 'color' => '#ff6b6b');
    }
    // 换脸
    if (strpos($name, '换脸') !== false || strpos($name, '人脸') !== false) {
        return array('icon' => 'scan-face', 'color' => '#ff9ff3');
    }
    // 变声
    if (strpos($name, '变声') !== false || strpos($name, '声音') !== false) {
        return array('icon' => 'mic', 'color' => '#a29bfe');
    }
    // 字幕
    if (strpos($name, '字幕') !== false || strpos($name, '歌词') !== false) {
        return array('icon' => 'subtitles', 'color' => '#feca57');
    }
    // 配音
    if (strpos($name, '配音') !== false || strpos($name, '朗读') !== false) {
        return array('icon' => 'volume-2', 'color' => '#54a0ff');
    }
    // 修图
    if (strpos($name, '修图') !== false || strpos($name, '美化') !== false || strpos($name, '滤镜') !== false) {
        return array('icon' => 'wand-2', 'color' => '#ff9ff3');
    }
    // 压缩
    if (strpos($name, '压缩') !== false || strpos($name, '解压') !== false) {
        return array('icon' => 'archive', 'color' => '#778ca3');
    }
    // 转换
    if (strpos($name, '转换') !== false || strpos($name, '格式') !== false) {
        return array('icon' => 'refresh-cw', 'color' => '#00d2d3');
    }
    // 下载
    if (strpos($name, '下载') !== false) {
        return array('icon' => 'download', 'color' => '#26de81');
    }
    // 检测/查重
    if (strpos($name, '检测') !== false || strpos($name, '查重') !== false || strpos($name, '原创') !== false) {
        return array('icon' => 'shield-check', 'color' => '#26de81');
    }
    // 修复
    if (strpos($name, '修复') !== false || strpos($name, '恢复') !== false) {
        return array('icon' => 'wrench', 'color' => '#fd9644');
    }
    // 增强
    if (strpos($name, '增强') !== false || strpos($name, '高清') !== false || strpos($name, '放大') !== false) {
        return array('icon' => 'zoom-in', 'color' => '#54a0ff');
    }
    // 生成
    if (strpos($name, '生成') !== false || strpos($name, '创建') !== false || strpos($name, '制作') !== false) {
        return array('icon' => 'sparkles', 'color' => '#a29bfe');
    }
    // 预测
    if (strpos($name, '预测') !== false || strpos($name, '预报') !== false) {
        return array('icon' => 'eye', 'color' => '#5f27cd');
    }
    // 推荐
    if (strpos($name, '推荐') !== false || strpos($name, '精选') !== false) {
        return array('icon' => 'thumbs-up', 'color' => '#ff6b6b');
    }
    // 排行榜
    if (strpos($name, '排行') !== false || strpos($name, '榜单') !== false) {
        return array('icon' => 'trophy', 'color' => '#feca57');
    }
    // 新出/最新
    if (strpos($name, '新出') !== false || strpos($name, '最新') !== false || strpos($name, '新品') !== false) {
        return array('icon' => 'sparkles', 'color' => '#ff9ff3');
    }
    // 大厂/知名
    if (strpos($name, '大厂') !== false || strpos($name, '知名') !== false || strpos($name, '品牌') !== false) {
        return array('icon' => 'building-2', 'color' => '#54a0ff');
    }
    // 开源
    if (strpos($name, '开源') !== false || strpos($name, '免费') !== false) {
        return array('icon' => 'github', 'color' => '#778ca3');
    }
    // 国产
    if (strpos($name, '国产') !== false || strpos($name, '国内') !== false || strpos($name, '中国') !== false) {
        return array('icon' => 'flag', 'color' => '#ff6b6b');
    }
    // 国外
    if (strpos($name, '国外') !== false || strpos($name, '海外') !== false || strpos($name, '国际') !== false) {
        return array('icon' => 'globe', 'color' => '#54a0ff');
    }
    // 综合
    if (strpos($name, '综合') !== false || strpos($name, '全部') !== false || strpos($name, '其他') !== false) {
        return array('icon' => 'layers', 'color' => '#778ca3');
    }
    
    // 默认图标
    return array('icon' => 'folder-open', 'color' => '#a0aec0');
}

// 获取当前分类
$current_id = 0;
if (is_tax('ai_category')) {
    $current_term = get_queried_object();
    if ($current_term) {
        $current_id = $current_term->term_id;
    }
}
?>

<!-- 移动端侧边栏遮罩 -->
<div class="sidebar-overlay"></div>

<!-- 展开按钮（侧边栏收起时显示） -->
<div class="sidebar-expand-btn" id="sidebar-expand-btn" aria-label="展开侧边栏">
    <i data-lucide="chevron-right"></i>
</div>

<aside class="sidebar" role="complementary">
    <nav class="sidebar-nav" aria-label="分类导航">
        <!-- 首页 -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="sidebar-item <?php echo is_front_page() ? 'active' : ''; ?>">
            <i data-lucide="home" style="color: #ff6b6b;"></i>
            <span>首页</span>
        </a>

        <!-- 分类导航 -->
        <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
            <?php foreach ($categories as $cat) : ?>
                <?php
                $cat_info = navai_get_category_icon($cat->name);
                // 从已获取的分类中查找子分类
                $children = isset($child_categories[$cat->term_id]) ? $child_categories[$cat->term_id] : array();
                $has_children = !empty($children);
                $is_active = ($current_id === $cat->term_id);
                ?>
                <div class="sidebar-item-wrapper <?php echo $has_children ? 'has-children' : ''; ?>">
                    <div class="sidebar-item <?php echo $is_active ? 'active' : ''; ?>" data-cat-id="<?php echo esc_attr($cat->term_id); ?>">
                        <i data-lucide="<?php echo esc_attr($cat_info['icon']); ?>" style="color: <?php echo esc_attr($cat_info['color']); ?>"></i>
                        <span><?php echo esc_html($cat->name); ?></span>
                        <?php if ($has_children) : ?>
                            <i data-lucide="chevron-down" class="sidebar-arrow"></i>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($has_children) : ?>
                        <div class="sidebar-submenu">
                            <?php foreach ($children as $child) : 
                                $child_info = navai_get_category_icon($child->name);
                            ?>
                                <a href="<?php echo esc_url(get_term_link($child)); ?>" class="sidebar-submenu-item <?php echo ($current_id === $child->term_id) ? 'active' : ''; ?>">
                                    <i data-lucide="<?php echo esc_attr($child_info['icon']); ?>" style="color: <?php echo esc_attr($child_info['color']); ?>"></i>
                                    <span><?php echo esc_html($child->name); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="sidebar-item-link"></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- 收起按钮 -->
        <div class="sidebar-collapse">
            <i data-lucide="chevron-up"></i>
            <span>收起</span>
        </div>
    </nav>
</aside>
