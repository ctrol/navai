=== NavAi ===
Contributors: ctrol
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, card-based directory theme for AI tools and resource navigation websites. / 一款现代化的卡片式 AI 工具与资源导航网站主题。

== Description ==

**EN:** NavAi is a clean and modern WordPress directory theme built for AI tools and resource navigation sites. It provides an intuitive browsing experience with a left sidebar for category navigation and a card-based content layout that works beautifully across all screen sizes.

**CN:** NavAi 是一款简洁现代的 WordPress 目录主题，专为 AI 工具和资源导航网站设计。它提供直观的浏览体验，左侧边栏用于分类导航，卡片式内容布局在各屏幕尺寸上均表现优秀。

= Key Features / 核心特性 =

* **Card-Based Layout / 卡片式布局** - Clean tool cards with hover effects, responsive grid for desktop, tablet, and mobile / 精洁的工具卡片，带悬停效果，响应式网格适配桌面、平板和手机
* **Sidebar Category Navigation / 侧边栏分类导航** - Hierarchical category tree with auto-assigned icons, hover expand/collapse, and scroll-spy highlighting / 分层分类树，自动分配图标，悬停展开/折叠，滚动高亮
* **Multi-Engine Search / 多引擎搜索** - Built-in search bar supporting Baidu, Bing, Google, and DeepSeek with tab switching / 内置搜索栏，支持百度、必应、Google 和 DeepSeek 标签切换
* **Responsive Design / 响应式设计** - Fully adaptive layout with dedicated mobile menu, optimized for all devices / 完全自适应布局，专用移动端菜单，针对所有设备优化
* **Customizer Ready / 可定制** - Logo, favicon, footer content, and category sorting configurable from Theme Settings / Logo、Favicon、页脚内容和分类排序均可从主题设置中配置
* **Nested Comments / 嵌套评论** - Threaded comment system with user agent and location display / 嵌套评论系统，显示用户代理和地理位置
* **Translation Ready / 国际化** - Full internationalization support with text domain and languages directory / 完整的国际化支持，包含文本域和语言目录

= Installation / 安装 =

1. Upload the theme folder to `/wp-content/themes/` / 将主题文件夹上传到 `/wp-content/themes/`
2. Activate the theme through the 'Themes' menu in WordPress / 在 WordPress 后台"外观 > 主题"中激活
3. Go to NavAi Settings to configure logo, favicon, and footer content / 进入 NavAi 设置配置 Logo、Favicon 和页脚内容

== Frequently Asked Questions / 常见问题 ==

= How do I change the sidebar category order? / 如何修改侧边栏分类排序？ =
Go to Theme Settings > General Settings, find the "Category Order" field, and enter category IDs separated by commas (e.g., 12,5,8,3). / 进入主题设置 > 通用设置，找到"分类排序"字段，输入分类 ID，用英文逗号分隔（如 12,5,8,3）。

== Changelog / 更新日志 ==

= 1.1.0 =
版本号重置为 1.1.0，统一版本管理体系。以下为合并后的完整更新日志：

【更新检测与授权管理】
* 重写更新检测集成代码：`navai_get_theme_version()` 优先检测当前激活主题（升级后目录名变更时最可靠）；`navai_get_check_version()` 支持 `NAVAI_UPDATE_PRO_DIRS` 常量自定义商业版目录，修复 `$slug` 未定义变量 bug。
* 新增 License 错误管理通知系统：检测到 license_error 时写入 transient，通过 `admin_notices` 钩子在后台显示中文错误提示（过期/禁用/无效/无权限/域名超限）。
* 授权注册成功后直接调用 `delete_site_transient('update_themes')`，确保立即生效。
* AJAX 检查更新按钮逻辑同步更新：license_error 错误码统一为 `license_expired/license_disabled/license_not_found/license_no_access/license_domain_limit`，跳过非错误状态。
* 重构授权 Key 加载方式：从文件加载时 `define()` 改为在更新检查过滤器回调内动态 `get_option()` 读取。
* 修复更新检测被 license_error 阻断：AJAX handler 先检查 `update_available` 再做授权验证，确保更新始终被检测到。
* 修复 `delete_site_transient` 在 `wp_send_json_success` 之后执行导致不生效的问题（wp_send_json 调用 wp_die）。
* 修复更新服务器 URL 包含反引号导致所有更新检查失败。
* 新增手动检查更新按钮、强制检查 URL 参数 `?navai_force_check=1`。
* 新增完整授权管理后台：注册购买码、查询授权信息、显示授权状态页面。

