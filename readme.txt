=== NavAi ===
Contributors: ctrol
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.29.23
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

= 1.29.23 =
* Fixed batch URL collection not returning results: removed server-side favicon validation loop that caused 48s+ timeout (6×8s HTTP requests), now returns favicon_candidates for client-side onerror fallback / 修复批量采集无返回结果：移除服务器端favicon验证循环（6次HTTP请求每次8秒超时导致超时），改为返回候选列表供客户端onerror回退
* Added 10-minute transient cache for fetched site info to avoid re-fetching same URL / 新增10分钟瞬态缓存避免重复采集同一网址
* Increased all AJAX timeouts from 30s to 60s to accommodate slow websites / 所有AJAX超时从30秒增至60秒以适应慢速网站
* Unified client-side favicon fallback across batch page, contribute page, and admin edit page / 统一批量页面、投稿页面和后台编辑页面的客户端favicon回退逻辑
* Added better error extraction from AJAX failure responses / 改进AJAX失败响应的错误信息提取

= 1.29.22 =
* Replaced Google favicon with multi-source fallback chain for China accessibility: site /favicon.ico -> api.iowen.cn -> DuckDuckGo -> Google (last resort) / 替换 Google favicon 为多源回退链适配国内：网站 /favicon.ico -> 一文 API -> DuckDuckGo -> Google（最后兜底）
* Added /favicon.png path to server-side favicon fallback chain / 服务器端 favicon 回退链增加 /favicon.png 路径
* Added reusable JS favicon fallback functions (extractDomain, getFaviconFallbacks, loadFaviconWithFallback) / 新增可复用 JS favicon 回退函数（域名提取、回退链构建、逐源加载）
* Updated duplicate detection modal icon with inline multi-source onerror fallback / 重复检测模态框图标改用内联多源 onerror 回退

= 1.29.20 =
* Fixed website icon disappearing after save: hidden field value could be lost in Gutenberg, now registered as REST API meta / 修复保存后网址图标消失的问题：隐藏字段值在 Gutenberg 下可能丢失，现注册为 REST API 元数据
* Fixed meta box icon preview not restoring on page load: PHP now renders saved icon image in preview area / 修复元数据框图标预览页面加载时不恢复的问题：PHP 现在直接渲染已保存的图标图片
* Fixed save_post overwriting icon URL with empty value: only updates when non-empty value is submitted / 修复保存时空值覆盖已有图标 URL 的问题：仅在提交非空值时更新
* Added Gutenberg data store sync: fetched icon URL now syncs to block editor meta via wp.data API / 增加 Gutenberg 数据存储同步：采集到的图标 URL 通过 wp.data API 同步到区块编辑器
* Added favicon fallback chain with Google favicon service / 增加 favicon 回退链（含 Google favicon 服务）
* Added JS icon preview restoration on page load with error handling / 增加 JS 页面加载时恢复图标预览及错误处理
* Registered post meta fields (_website_url, _site_icon_url, _is_hot, _is_new, _site_tags) with show_in_rest for Gutenberg compatibility / 注册自定义字段为 REST API 可访问，确保 Gutenberg 兼容

= 1.29.19 =
* Replaced all alert() with Toast notification component for non-blocking feedback / 用 Toast 通知组件替换所有 alert()，实现非阻塞式反馈
* Added detailed field-by-field success reporting: shows which fields (title, description, icon) were collected / 采集成功提示显示具体采集到的字段（网站名称、网站描述、网站图标）
* Added partial success warning: shows collected fields and lists missing fields / 部分采集成功时显示已采集字段并列出缺失字段
* Added timeout handling: distinguishes timeout errors from general errors / 增加超时处理：区分超时错误和一般错误
* Batch fetch summary: shows success/partial/fail counts upon completion / 批量采集完成显示成功/部分成功/失败汇总
* Fixed missing echo in esc_js() calls causing empty i18n strings / 修复 esc_js() 缺少 echo 导致国际化字符串为空的问题
* Fixed duplicate fetchError i18n key / 修复重复的 fetchError 国际化键
* Added 30s timeout to frontend contribute AJAX request / 前端投稿页 AJAX 请求增加 30 秒超时

