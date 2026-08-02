/**
 * NavAi 主题 - 主脚本文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.29.10
 */

(function($) {
    'use strict';

    /**
     * DOM Ready 初始化
     */
    $(document).ready(function() {
        initMobileMenu();
        initMobileSearch();
        initSearchTabs();
        initSubcategoryTabs();
        initBackToTop();
        initSidebarCollapse();
        initSidebarSubmenu();
        initSearchAutocomplete();
        initCardHover();
        initLazyLoad();
        initSmoothScroll();
        initScrollSpy();
        initFloatingMenu();
        initClickTracking();
    });

    /**
     * 移动端侧边栏菜单初始化
     *
     * @return void
     */
    function initMobileMenu() {
        var $toggle = $('.mobile-menu-btn');
        var $sidebar = $('.sidebar');
        var $overlay = $('.sidebar-overlay');
        var $expandBtn = $('#sidebar-expand-btn');

        if (!$toggle.length || !$sidebar.length) return;

        $toggle.on('click', function() {
            // 桌面端详情页：切换侧边栏展开/收起
            if ($(this).hasClass('desktop-menu-toggle') && $(window).width() >= 1025) {
                $sidebar.toggleClass('active');
                // 同步收起状态图标栏
                var $collapsedBar = $('#sidebar-collapsed-bar');
                if ($sidebar.hasClass('active')) {
                    $collapsedBar.removeClass('visible');
                    $('body').removeClass('sidebar-collapsed');
                } else {
                    $collapsedBar.addClass('visible');
                    $('body').addClass('sidebar-collapsed');
                }
            } else {
                // 移动端：使用 overlay 模式
                $sidebar.addClass('active');
                $overlay.addClass('active');
                $('body').addClass('sidebar-open');
            }
        });

        $overlay.on('click', function() {
            $sidebar.removeClass('active');
            $overlay.removeClass('active');
            $('body').removeClass('sidebar-open');
        });

        // 点击侧边栏中的链接后关闭侧边栏（排除有子菜单的一级分类锚点）
        $sidebar.find('a').on('click', function(e) {
            var $this = $(this);
            // 如果是一级分类的锚点链接且有子菜单，不关闭侧边栏（让展开/收起逻辑处理）
            if ($this.hasClass('sidebar-anchor') && $this.closest('.has-children').length) {
                return;
            }
            // 如果是子菜单链接或无子菜单的链接，关闭侧边栏
            $sidebar.removeClass('active');
            $overlay.removeClass('active');
            $('body').removeClass('sidebar-open');
        });
    }

    /**
     * 手机端搜索按钮初始化
     *
     * @return void
     */
    function initMobileSearch() {
        var $toggle = $('.mobile-search-toggle');
        var $searchSection = $('.search-section');
        var $searchInput = $('.search-input');

        if (!$toggle.length) return;

        $toggle.on('click', function() {
            // 滚动到搜索区域
            $('html, body').animate({
                scrollTop: $searchSection.offset().top - 70
            }, 300);

            // 聚焦搜索框
            setTimeout(function() {
                $searchInput.focus();
            }, 400);
        });
    }

    /**
     * 搜索标签切换初始化
     *
     * @return void
     */
    function initSearchTabs() {
        // 搜索引擎配置
        var engineConfigs = {
            'search': {
                placeholder: '百度一下',
                engines: [
                    { name: '百度', url: 'https://www.baidu.com/s?wd=', placeholder: '百度一下' },
                    { name: 'Bing', url: 'https://www.bing.com/search?q=', placeholder: '必应搜索' },
                    { name: 'Google', url: 'https://www.google.com/search?q=', placeholder: 'Google一下' },
                    { name: '头条', url: 'https://so.toutiao.com/search?keyword=', placeholder: '头条搜索' }
                ]
            },
            'image': {
                placeholder: '搜索图片...',
                engines: [
                    { name: '百度图片', url: 'https://image.baidu.com/search/index?tn=baiduimage&word=', placeholder: '百度图片搜索' },
                    { name: '花瓣', url: 'https://huaban.com/search?q=', placeholder: '花瓣搜索' },
                    { name: '图虫', url: 'https://tuchong.com/search/?q=', placeholder: '图虫搜索' }
                ]
            },
            'site': {
                placeholder: '站内搜索...',
                engines: [
                    { name: '站内搜索', url: '', placeholder: '站内搜索...' }
                ]
            },
            'deepseek': {
                placeholder: 'DeepSeek AI搜索...',
                engines: [
                    { name: 'DeepSeek', url: 'https://chat.deepseek.com/search?q=', placeholder: 'DeepSeek AI搜索...' },
                    { name: '秘塔AI搜索', url: 'https://metaso.cn/?q=', placeholder: '秘塔AI搜索...' }
                ]
            }
        };

        // 初始化单个搜索区域
        function initSearchSection($section) {
            var $tabs = $section.find('.search-tab');
            var $input = $section.find('.search-input');
            var $enginesContainer = $section.find('.search-engines');
            var $form = $section.find('form');

            if (!$tabs.length) return;

            // 保存原始站内搜索action
            var siteAction = $form.attr('action');

            function renderEngines(mode) {
                var config = engineConfigs[mode];
                if (!config) return;

                // 更新占位符
                $input.attr('placeholder', config.placeholder);

                // 渲染搜索引擎选项
                if (config.engines.length > 0) {
                    var html = '';
                    config.engines.forEach(function(engine, index) {
                        html += '<a href="' + engine.url + '" class="search-engine' + (index === 0 ? ' active' : '') + '" data-placeholder="' + (engine.placeholder || '') + '">' + engine.name + '</a>';
                    });
                    $enginesContainer.html(html);
                }

                // 更新表单行为
                if (mode === 'site') {
                    $form.attr('action', siteAction);
                    $form.attr('method', 'get');
                    $form.removeAttr('target');
                    $form.removeAttr('onsubmit');
                } else {
                    $form.attr('action', 'javascript:void(0);');
                    $form.removeAttr('method');
                }
            }

            $tabs.on('click', function() {
                var $tab = $(this);
                var mode = $tab.data('mode');

                // 只切换当前区域内的tab
                $section.find('.search-tab').removeClass('active');
                $tab.addClass('active');

                renderEngines(mode);
            });

            // 初始化默认搜索引擎
            renderEngines('search');
        }

        // 初始化移动端搜索区域
        var $mobileSection = $('.mobile-search-section');
        if ($mobileSection.length) {
            initSearchSection($mobileSection);
        }

        // 初始化桌面端搜索区域
        var $desktopSection = $('.desktop-search-section');
        if ($desktopSection.length) {
            initSearchSection($desktopSection);
        }
    }

    /**
     * 二级分类Tab切换初始化
     *
     * @return void
     */
    function initSubcategoryTabs() {
        var $tabs = $('.subcategory-tab');

        if (!$tabs.length) return;

        $tabs.on('click', function() {
            var $tab = $(this);
            var catId = $tab.data('filter');
            
            // 查找包含该Tab的容器（首页用 .category-section，分类页用 .main-content）
            var $section = $tab.closest('.category-section');
            if (!$section.length) {
                $section = $tab.closest('.main-content');
            }

            // 切换Tab激活状态
            $section.find('.subcategory-tab').removeClass('active');
            $tab.addClass('active');

            // 过滤显示的网址
            var $cards = $section.find('.ai-card');
            if (catId === 'all') {
                $cards.show();
            } else {
                $cards.each(function() {
                    var cardCats = $(this).data('terms');
                    if (cardCats && cardCats.toString().split(',').includes(catId.toString())) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    }

    /**
     * 返回顶部按钮初始化
     *
     * @return void
     */
    function initBackToTop() {
        var $btn = $('#back-to-top');
        var threshold = 300;

        if (!$btn.length) return;

        $(window).on('scroll', function() {
            if ($(this).scrollTop() > threshold) {
                $btn.addClass('visible');
            } else {
                $btn.removeClass('visible');
            }
        });

        $btn.on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 500);
        });
    }

    /**
     * 侧边栏折叠初始化
     *
     * @return void
     */
    function initSidebarCollapse() {
        var $collapse = $('.sidebar-collapse');
        var $sidebar = $('.sidebar');
        var $collapsedBar = $('#sidebar-collapsed-bar');
        var $collapsedBarExpand = $('#collapsed-bar-expand');
        var $body = $('body');
        var isCollapsed = false;

        // 显示收起状态图标栏
        function showCollapsedBar() {
            if ($(window).width() >= 1025) {
                $collapsedBar.addClass('visible');
                $body.addClass('sidebar-collapsed');
            }
        }

        // 隐藏收起状态图标栏
        function hideCollapsedBar() {
            $collapsedBar.removeClass('visible');
            $body.removeClass('sidebar-collapsed');
        }

        // 更新状态
        function updateState() {
            if ($(window).width() < 1025) {
                hideCollapsedBar();
                return;
            }

            // 详情页模式
            if ($('.detail-page').length) {
                if ($sidebar.hasClass('active')) {
                    hideCollapsedBar();
                } else {
                    showCollapsedBar();
                }
            } else {
                // 首页/分类页模式
                if ($sidebar.hasClass('collapsed')) {
                    showCollapsedBar();
                    isCollapsed = true;
                } else {
                    hideCollapsedBar();
                    isCollapsed = false;
                }
            }
        }

        // 初始化时检查状态
        updateState();

        // 窗口大小改变时更新
        $(window).on('resize', function() {
            updateState();
        });

        // 收起按钮点击（侧边栏内部）
        if ($collapse.length) {
            $collapse.on('click', function() {
                // 详情页模式：移除 active 隐藏侧边栏
                if ($('.detail-page').length && $(window).width() >= 1025) {
                    $sidebar.removeClass('active');
                    showCollapsedBar();
                    if (window.lucide) lucide.createIcons();
                    return;
                }

                // 首页/分类页模式
                isCollapsed = !isCollapsed;
                if (isCollapsed) {
                    $sidebar.addClass('collapsed');
                    $collapse.find('span').text('展开');
                    $collapse.find('i').attr('data-lucide', 'chevron-right');
                    showCollapsedBar();
                } else {
                    $sidebar.removeClass('collapsed');
                    $collapse.find('span').text('收起');
                    $collapse.find('i').attr('data-lucide', 'chevron-left');
                    hideCollapsedBar();
                }
                if (window.lucide) lucide.createIcons();
            });
        }

        // 收起状态图标栏的展开按钮
        if ($collapsedBarExpand.length) {
            $collapsedBarExpand.on('click', function() {
                // 详情页模式：添加 active 展开侧边栏
                if ($('.detail-page').length && $(window).width() >= 1025) {
                    $sidebar.addClass('active');
                    hideCollapsedBar();
                } else {
                    // 首页/分类页模式：移除 collapsed 展开侧边栏
                    isCollapsed = false;
                    $sidebar.removeClass('collapsed');
                    if ($collapse.length) {
                        $collapse.find('span').text('收起');
                        $collapse.find('i').attr('data-lucide', 'chevron-left');
                    }
                    hideCollapsedBar();
                }
                if (window.lucide) lucide.createIcons();
            });
        }
    }

    /**
     * 侧边栏二级分类展开/收起初始化
     *
     * @return void
     */
    function initSidebarSubmenu() {
        var $items = $('.sidebar-item-wrapper.has-children');
        var $allWrappers = $('.sidebar-item-wrapper');
        var $sidebar = $('.sidebar');

        if (!$items.length) return;

        var closeTimer = null;
        var activeItem = null;

        function openItem($wrapper) {
            if (activeItem && activeItem[0] !== $wrapper[0]) {
                activeItem.removeClass('open');
            }
            clearTimeout(closeTimer);
            activeItem = $wrapper;
            $wrapper.addClass('open');
        }

        function closeActive() {
            if (activeItem) {
                activeItem.removeClass('open');
                activeItem = null;
            }
        }

        // 点击展开/收起（移动端和桌面端通用）
        $items.each(function() {
            var $wrapper = $(this);
            var $header = $wrapper.find('.sidebar-item');

            $header.on('click', function(e) {
                // 如果点击的是子菜单链接，不触发展开/收起
                if ($(e.target).closest('.sidebar-submenu').length) {
                    return;
                }

                // 移动端有子菜单的项：只展开/收起，不关闭侧边栏，不跳转
                if ($(window).width() < 1025) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isOpen = $wrapper.hasClass('open');
                    $('.sidebar-item-wrapper.open').not($wrapper).removeClass('open');
                    $wrapper.toggleClass('open', !isOpen);
                    return;
                }

                // 桌面端：展开/收起子菜单，但不阻止默认行为，让锚点跳转正常工作
                if ($(window).width() >= 1025 && $wrapper.hasClass('has-children')) {
                    var isOpen = $wrapper.hasClass('open');
                    if (isOpen) {
                        closeActive();
                    } else {
                        openItem($wrapper);
                    }
                    // 不 preventDefault，让平滑滚动正常触发
                }
            });
        });

        // 桌面端悬停逻辑
        if ($sidebar.length) {
            var switchLock = false;

            function lockSwitch() {
                switchLock = true;
                setTimeout(function() {
                    switchLock = false;
                }, 120);
            }

            // 鼠标进入任意 wrapper（包括无子菜单的）时取消关闭
            $allWrappers.on('mouseenter', function() {
                if ($(window).width() >= 1025) {
                    clearTimeout(closeTimer);
                }
            });

            // 鼠标进入有子菜单的 wrapper 时展开
            $items.on('mouseenter', function() {
                if ($(window).width() >= 1025 && !switchLock) {
                    openItem($(this));
                    lockSwitch();
                }
            });

            // 鼠标离开有子菜单的 wrapper 时立即关闭
            $items.on('mouseleave', function() {
                if ($(window).width() >= 1025) {
                    closeActive();
                }
            });
        }
    }

    /**
     * 搜索自动补全初始化
     *
     * @return void
     */
    function initSearchAutocomplete() {
        var $input = $('.search-input');
        var $form = $('.search-box');
        var $results = $('<div class="search-results"></div>').insertAfter($form);
        var debounceTimer;

        if (!$input.length) return;

        $input.on('input', function() {
            var query = $(this).val().trim();

            clearTimeout(debounceTimer);

            if (query.length < 2) {
                $results.hide();
                return;
            }

            debounceTimer = setTimeout(function() {
                // 检查 ajaxurl 是否存在
                if (typeof navaiAjax === 'undefined' || !navaiAjax.ajaxurl) {
                    return;
                }

                $.ajax({
                    url: navaiAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'navai_search',
                        nonce: navaiAjax.nonce,
                        search: query
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            var html = response.data.map(function(item) {
                                return '<a href="' + item.url + '" class="search-result-item">' +
                                    '<div class="result-icon"><img src="' + (item.icon || '') + '" alt=""></div>' +
                                    '<div class="result-content">' +
                                        '<div class="result-title">' + item.title + '</div>' +
                                        '<div class="result-excerpt">' + item.excerpt + '</div>' +
                                    '</div>' +
                                '</a>';
                            }).join('');

                            $results.html(html).show();
                        } else {
                            $results.hide();
                        }
                    },
                    error: function() {
                        $results.hide();
                    }
                });
            }, 300);
        });

        // 点击外部关闭
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-inner').length) {
                $results.hide();
            }
        });
    }

    /**
     * 卡片悬停效果初始化
     *
     * @return void
     */
    function initCardHover() {
        $('.ai-card').on('mouseenter', function() {
            $(this).addClass('hover');
        }).on('mouseleave', function() {
            $(this).removeClass('hover');
        });
    }

    /**
     * 外链点击计数追踪
     * 当用户点击卡片中的外链（target="_blank"）时，异步发送 AJAX 增加 _click_count
     *
     * @return void
     */
    function initClickTracking() {
        if (typeof navaiAjax === 'undefined') {
            return;
        }

        // 追踪所有 target="_blank" 的外链点击（卡片图标、详情页打开网站按钮等）
        $(document).on('click', 'a[target="_blank"]', function(e) {
            var $link = $(this);
            var href = $link.attr('href') || '';
            var postId = 0;

            // 从卡片容器中提取 post_id
            var $card = $link.closest('.ai-card');
            if ($card.length && $card.data('post-id')) {
                postId = parseInt($card.data('post-id'), 10);
            }

            // 详情页：从 body data 或已知 post_id 获取
            if (!postId) {
                postId = parseInt($('body').data('post-id'), 10) || 0;
            }

            if (!postId) {
                return;
            }

            // 使用 navigator.sendBeacon 发送（页面跳转时更可靠）
            if (navigator.sendBeacon) {
                var formData = new FormData();
                formData.append('action', 'navai_increment_click');
                formData.append('nonce', navaiAjax.nonce);
                formData.append('post_id', postId);
                navigator.sendBeacon(navaiAjax.ajaxurl, formData);
            } else {
                // 回退到同步 AJAX
                $.ajax({
                    url: navaiAjax.ajaxurl,
                    type: 'POST',
                    async: false,
                    data: {
                        action: 'navai_increment_click',
                        nonce: navaiAjax.nonce,
                        post_id: postId
                    }
                });
            }
        });
    }

    /**
     * 懒加载图片初始化
     *
     * @return void
     */
    function initLazyLoad() {
        var $images = $('img[data-src]');

        if ($images.length === 0) {
            return;
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var $img = $(entry.target);
                        $img.attr('src', $img.data('src')).removeAttr('data-src');
                        observer.unobserve(entry.target);
                    }
                });
            });

            $images.each(function() {
                observer.observe(this);
            });
        } else {
            // Fallback
            $images.each(function() {
                $(this).attr('src', $(this).data('src')).removeAttr('data-src');
            });
        }
    }

    /**
     * 平滑滚动到锚点初始化
     *
     * @return void
     */
    function initSmoothScroll() {
        $('a[href^="#"]').not('[href="#"]').on('click', function(e) {
            var target = $(this).attr('hash');
            if (target && $(target).length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 80
                }, 400, function() {
                    // 滚动完成后，关闭移动端侧边栏
                    if ($(window).width() < 1025) {
                        $('.sidebar').removeClass('active');
                        $('.sidebar-overlay').removeClass('active');
                        $('body').removeClass('sidebar-open');
                    }
                });
            }
        });
    }

    /**
     * 滚动监听高亮当前分类（ScrollSpy）
     *
     * @return void
     */
    function initScrollSpy() {
        var $sections = $('.category-section');
        var $anchors = $('.sidebar-anchor');

        if (!$sections.length || !$anchors.length) return;

        var offset = 120; // 触发高亮的偏移量
        var isScrolling = false;

        // 标记正在执行平滑滚动，避免冲突
        $('a[href^="#"]').not('[href="#"]').on('click', function() {
            isScrolling = true;
            window.setTimeout(function() {
                isScrolling = false;
            }, 450);
        });

        $(window).on('scroll', function() {
            if (isScrolling) return;

            var scrollTop = $(window).scrollTop();
            var currentId = '';

            $sections.each(function() {
                var $section = $(this);
                var sectionTop = $section.offset().top;
                if (scrollTop >= sectionTop - offset) {
                    currentId = $section.attr('id');
                }
            });

            if (!currentId) {
                // 如果在顶部，高亮第一个
                currentId = $sections.first().attr('id');
            }

            $anchors.removeClass('active');
            if (currentId) {
                $anchors.filter('[href="#' + currentId + '"]').addClass('active');
            }
        });

        // 初始化时触发一次
        $(window).trigger('scroll');
    }

    /**
     * 复制链接功能
     *
     * @param {string} url 要复制的链接
     * @return void
     */
    window.navaiCopyLink = function(url) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function() {
                showToast('链接已复制');
            });
        } else {
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(url).select();
            document.execCommand('copy');
            $temp.remove();
            showToast('链接已复制');
        }
    };

    /**
     * 显示提示消息
     *
     * @param {string} message 消息内容
     * @param {string} type 消息类型 (success/error)
     * @return void
     */
    function showToast(message, type) {
        type = type || 'success';
        var $toast = $('<div class="toast toast-' + type + '">' + message + '</div>');

        $('body').append($toast);

        setTimeout(function() {
            $toast.addClass('show');
        }, 10);

        setTimeout(function() {
            $toast.removeClass('show');
            setTimeout(function() {
                $toast.remove();
            }, 300);
        }, 3000);
    }

    /**
     * 搜索引擎快捷跳转
     */
    $(document).on('click', '.search-engine', function(e) {
        e.preventDefault();

        var $this = $(this);
        var $section = $this.closest('.mobile-search-section, .desktop-search-section');

        // 切换当前区域内搜索引擎激活状态
        $section.find('.search-engine').removeClass('active');
        $this.addClass('active');

        // 更新当前区域内搜索框占位符
        var enginePlaceholder = $this.data('placeholder');
        if (enginePlaceholder) {
            $section.find('.search-input').attr('placeholder', enginePlaceholder);
        }

        var baseUrl = $this.attr('href');
        var query = $section.find('.search-input').val().trim();

        if (query) {
            window.open(baseUrl + encodeURIComponent(query), '_blank');
        }
    });

    /**
     * 搜索表单提交处理
     */
    $(document).on('submit', '.search-box', function(e) {
        var $form = $(this);
        var $section = $form.closest('.mobile-search-section, .desktop-search-section');
        var $activeTab = $section.find('.search-tab.active');
        var mode = $activeTab.data('mode');
        var query = $section.find('.search-input').val().trim();

        if (!query) {
            e.preventDefault();
            return;
        }

        // 站内搜索直接提交表单
        if (mode === 'site') {
            return true;
        }

        // 其他模式：使用选中的搜索引擎跳转
        e.preventDefault();
        var $activeEngine = $section.find('.search-engine.active');
        if ($activeEngine.length) {
            var baseUrl = $activeEngine.attr('href');
            window.open(baseUrl + encodeURIComponent(query), '_blank');
        }
    });

    /**
     * 浮动菜单 (FAB) 交互初始化
     */
    function initFloatingMenu() {
        var $floatingMenu = $('#floating-menu');
        var $floatingToggle = $('#floating-menu-toggle');

        if (!$floatingMenu.length || !$floatingToggle.length) return;

        // 点击切换按钮展开/收起菜单
        $floatingToggle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $floatingMenu.toggleClass('active');
            $floatingToggle.toggleClass('active');
        });

        // 点击页面其他地方关闭菜单
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.floating-menu').length) {
                $floatingMenu.removeClass('active');
                $floatingToggle.removeClass('active');
            }
        });

        // 复制链接功能
        $('#copy-link-btn').on('click', function(e) {
            e.preventDefault();
            var url = window.location.href;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function() {
                    alert('链接已复制到剪贴板');
                }).catch(function() {
                    fallbackCopy(url);
                });
            } else {
                fallbackCopy(url);
            }
        });

        function fallbackCopy(text) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                alert('链接已复制到剪贴板');
            } catch (err) {
                alert('复制失败，请手动复制');
            }
            document.body.removeChild(textarea);
        }

        // 分享功能
        $('#share-btn').on('click', function(e) {
            e.preventDefault();
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                });
            } else {
                // 复制链接作为分享备选
                var url = window.location.href;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(function() {
                        alert('链接已复制，可以粘贴分享了');
                    });
                } else {
                    alert('请手动复制链接进行分享');
                }
            }
        });

        // 返回顶部
        $('#back-to-top-fab').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 300);
            $floatingMenu.removeClass('active');
            $floatingToggle.removeClass('active');
        });
    }

})(jQuery);