【个人中心与书签功能】
* 新增前端"我的"个人中心页面：双栏布局，左侧用户信息+统计数据，右侧三标签页（收藏/评分/网址）。
* 修复分页 Tab 状态丢失：三层冗余（URL 参数 + query var + cookie），确保分页后 Tab 不回弹。
* 修复书签持久化：AJAX 响应新增 `is_bookmarked` 字段，cookie→user_meta 自动迁移。
* 新增评分系统：1-5 星 AJAX 投票，cookie 去重，卡片和详情页展示。
* 修复评分星星显示 bug：JS 用投票人数而非平均分决定填充数量。

【后台管理与仪表盘】
* 新增完整后台仪表盘：统计概览卡片、7/30/90 天趋势折线图、热门网站 Top10、分类分布饼图、快捷操作、死链检测、CSV 导出。
* 后台菜单"仪表盘"更名为"导航概览"。
* 新增自定义后台列：网站图标、网址链接、热门/新站标记、访问数、前台访问按钮。
* 新增重复检测功能：一键扫描、模态框显示重复组、单选保留/删除。
* 新增批量编辑：多选批量修改分类/标记/推荐位/审核状态/删除。
* 新增推荐位功能：后台设置推荐等级（推荐/置顶/轮播），首页展示推荐区域。
* 新增定时死链检测：每日 WP-Cron 自动检测，邮件通知。

【导航站采集与批量导入】
* 新增导航站导入采集：输入 URL 自动抓取全部工具链接，批量填入表格。
* 智能分类匹配：关键词匹配 + 12 条默认规则自动分配分类。
* 多页抓取：自动检测分页格式（/page/N/, ?page=N, ?paged=N）。
* 支持 OneNav 等主题 data-url 属性提取、data-id 配对、二级页面抓取回退。
* SSRF 防护：阻止内网 IP、localhost、DNS 解析失败域名。
* 非UTF-8 站点编码转换（GBK 等）。
* 修复批量采集超时：移除服务器端 favicon 验证循环，改用客户端 onerror 回退。

【网站图标与采集优化】
* 替换 Google favicon 为多源回退链：网站 /favicon.ico → 一文 API → DuckDuckGo → Google。
* 新增可复用 JS favicon 回退函数：域名提取、回退链构建、逐源加载。
* 全局 JS 图标回退检测：onload 尺寸检查（1x1 透明像素）、5 秒超时、MutationObserver。
* 修复保存后图标消失：隐藏字段注册为 REST API meta，兼容 Gutenberg。
* 优化网站信息采集：5 次重定向、完整 Chrome UA、30 秒超时、og:title 优先。

【SEO 与结构化数据】
* 新增 JSON-LD 结构化数据 SEO：单篇和首页标记。
* 新增 AI 工具分类专属 RSS 订阅。
* 新增浏览量和点击数统计：template_redirect 浏览追踪 + sendBeacon 点击追踪。

【主题设置与布局】
* 新增"首页每行网址数"设置（3-8 列，默认 5），通过 wp_head 动态注入 CSS。
* 卡片标题超 8 字自动截断，完整标题保留在 title 属性。
* 侧边栏收起状态显示独立图标栏，持续展示一级分类图标。
* 评论登录拦截：init 钩子重定向替代 wp_die_handler，JS 表单防护，登录后返回原页。
* 完整国际化支持，WordPress.org 合规。
* 嵌套评论系统、Logo/Favicon 上传修复、分类自定义排序。

【移动端适配】
* 后台列表移动端响应式：782px 以下使用 WordPress 原生卡片布局，支持横向滚动。
* 修复手机端展开行布局：table/tbody/tr 全部 block 化，彻底脱离表格布局算法。
* 批量添加页移动端适配：表格横向滚动、导入选项堆叠、弹窗适配。

【广告与功能修复】
* 修复广告代码被 wp_kses_post 过滤导致 script 标签丢失：改为 wp_unslash 保留完整 HTML/JS。
* 修复批量编辑 AJAX 参数冲突：$_POST['action'] 同时用于路由和批量操作，改名为 bulk_action。
* 修复主题激活钩子：register_activation_hook → after_switch_theme。
* 修复主题设置页致命错误：补充未定义的 `navai_render_update_check_button()` 函数。
* 用 Toast 通知组件替换所有 alert()，实现非阻塞式反馈。

== Screenshots / 截图 ==

1. Homepage with category sidebar and tool cards / 带分类侧边栏和工具卡片的首页
2. Tool detail page with sidebar / 带侧边栏的工具详情页
3. Mobile responsive layout / 移动端响应式布局
4. Admin theme settings page / 后台主题设置页面

== Upgrade Notice / 升级通知 ==

= 1.1.0 =
版本号重置为 1.1.0，合并所有历史更新日志。完整国际化支持和 WordPress.org 合规性改进。/ Version reset to 1.1.0, all historical changelogs merged. Full i18n support and WordPress.org compliance improvements.