= 1.29.18 =
* Improved site info fetching: allowed up to 5 redirects, enhanced User-Agent to full Chrome string, increased timeout to 30s / 优化网站信息采集：允许最多 5 次重定向，增强 User-Agent 为完整 Chrome 字符串，超时增至 30 秒
* Improved title detection: added og:title as primary source, fixed over-aggressive title cleanup that truncated valid titles / 优化标题采集：增加 og:title 作为优先源，修复标题清理过于激进导致截断有效标题的问题
* Improved description detection: added twitter:description and schema.org itemprop as fallbacks / 优化描述采集：增加 twitter:description 和 schema.org itemprop 作为备选
* Improved favicon detection: rewrote regex to match link tags first then extract href, added relative URL resolver, added mask-icon and fluid-icon support / 优化 favicon 采集：重写正则先匹配 link 标签再提取 href，添加相对 URL 解析器，增加 mask-icon 和 fluid-icon 支持
* Improved favicon validation: switched from HEAD to GET for servers that reject HEAD, added content-type verification / 优化 favicon 验证：从 HEAD 改为 GET 请求以兼容拒绝 HEAD 的服务器，增加 content-type 验证
* Improved charset detection: added Content-Type header and HTML5 meta charset detection / 优化编码检测：增加 Content-Type header 和 HTML5 meta charset 检测

= 1.29.17 =
* Fixed view count and click count always showing zero: implemented actual tracking logic / 修复浏览量和点击数始终为零的问题：实现了真正的计数逻辑
* Added post view tracking via template_redirect hook with 5-minute cookie dedup / 通过 template_redirect 钩子添加浏览量统计，使用 cookie 实现 5 分钟去重
* Added click tracking via AJAX using navigator.sendBeacon for reliable cross-page tracking / 通过 AJAX 和 navigator.sendBeacon 添加点击数统计，确保跨页面追踪可靠
* Added data-post-id to card containers and detail page body for click tracking / 卡片容器和详情页 body 添加 data-post-id 属性用于点击追踪

= 1.29.16 =
* Added custom admin columns for URL list: website icon, URL link, hot/new marks, visit count, and front-end visit button / 后台网址列表增加自定义列：网站图标、网址链接、热门/新站标记、访问数、前台访问按钮
* Visit count column shows views/clicks and supports sorting by views / 访问数列显示浏览量/点击数，支持按浏览量排序
* Added CSS styling for column widths and display optimization / 添加列宽和显示优化的 CSS 样式

= 1.29.15 =
* Redesigned duplicate detection modal: users can now freely choose which entry to keep via radio buttons, no longer forced to keep the first row / 重新设计重复检测模态框：用户可通过单选按钮自由选择保留哪条记录，不再强制保留第一行
* Added completeness indicators for each duplicate entry: shows post status, description, icon, category, tags with visual badges and a completeness score / 每条重复记录增加完整度指示：显示发布状态、描述、图标、分类、标签的可视化标签和完整度评分
* Switched delete button to delegated event binding for reliability after keep-selection changes / 删除按钮改用事件委托绑定，确保切换保留项后仍可正常工作

= 1.29.14 =
* Improved duplicate detection modal: each row now displays website favicon and URL alongside title for easier identification / 重复检测模态框改进：每行显示网址图标和网址，便于识别

= 1.29.13 =
* Added duplicate detection button on admin URL list page: scan all URLs with one click, modal shows duplicate groups with keep/delete actions / 后台网址列表页添加重复检测按钮：一键扫描所有网址，模态框显示重复组并支持保留/删除操作
* Duplicate detection uses URL normalization for accurate matching: ignores trailing slashes, protocol case, and fragments / 重复检测使用 URL 规范化进行精确匹配：忽略尾部斜杠、协议大小写和锚点
* Delete action moves duplicates to trash (not permanent delete) for safety / 删除操作将重复网址移至回收站（非永久删除），确保安全

= 1.29.12 =
* Added URL duplicate detection for single add in admin: prevents saving duplicate URLs with error notice / 后台单个添加网址时增加重复检测：重复网址不保存并显示错误提示
* Improved batch add duplicate detection: URL normalization (trailing slash, protocol case), batch-internal duplicate check, detailed error messages / 批量添加重复检测增强：URL 规范化、批次内部重复检测、详细错误提示
* Unified duplicate detection across all entry points: admin single add, batch add, front-end contribute / 统一所有添加入口的重复检测：后台单个添加、批量添加、前端投稿

= 1.29.11 =
* Fixed batch fetch: improved sequential processing with progress indicator and error handling / 修复批量采集：改进顺序处理逻辑，添加进度显示和错误处理
* Fixed dynamically added rows missing fetch button text: esc_js() without echo caused empty strings / 修复新增行采集按钮无文字：esc_js() 缺少 echo 导致变量为空
* Fixed dynamically added rows missing delete button text: same root cause as above / 修复新增行删除按钮无文字：同一根本原因
* Added 30s timeout to AJAX fetch requests, reduced inter-row delay from 500ms to 300ms / AJAX 采集请求增加 30 秒超时，行间延迟从 500ms 降至 300ms

