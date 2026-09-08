# NavAi WordPress Theme
# NavAi WordPress 主题

A modern, card-based directory WordPress theme for AI tools and resource navigation websites.
一款现代化的卡片式 AI 工具与资源导航 WordPress 主题。

## Theme Info / 主题信息

- **Author / 作者**: 老九
- **Version / 版本**: 1.1.1
- **License / 许可证**: GPL-2.0+
- **Requires WordPress / 最低 WordPress 版本**: 5.0
- **Tested up to / 测试到**: 6.7
- **Requires PHP / 最低 PHP 版本**: 7.4

## Features / 功能特性

### Frontend / 前端
- Card-based responsive grid layout with hover effects / 卡片式响应式网格布局，带悬停效果
- Left sidebar category navigation with auto-assigned icons, collapse/expand icon bar, scroll-spy / 左侧边栏分类导航，自动分配图标，收起/展开图标栏，滚动高亮
- Multi-engine search bar (Baidu, Bing, Google, DeepSeek) / 多引擎搜索栏（百度、必应、Google、DeepSeek）
- Personal center page: bookmarks, ratings, submitted URLs with two-column layout / 个人中心：收藏/评分/投稿，双栏布局
- 1-5 star AJAX rating system with cookie dedup / 1-5 星 AJAX 评分系统，cookie 去重
- Bookmark collection with cookie→user_meta migration / 书签收藏，cookie→user_meta 迁移
- Threaded comments with user agent and location display / 嵌套评论，显示用户代理和地理位置
- Frontend contribution page / 前端投稿页面

### Admin / 后台
- Dashboard with statistics cards, trend charts (7/30/90 days), Top 10, category pie chart / 仪表盘：统计卡片、趋势折线图、Top10、分类饼图
- Dead link detection with WP-Cron and email notification / 定时死链检测，邮件通知
- CSV data export / CSV 数据导出
- Custom admin columns: icon, URL, hot/new marks, visit count / 自定义后台列：图标、网址、标记、访问数
- Duplicate URL detection with keep/delete modal / 重复检测，保留/删除模态框
- Bulk edit: category, tags, recommend level, status, delete / 批量编辑：分类/标记/推荐位/状态/删除
- Recommend positions (recommend/fixed-top/carousel) / 推荐位功能（推荐/置顶/轮播）
- Navigation site import: crawl URL, auto-extract links, smart category matching / 导航站采集：抓取URL、自动提取链接、智能分类匹配
- Batch add with auto site info fetch (title, description, favicon) / 批量添加并自动采集站点信息

### SEO & Tracking / SEO 与统计
- JSON-LD structured data for single posts and homepage / JSON-LD 结构化数据
- AI tool category RSS feed / 分类 RSS 订阅
- View count and click count tracking (template_redirect + sendBeacon) / 浏览量和点击数统计

### Theme Update System / 主题更新系统
- Built-in update check with license management / 内置更新检查与授权管理
- License error admin notices (expired/disabled/invalid/no access/domain limit) / 授权错误后台通知
- Manual check button and force check URL parameter / 手动检查按钮和强制检查参数
- License management page (register, query, status) / 授权管理页面

### Other / 其他
- Logo, favicon, footer content, ad code configurable from Theme Settings / Logo、Favicon、页脚内容、广告代码可在主题设置中配置
- Homepage grid columns setting (3-8, default 5) / 首页列数设置（3-8，默认5）
- Custom category sorting via admin panel / 后台面板自定义分类排序
- Multi-source favicon fallback chain (/favicon.ico → iowen API → DuckDuckGo → Google) / 多源 favicon 回退链
- Global JS icon fallback (size check, timeout, MutationObserver) / 全局 JS 图标回退
- Full internationalization support (translation-ready) / 完整国际化支持（翻译就绪）
- Mobile-optimized admin list (WordPress native card layout below 782px) / 移动端优化后台列表

## Installation / 安装

1. Upload the `navai` folder to `/wp-content/themes/` / 将 `navai` 文件夹上传到 `/wp-content/themes/`
2. Activate the theme via Appearance > Themes / 在"外观 > 主题"中激活
3. Go to NavAi Settings to configure logo, favicon, footer content, and ad code / 进入 NavAi 设置配置 Logo、Favicon、页脚内容和广告代码
4. Register a license key at Appearance > 高级版授权 to enable commercial version updates / 在"外观 > 高级版授权"注册授权码以启用商业版更新

## Directory Structure / 目录结构

```
navai/
├── style.css                      # Main stylesheet (theme header) / 主样式文件（含主题信息）
├── functions.php                  # Theme functions / 主题功能
├── header.php                     # Header template / 头部模板
├── footer.php                     # Footer template / 底部模板
├── sidebar.php                    # Sidebar template / 侧边栏模板
├── index.php                      # Homepage template / 首页模板
├── single.php                     # Single post template / 单篇文章模板
├── page.php                       # Page template / 页面模板
├── search.php                     # Search results / 搜索结果模板
├── comments.php                   # Comments template / 评论模板
├── taxonomy-ai_category.php       # Category archive / 分类归档模板
├── page-my-center.php             # Personal center page / 个人中心页面
├── template-contribute.php        # Frontend contribute page / 前端投稿模板
├── template-detail.php            # Detail page template / 详情页模板
├── screenshot.png                 # Theme preview / 主题预览图
├── readme.txt                     # WordPress.org readme
├── license.txt                    # GPL-2.0 license / GPL-2.0 许可证
├── languages/                     # Translation files / 翻译文件目录
├── assets/
│   ├── js/
│   │   ├── main.js                # Main script / 主脚本
│   │   └── lucide.min.js          # Lucide Icons
│   └── images/
│       └── placeholder-screenshot.jpg
├── includes/
│   └── class-navai-dashboard.php  # Admin dashboard class / 后台仪表盘类
└── template-parts/
    └── content-ai-card.php        # Card template part / 卡片模板片段
```

## Customization / 自定义

### Color Scheme / 配色方案

Edit CSS variables in `style.css` / 编辑 `style.css` 中的 CSS 变量：

```css
:root {
    --primary-color: #E53935;
    --brand-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Child Theme / 子主题

Create a child theme and override `style.css` or use the WordPress Customizer. / 创建子主题覆盖 `style.css`，或使用 WordPress 定制器。

## Browser Support / 浏览器支持

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Tech Stack / 技术栈

- **CSS**: CSS Variables, Flexbox, Grid, Animations
- **JavaScript**: jQuery, Intersection Observer, MutationObserver, sendBeacon
- **Icons / 图标**: Lucide Icons
- **PHP**: WordPress hooks (actions/filters), Transients API, WP-Cron, REST API meta
