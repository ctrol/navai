/**
 * NavAi 主题 - 主脚本文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.27.0
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
        initFloatingMenu();
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
                // 同步展开按钮状态
                if ($expandBtn.length) {
                    if ($sidebar.hasClass('active')) {
                        $expandBtn.removeClass('visible');
                    } else {
                        $expandBtn.addClass('visible');
                    }
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

        // 点击侧边栏中的链接后关闭侧边栏
        $sidebar.find('a').on('click', function() {
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
        var $expandBtn = $('#sidebar-expand-btn');
        var $sidebar = $('.sidebar');
        var isCollapsed = false;

        // 更新展开按钮显示状态
        function updateExpandBtn() {
            if ($expandBtn.length) {
                // 桌面端且侧边栏收起时显示展开按钮
                if ($(window).width() >= 1025 && $sidebar.hasClass('collapsed')) {
                    $expandBtn.addClass('visible');
                } else {
                    $expandBtn.removeClass('visible');
                }
            }
        }

        // 初始化时检查状态
        updateExpandBtn();

        // 窗口大小改变时更新
        $(window).on('resize', function() {
            updateExpandBtn();
        });

        // 收起按钮点击
        if ($collapse.length) {
            $collapse.on('click', function() {
                isCollapsed = !isCollapsed;

                if (isCollapsed) {
                    $sidebar.addClass('collapsed');
                    $collapse.find('span').text('展开');
                    $collapse.find('i').attr('data-lucide', 'chevron-right');
                } else {
                    $sidebar.removeClass('collapsed');
                    $collapse.find('span').text('收起');
                    $collapse.find('i').attr('data-lucide', 'chevron-left');
                }

                updateExpandBtn();

                // 重新渲染图标
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        }

        // 展开按钮点击
        if ($expandBtn.length) {
            $expandBtn.on('click', function() {
                // 详情页模式：添加 active 类展开侧边栏
                if ($('.detail-page').length && $(window).width() >= 1025) {
                    $sidebar.addClass('active');
                } else {
                    // 首页/分类页模式：移除 collapsed 类
                    isCollapsed = false;
                    $sidebar.removeClass('collapsed');
                    if ($collapse.length) {
                        $collapse.find('span').text('收起');
                        $collapse.find('i').attr('data-lucide', 'chevron-left');
                    }
                }

                updateExpandBtn();

                // 重新渲染图标
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        }

        // 侧边栏内部收起按钮（详情页模式）
        if ($collapse.length && $('.detail-page').length) {
            $collapse.on('click', function() {
                // 详情页：点击收起按钮关闭侧边栏（移除 active）
                if ($(window).width() >= 1025) {
                    $sidebar.removeClass('active');
                    updateExpandBtn();
                }
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

        if (!$items.length) return;

        $items.each(function() {
            var $wrapper = $(this);
            var $header = $wrapper.find('.sidebar-item');

            $header.on('click', function(e) {
                // 如果点击的是子菜单链接，不触发展开/收起
                if ($(e.target).closest('.sidebar-submenu').length) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();

                // 切换当前项的展开状态
                var isOpen = $wrapper.hasClass('open');

                // 关闭其他已展开的项目（手风琴效果）
                $('.sidebar-item-wrapper.open').not($wrapper).removeClass('open');

                // 切换当前项目
                $wrapper.toggleClass('open', !isOpen);
            });
        });
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
                    scrollTop: $(target).offset().top - 100
                }, 500);
            }
        });
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
