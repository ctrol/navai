=== NavAi ===
Contributors: ctrol
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, card-based directory theme for AI tools and resource navigation websites. / 一款现代化的卡片式 AI 工具与资源导航网站主题。

== Description ==

**EN:** NavAi is a clean and modern WordPress directory theme built for AI tools and resource navigation sites. It features a card-based content layout, sidebar category navigation, multi-engine search, a personal center with bookmarks and ratings, a powerful admin dashboard with statistics and charts, navigation site crawling import, batch URL management, structured data SEO, and a built-in theme update system with license management.

**CN:** NavAi 是一款简洁现代的 WordPress 目录主题，专为 AI 工具和资源导航网站设计。包含卡片式内容布局、侧边栏分类导航、多引擎搜索、个人中心（收藏/评分/投稿）、功能丰富的后台仪表盘（统计图表/死链检测/CSV导出）、导航站采集导入、批量网址管理、结构化数据 SEO、以及内置主题更新与授权管理系统。

= Key Features / 核心特性 =

* **Card-Based Layout / 卡片式布局** - Clean tool cards with hover effects, responsive grid, auto favicon with multi-source fallback / 精洁的工具卡片，悬停效果，响应式网格，多源 favicon 自动回退
* **Sidebar Category Navigation / 侧边栏分类导航** - Hierarchical category tree with auto-assigned icons, collapse/expand icon bar, scroll-spy highlighting / 分层分类树，自动分配图标，收起/展开图标栏，滚动高亮
* **Multi-Engine Search / 多引擎搜索** - Built-in search bar supporting Baidu, Bing, Google, and DeepSeek with tab switching / 内置搜索栏，支持百度、必应、Google 和 DeepSeek 标签切换
* **Personal Center / 个人中心** - Frontend user page with bookmarks, ratings, and submitted URLs tabs, responsive two-column layout / 前端用户页面，收藏/评分/投稿三标签页，响应式双栏布局
* **Admin Dashboard / 后台仪表盘** - Statistics overview, trend charts (7/30/90 days), Top 10 sites, category pie chart, dead link detection, CSV export / 统计概览、趋势折线图、热门排行、分类饼图、死链检测、CSV 导出
* **Navigation Site Import / 导航站采集** - Crawl any navigation site URL, auto-extract tool links, smart category matching, multi-page support, SSRF protection / 采集任意导航站，自动提取工具链接，智能分类匹配，多页抓取，SSRF 防护
* **Batch Management / 批量管理** - Batch add with auto site info fetch, duplicate detection, bulk edit (category/tags/recommend/status) / 批量添加并自动采集站点信息、重复检测、批量编辑
* **Rating & Bookmark System / 评分与收藏** - 1-5 star AJAX ratings, bookmark collection, cookie dedup, user meta migration / 1-5 星 AJAX 评分、书签收藏、cookie 去重、用户数据迁移
* **SEO / 搜索引擎优化** - JSON-LD structured data, AI tool category RSS feed, view/click tracking / JSON-LD 结构化数据、分类 RSS 订阅、浏览/点击统计
* **Theme Update System / 主题更新系统** - Built-in update check with license management, admin notices for license errors, manual check button / 内置更新检查与授权管理、授权错误后台通知、手动检查按钮
* **Responsive Design / 响应式设计** - Fully adaptive layout with mobile-optimized admin list, dedicated mobile menu / 完全自适应布局，移动端优化后台列表，专用移动端菜单
* **Translation Ready / 国际化** - Full internationalization support with text domain and languages directory / 完整的国际化支持，包含文本域和语言目录

= Installation / 安装 =

1. Upload the theme folder to `/wp-content/themes/` / 将主题文件夹上传到 `/wp-content/themes/`
2. Activate the theme through the 'Themes' menu in WordPress / 在 WordPress 后台"外观 > 主题"中激活
3. Go to NavAi Settings to configure logo, favicon, footer content, and ad code / 进入 NavAi 设置配置 Logo、Favicon、页脚内容和广告代码
4. Register a license key at Appearance > 高级版授权 to enable commercial version updates / 在"外观 > 高级版授权"注册授权码以启用商业版更新

== Frequently Asked Questions / 常见问题 ==