= 1.29.10 =
* Moved theme update check button above the save settings button / 检查更新按钮移至保存设置按钮前面

= 1.29.9 =
* Changed submit button link from /contribute to /submit / 提交按钮链接从 /contribute 改为 /submit
* Removed background colors from login/register/submit buttons, unified to transparent style / 取消登录/注册/提交按钮配色，统一为透明样式
* Fixed collapsed sidebar expand arrow position: moved from bottom to after last category icon / 修复折叠状态展开箭头位置：从底部移至最后一个分类图标下方

= 1.29.8 =
* Fixed theme activation no longer overriding homepage to static page, defaults to latest posts / 修复主题激活不再覆盖首页为静态页面，默认显示最新文章
* Moved theme update check button from global admin notices to Theme Settings > General Settings / 检查更新按钮从全局后台通知移至主题设置 > 通用设置
* Reordered sidebar: login/register/submit buttons now appear before collapse button, after categories / 调整侧边栏按钮顺序：登录/注册/提交按钮移至收起按钮之前、分类之后

= 1.29.7 =
* Reworked sidebar collapse: collapsed state now shows a dedicated icon bar with first-level category icons / 重做侧边栏收起：收起状态显示独立图标栏，持续展示一级分类图标
* Added login/register/submit action buttons at the bottom of expanded sidebar / 展开状态侧边栏底部添加登录/注册/提交操作按钮
* Collapsed icon bar includes expand button for easy restoration / 收起图标栏包含展开按钮，便于恢复

= 1.29.6 =
* Added manual theme update check button in admin dashboard / 后台仪表盘添加手动检查主题更新按钮
* Added force update check via URL parameter ?navai_force_check=1 / 通过 URL 参数强制检查更新
* Clears update_themes transient to bypass 12-hour cache / 清除 transient 缓存绕过 12 小时检查间隔

= 1.29.5 =
* Reworked comment login interception: replaced unreliable wp_die_handler with init hook redirect / 重做评论登录拦截：用 init 钩子重定向替代不可靠的 wp_die_handler
* Added JavaScript form submission guard for cached pages / 为缓存页面添加 JS 表单提交防护
* Added redirect-back notice with login button on post page / 添加重定向回来的带登录按钮的提示条

= 1.29.4 =
* Fixed homepage content not following sidebar custom category order / 修复首页内容区域未跟随侧边栏自定义分类排序的问题
* Content sections now use the same navai_category_order option as sidebar / 内容区块与侧边栏使用相同的自定义排序配置

= 1.29.3 =
* Fixed theme activation overwriting existing category data / 修复主题激活时覆盖已有分类数据的问题
* Default categories only created when ai_category taxonomy is empty / 仅当分类法为空时才创建默认分类
* Homepage settings no longer overridden on theme activation / 主题激活时不再覆盖已有首页设置

= 1.29.2 =
* Upgraded update integration: added license key and domain support / 升级更新集成：新增 License Key 和域名支持
* Added no_update transient for WP 5.5+ auto-update UI compatibility / 添加 no_update 支持，兼容 WP 5.5+ 自动更新界面
* Fixed transient key from 'slug' to 'theme' for WordPress core compatibility / 修复 transient 键名从 slug 改为 theme

= 1.29.1 =
* Improved comment login UX: styled login prompt card replaces raw comment form when login is required / 改进评论登录体验：需要登录时显示美观的登录提示卡片
* Added themed wp_die handler for comment submission errors / 添加主题风格评论错误页面
* Login redirect now returns to the original post after authentication / 登录后自动返回原文章页

= 1.29.0 =
* Full internationalization support with translation-ready text domain / 完整的国际化支持
* Added WordPress.org required files (readme.txt, license.txt) / 添加 WordPress.org 必需文件
* Fixed anchor scroll navigation for desktop sidebar categories / 修复桌面端侧边栏分类锚点滚动
* Improved submenu expand/collapse behavior / 改进子菜单展开/折叠行为
* Code style: tab indentation and Yoda conditions / 代码风格：Tab 缩进和 Yoda 条件

= 1.28.0 =
* Added nested comment system with user agent and location display / 添加嵌套评论系统
* Fixed logo/favicon upload button not working / 修复 Logo/Favicon 上传按钮无效

== Screenshots / 截图 ==

1. Homepage with category sidebar and tool cards / 带分类侧边栏和工具卡片的首页
2. Tool detail page with sidebar / 带侧边栏的工具详情页
3. Mobile responsive layout / 移动端响应式布局
4. Admin theme settings page / 后台主题设置页面

== Upgrade Notice / 升级通知 ==

= 1.29.0 =
Full i18n support and WordPress.org compliance improvements. / 完整国际化支持和 WordPress.org 合规性改进。