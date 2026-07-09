# NavAi WordPress 主题

一个复刻自 [faxianai.com](https://faxianai.com) 的专业AI导航网站WordPress主题。

## 主题信息

- **作者**: 老九
- **版本**: 1.28.0
- **许可证**: GPL-2.0+
- **最低WordPress版本**: 5.0
- **测试到WordPress版本**: 6.4

## 功能特性

- 🎨 **现代化UI设计** - 复刻faxianai.com的视觉风格
- 📱 **响应式布局** - 完美适配桌面端和移动端
- 🔍 **智能搜索** - 支持多种搜索引擎快捷切换
- 📂 **分类导航** - 左侧边栏分类快速定位
- 🃏 **卡片展示** - 精美的AI工具卡片布局
- 🏷️ **自定义文章类型** - 专用的AI工具管理
- ⚙️ **后台主题设置** - Logo、Favicon、页脚内容自定义
- ⚡ **性能优化** - CSS变量、懒加载、平滑动画

## 安装方法

1. 将 `NavAi` 文件夹上传到 WordPress 的 `/wp-content/themes/` 目录
2. 在 WordPress 后台 > 外观 > 主题 中激活 "NavAi 主题"
3. 进入后台 > **NavAi设置** 配置Logo、Favicon和页脚内容

## 目录结构

```
NavAi/
├── style.css                      # 主样式文件（含主题信息）
├── functions.php                  # 主题功能文件
├── header.php                     # 头部模板
├── footer.php                     # 底部模板
├── sidebar.php                    # 侧边栏模板
├── index.php                      # 主页模板
├── single.php                     # 单篇文章模板
├── page.php                       # 页面模板
├── search.php                     # 搜索结果模板
├── taxonomy-ai_category.php       # 分类归档模板
├── screenshot.png                 # 主题预览图
├── README.md                      # 说明文档
├── assets/
│   └── js/
│       └── main.js                # 主脚本文件
└── template-parts/
    └── content-ai-card.php        # AI卡片模板片段
```

## 后台设置

进入 WordPress 后台 > **NavAi设置**，可以配置：

### Logo设置
- **Logo类型**: 文字Logo 或 图片Logo
- **Logo文字**: 显示在Logo图标旁边的文字
- **图标文字**: Logo图标内显示的文字（1-4个字符）
- **Logo图片**: 上传自定义Logo图片

### Favicon设置
- 上传网站Favicon图标（建议32x32或64x64像素的PNG/ICO格式）

### 页脚设置
- **自定义页脚内容**: 支持HTML，显示在版权信息上方
- **版权信息**: 自定义版权文字
- **ICP备案号**: 添加备案号并自动链接到工信部网站

### 站点统计
- 自定义搜索框右侧显示的统计信息

## 使用说明

### 添加AI工具

1. 进入 WordPress 后台 > AI工具管理 > 添加新工具
2. 填写工具名称和简介
3. 设置特色图片（工具图标）
4. 在"AI工具详情"面板中填写：
   - 官网地址
   - 图标颜色（选择渐变色1-8）
   - 是否热门推荐
   - 是否新上线
5. 选择AI分类
6. 发布

### 管理分类

主题预设了以下AI分类：
- 大热门AI、TOP 10、AI操作手册、超级智能体
- 新出AI、大厂AI、写作AI、图像AI
- 设计AI、办公AI、对话AI、热门APP
- 视频AI、音频AI、求职招聘AI、编程AI
- 开发工具、搜索AI、模型AI、AI提示词、AI社区

可以在后台 > AI工具管理 > AI分类 中添加或修改。

### 设置导航菜单

1. 进入后台 > 外观 > 菜单
2. 创建新菜单并分配到"主导航菜单"位置
3. 添加页面链接到菜单

## 短代码

主题提供以下短代码方便在页面中使用：

### 显示热门AI工具
```
[hot_ai count="8"]
```

### 按分类显示AI工具
```
[category_ai category="写作AI" count="8"]
```

## 自定义

### 修改配色

编辑 `style.css` 中的 CSS 变量：

```css
:root {
    --primary-color: #E53935;      /* 主色调 */
    --brand-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);  /* 品牌渐变 */
    /* ... 其他变量 */
}
```

### 添加自定义样式

在子主题中创建 `style.css` 或使用 WordPress 定制器。

## 技术栈

- **CSS**: CSS Variables、Flexbox、Grid、动画
- **JavaScript**: jQuery、Intersection Observer
- **图标**: Lucide Icons
- **WordPress**: 自定义文章类型、分类法、元数据框

## 浏览器支持

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## 更新日志

### v1.28.0
- 菜单样式美化（顶部导航 + 侧边栏）
- 新增外链图片扫描采集功能
- 新增详情页正文广告位
- 修复广告位响应式显示问题

### v1.26.05
- 更新作者信息为"老九"
- 添加完整的后台主题设置面板
- 支持自定义Logo（文字/图片）
- 支持自定义Favicon
- 支持自定义页脚内容
- 优化代码结构和注释
- 修复已知bug

### v1.0.0
- 初始版本发布
- 复刻faxianai.com核心UI
- 实现AI工具管理功能
- 响应式布局支持