= How do I change the sidebar category order? / 如何修改侧边栏分类排序？ =
Go to Theme Settings > General Settings, find the "Category Order" field, and enter category IDs separated by commas (e.g., 12,5,8,3). / 进入主题设置 > 通用设置，找到"分类排序"字段，输入分类 ID，用英文逗号分隔（如 12,5,8,3）。

= How do I set the homepage grid columns? / 如何设置首页每行网址数？ =
Go to Theme Settings > General Settings, find the "首页每行网址数" field, and enter a number from 3 to 8 (default 5). / 进入主题设置 > 通用设置，找到"首页每行网址数"字段，输入 3-8 的数字（默认 5）。

= How do I import links from another navigation site? / 如何从其他导航站导入链接？ =
Go to the admin URL list page, click "导入导航站", enter the navigation site URL, and the system will crawl and extract all tool links automatically. / 进入后台网址列表页，点击"导入导航站"，输入导航站 URL，系统将自动抓取并提取所有工具链接。

= How do I check for theme updates? / 如何检查主题更新？ =
Go to Theme Settings > General Settings and click the "检查更新" button, or visit the dashboard which auto-checks. Register a license key at Appearance > 高级版授权 to receive commercial version updates. / 进入主题设置 > 通用设置点击"检查更新"按钮，或访问仪表盘自动检查。在"外观 > 高级版授权"注册授权码以接收商业版更新。

== Changelog / 更新日志 ==

= 1.1.1 =
* 高级版授权页面全面优化：左右两栏等宽布局（左栏授权状态+更换授权，右栏购买引导+功能对比表），购买引导移至顶部，窄屏自动堆叠。
* 「NavAi授权」菜单改名为「高级版授权」，统一所有授权相关提示文案。
* 新增 Pro 版购买引导：功能对比表 20 项功能对比，微信联系方式（meshfuture），39.9元无加密可换域名。

= 1.1.0 =
* 版本号重置为 1.1.0，统一版本管理体系，合并所有历史更新日志。
* 更新检测与授权管理：重写版本检测逻辑（优先激活主题→slug目录→Theme Slug扫描），支持 `NAVAI_UPDATE_PRO_DIRS` 自定义商业版目录，License 错误通知系统（admin_notices 中文提示），动态授权 Key 读取，手动检查按钮，完整授权管理后台。
* 个人中心与书签：双栏布局个人中心页面（收藏/评分/投稿三标签），分页 Tab 三层冗余持久化，书签 cookie→user_meta 迁移，1-5 星 AJAX 评分系统。
* 后台仪表盘：统计概览卡片、趋势折线图、Top10 排行、分类饼图、快捷操作、死链检测、CSV 导出，自定义后台列，重复检测，批量编辑，推荐位功能，定时死链 WP-Cron。
* 导航站采集：URL 自动抓取、智能分类匹配（12 条默认规则）、多页抓取、OneNav data-url 提取、SSRF 防护、编码转换。
* 网站图标：多源 favicon 回退链（/favicon.ico→一文API→DuckDuckGo→Google），JS 全局图标回退检测（尺寸检查/超时/MutationObserver），Gutenberg REST API meta 兼容。
* SEO：JSON-LD 结构化数据、分类 RSS 订阅、浏览/点击统计（template_redirect + sendBeacon）。
* 主题设置：首页列数设置（3-8）、卡片标题截断、侧边栏收起图标栏、评论登录拦截、国际化支持、嵌套评论。
* 移动端适配：782px 以下原生卡片布局、table block 化脱离表格布局、批量添加页适配。
* 功能修复：广告代码 wp_unslash 保留 script、批量编辑参数冲突修复、主题激活钩子修正、Toast 通知替换 alert。

== Screenshots / 截图 ==

1. Homepage with category sidebar and tool cards / 带分类侧边栏和工具卡片的首页
2. Tool detail page with sidebar / 带侧边栏的工具详情页
3. Mobile responsive layout / 移动端响应式布局
4. Admin dashboard with statistics and charts / 后台仪表盘统计与图表
5. Personal center with bookmarks and ratings / 个人中心收藏与评分

== Upgrade Notice / 升级通知 ==

= 1.1.0 =
版本号重置为 1.1.0，合并所有历史更新日志，统一版本管理体系。建议所有用户升级。/ Version reset to 1.1.0, all historical changelogs merged, unified version management. Recommended for all users.
