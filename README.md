# NavAi WordPress Theme
# NavAi WordPress 主题

A modern, card-based directory WordPress theme for AI tools and resource navigation websites.
一款现代化的卡片式 AI 工具与资源导航 WordPress 主题。

## Theme Info / 主题信息

- **Author / 作者**: 老九
- **Version / 版本**: 1.29.22
- **License / 许可证**: GPL-2.0+
- **Requires WordPress / 最低 WordPress 版本**: 5.0
- **Tested up to / 测试到**: 6.7
- **Requires PHP / 最低 PHP 版本**: 7.4

## Features / 功能特性

- Card-based responsive grid layout with hover effects / 卡片式响应式网格布局，带悬停效果
- Left sidebar category navigation with auto-assigned icons and scroll-spy highlighting / 左侧边栏分类导航，自动分配图标，滚动高亮
- Multi-engine search bar (Baidu, Bing, Google, DeepSeek) / 多引擎搜索栏（百度、必应、Google、DeepSeek）
- Threaded comments with user agent and location display / 嵌套评论，显示用户代理和地理位置
- Logo, favicon, and footer content configurable from Theme Settings / Logo、Favicon、页脚内容可在主题设置中配置
- Custom category sorting via admin panel / 后台面板自定义分类排序
- Full internationalization support (translation-ready) / 完整国际化支持（翻译就绪）

## Installation / 安装

1. Upload the `NavAi` folder to `/wp-content/themes/` / 将 `NavAi` 文件夹上传到 `/wp-content/themes/`
2. Activate the theme via Appearance > Themes / 在"外观 > 主题"中激活
3. Go to NavAi Settings to configure logo, favicon, and footer content / 进入 NavAi 设置配置 Logo、Favicon 和页脚内容

## Directory Structure / 目录结构

```
NavAi/
├── style.css                      # Main stylesheet / 主样式文件（含主题信息）
├── functions.php                  # Theme functions / 主题功能
├── header.php                     # Header template / 头部模板
├── footer.php                     # Footer template / 底部模板
├── sidebar.php                    # Sidebar template / 侧边栏模板
├── index.php                      # Homepage template / 首页模板
├── single.php                     # Single post template / 单篇文章模板
├── page.php                       # Page template / 页面模板
├── search.php                     # Search results / 搜索结果模板
├── taxonomy-ai_category.php       # Category archive / 分类归档模板
├── screenshot.png                 # Theme preview / 主题预览图
├── readme.txt                     # WordPress.org readme
├── license.txt                    # GPL-2.0 license / GPL-2.0 许可证
├── languages/                     # Translation files / 翻译文件目录
├── assets/
│   ├── js/
│   │   ├── main.js                # Main script / 主脚本
│   │   └── lucide.min.js          # Lucide Icons
│   ├── css/
│   └── images/
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
    /* ... */
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
- **JavaScript**: jQuery, Intersection Observer
- **Icons / 图标**: Lucide Icons