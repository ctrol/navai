<?php
/**
 * Template Name: 网址提交
 * 外部网址提交页面模板
 *
 * @package NavAi
 * @author 老九
 * @version 1.29.22
 */

if ( ! defined('ABSPATH')) {
	exit;
}

// 启动session用于防重复提交
if (!session_id()) {
	session_start();
}

// 检查用户是否已登录
if (!is_user_logged_in()) {
	auth_redirect();
	exit;
}

$current_user = wp_get_current_user();
$message = '';
$message_type = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['navai_contribute_nonce'])) {
	if (wp_verify_nonce($_POST['navai_contribute_nonce'], 'navai_contribute_action')) {
		// 防重复提交检查（基于session/token）
		$submit_token = isset($_POST['navai_submit_token']) ? sanitize_text_field($_POST['navai_submit_token']) : '';
		$session_token = isset($_SESSION['navai_last_submit_token']) ? $_SESSION['navai_last_submit_token'] : '';
		if ($submit_token && $submit_token === $session_token) {
			$message = esc_html__('请勿重复提交', 'navai');
			$message_type = 'error';
		} else {
			// 频率限制：每个用户每分钟最多提交1次
			$rate_limit_key = 'navai_submit_rate_' . $current_user->ID;
			$last_submit_time = get_transient($rate_limit_key);
			if ($last_submit_time) {
				$message = esc_html__('提交过于频繁，请1分钟后再试', 'navai');
				$message_type = 'error';
			} else {
				$site_name = sanitize_text_field($_POST['site_name']);
				$site_url = esc_url_raw($_POST['site_url']);
				$site_desc = wp_kses_post($_POST['site_desc']);
				$site_category = isset($_POST['site_category']) ? intval($_POST['site_category']) : 0;
				$site_tags = sanitize_text_field($_POST['site_tags']);
				$site_icon_url = esc_url_raw($_POST['site_icon_url']);

				if (empty($site_name) || empty($site_url)) {
					$message = esc_html__('网站名称和网址为必填项', 'navai');
					$message_type = 'error';
				} elseif (!navai_is_valid_external_url($site_url)) {
					$message = esc_html__('网址格式不合法或不允许访问内网地址', 'navai');
					$message_type = 'error';
				} elseif (mb_strlen($site_name) > 100) {
					$message = esc_html__('网站名称不能超过100个字符', 'navai');
					$message_type = 'error';
				} else {
					// 校验分类合法性（必须是 ai_category 下的子分类）
					$category_valid = true;
					if ( ! empty($site_category)) {
						$term = get_term($site_category, 'ai_category');
						if (!$term || is_wp_error($term) || $term->parent === 0) {
							$category_valid = false;
							$message = esc_html__('请选择有效的子分类', 'navai');
							$message_type = 'error';
						}
					}

					if ($category_valid && empty($message)) {
						// 检查是否已提交过相同网址（含 URL 规范化检测）
						if (function_exists('navai_url_exists') && navai_url_exists($site_url)) {
							$message = esc_html__('该网址已被提交过，请勿重复提交', 'navai');
							$message_type = 'error';
						} else {
							// 创建待审核文章
							$post_data = array(
								'post_title'   => $site_name,
								'post_content' => $site_desc,
								'post_status'  => 'pending',
								'post_type'    => 'ai_tool',
								'post_author'  => $current_user->ID,
							);

							$post_id = wp_insert_post($post_data);

							if ($post_id && !is_wp_error($post_id)) {
								// 保存自定义字段
								update_post_meta($post_id, '_website_url', $site_url);
								update_post_meta($post_id, '_site_icon_url', $site_icon_url);
								update_post_meta($post_id, '_submit_status', 'pending');
								update_post_meta($post_id, '_submit_user', $current_user->ID);
								update_post_meta($post_id, '_submit_time', current_time('mysql'));
								update_post_meta($post_id, '_site_tags', $site_tags);

								// 设置分类（单选，只接受子分类）
								if ( ! empty($site_category)) {
									wp_set_object_terms($post_id, array($site_category), 'ai_category');
								}

								// 设置频率限制（60秒）
								set_transient($rate_limit_key, time(), 60);

								// 记录提交token防止重复
								if (!session_id()) {
									session_start();
								}
								$_SESSION['navai_last_submit_token'] = $submit_token;

								$message = esc_html__('提交成功！您的网站已提交审核，请耐心等待管理员审核。', 'navai');
								$message_type = 'success';
							} else {
								$message = esc_html__('提交失败，请稍后重试', 'navai');
								$message_type = 'error';
							}
						}
					}
				}
			}
		}
	}
}

// 获取所有分类
$categories = get_terms(array(
	'taxonomy'   => 'ai_category',
	'hide_empty' => false,
	'parent'     => 0,
));

get_header();
get_sidebar();
?>

<div class="main-content">
	<div class="contribute-page">
		<div class="contribute-header">
			<h1 class="contribute-title">
				<i data-lucide="send"></i>
				<?php esc_html_e('提交网站', 'navai'); ?>
			</h1>
			<p class="contribute-subtitle"><?php esc_html_e('提交您发现的优质AI网站，与更多人分享', 'navai'); ?></p>
		</div>

		<?php if ($message) : ?>
		<div class="contribute-message <?php echo esc_attr($message_type); ?>">
			<i data-lucide="<?php echo $message_type === 'success' ? 'check-circle' : 'alert-circle'; ?>"></i>
			<?php echo esc_html($message); ?>
		</div>
		<?php endif; ?>

		<form method="post" action="" class="contribute-form" id="contribute-form">
			<?php wp_nonce_field('navai_contribute_action', 'navai_contribute_nonce'); ?>
			<input type="hidden" name="navai_submit_token" value="<?php echo esc_attr(wp_hash(microtime() . $current_user->ID . wp_rand())); ?>">

			<!-- 网站名称 -->
			<div class="form-group">
				<label for="site_name">
					<i data-lucide="type"></i>
					<?php esc_html_e('网站名称', 'navai'); ?> <span class="required">*</span>
				</label>
				<input type="text" id="site_name" name="site_name" required
                       placeholder="<?php esc_attr_e('请输入网站名称', 'navai'); ?>"
                       value="<?php echo isset($_POST['site_name']) ? esc_attr($_POST['site_name']) : ''; ?>">
			</div>

			<!-- 网站链接 -->
			<div class="form-group">
				<label for="site_url">
					<i data-lucide="link"></i>
					<?php esc_html_e('网站链接', 'navai'); ?> <span class="required">*</span>
				</label>
				<div class="url-input-group">
					<input type="url" id="site_url" name="site_url" required
                           placeholder="https://example.com"
                           value="<?php echo isset($_POST['site_url']) ? esc_attr($_POST['site_url']) : ''; ?>">
					<button type="button" class="btn-fetch-tdk" id="fetch-tdk">
						<i data-lucide="download"></i>
						<?php esc_html_e('获取信息', 'navai'); ?>
					</button>
				</div>
			</div>

			<!-- 网站介绍（经典编辑器） -->
			<div class="form-group">
				<label for="site_desc">
					<i data-lucide="file-text"></i>
					<?php esc_html_e('网站介绍', 'navai'); ?>
				</label>
				<?php
				$content = isset($_POST['site_desc']) ? $_POST['site_desc'] : '';
				wp_editor($content, 'site_desc', array(
					'textarea_name' => 'site_desc',
					'textarea_rows' => 10,
					'teeny'         => true,
					'quicktags'     => true,
					'media_buttons' => false,
					'tinymce'       => array(
						'toolbar1' => 'bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,fullscreen',
						'toolbar2' => '',
					),
				));
				?>
			</div>

			<!-- 网站分类（单选，仅子分类可选） -->
			<div class="form-group">
				<label>
					<i data-lucide="folder-open"></i>
					<?php esc_html_e('网址分类', 'navai'); ?>
				</label>
				<div class="category-grid">
					<?php if ( ! empty($categories) && !is_wp_error($categories)) : ?>
						<?php foreach ($categories as $cat) : ?>
							<?php
							$children = get_terms(array(
								'taxonomy'   => 'ai_category',
								'parent'     => $cat->term_id,
								'hide_empty' => false,
							));
							?>
							<div class="category-group">
								<div class="category-parent">
									<span class="cat-parent-name"><?php echo esc_html($cat->name); ?></span>
								</div>
								<?php if ( ! empty($children) && !is_wp_error($children)) : ?>
								<div class="category-children">
									<?php foreach ($children as $child) : ?>
									<label class="radio-label">
										<input type="radio" name="site_category" value="<?php echo esc_attr($child->term_id); ?>">
										<span class="radiomark"></span>
										<span class="cat-name"><?php echo esc_html($child->name); ?></span>
									</label>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<!-- 网站标签 -->
			<div class="form-group">
				<label for="site_tags">
					<i data-lucide="tags"></i>
					<?php esc_html_e('网站标签', 'navai'); ?>
				</label>
				<input type="text" id="site_tags" name="site_tags"
                       placeholder="<?php esc_attr_e('输入标签，用逗号分隔，例如：AI写作, 免费, 中文', 'navai'); ?>"
                       value="<?php echo isset($_POST['site_tags']) ? esc_attr($_POST['site_tags']) : ''; ?>">
			</div>

			<!-- 隐藏字段：图标URL -->
			<input type="hidden" id="site_icon_url" name="site_icon_url" value="">

			<!-- 提交按钮 -->
			<div class="form-submit-area">
				<button type="submit" class="btn-submit">
					<i data-lucide="send"></i>
					<?php esc_html_e('提交审核', 'navai'); ?>
				</button>
			</div>
		</form>

		<!-- 投稿须知 -->
		<div class="contribute-rules">
			<h3 class="rules-title">
				<i data-lucide="info"></i>
				<?php esc_html_e('投稿须知', 'navai'); ?>
			</h3>
			<div class="rules-content">
				<p><?php esc_html_e('1. 请确保您提交的网站是合法、正规的网站。', 'navai'); ?></p>
				<p><?php esc_html_e('2. 网站名称和介绍应真实准确，不得含有虚假信息。', 'navai'); ?></p>
				<p><?php esc_html_e('3. 提交后网站将进入待审核状态，管理员审核通过后将显示在网站上。', 'navai'); ?></p>
				<p><?php esc_html_e('4. 审核时间一般为1-3个工作日，请耐心等待。', 'navai'); ?></p>
				<p><?php esc_html_e('5. 请勿重复提交同一网站。', 'navai'); ?></p>
			</div>
		</div>
	</div>
</div>

<script>
(function($) {
	'use strict';

	$(document).ready(function() {
		var navaiContributeI18n = {
			enterUrl: '<?php echo esc_js(__('请先输入网址', 'navai')); ?>',
			fetching: '<?php echo esc_js(__('获取中...', 'navai')); ?>',
			fetchSuccess: '<?php echo esc_js(__('采集成功', 'navai')); ?>',
			fetchFailed: '<?php echo esc_js(__('采集失败', 'navai')); ?>',
			fetchError: '<?php echo esc_js(__('采集出错', 'navai')); ?>',
			unknownError: '<?php echo esc_js(__('未知错误', 'navai')); ?>',
			timeout: '<?php echo esc_js(__('请求超时，请稍后重试', 'navai')); ?>',
			partialSuccess: '<?php echo esc_js(__('部分采集成功', 'navai')); ?>',
			noDataFetched: '<?php echo esc_js(__('未采集到有效信息，请手动填写', 'navai')); ?>',
			gotTitle: '<?php echo esc_js(__('网站名称', 'navai')); ?>',
			gotDesc: '<?php echo esc_js(__('网站描述', 'navai')); ?>',
			gotIcon: '<?php echo esc_js(__('网站图标', 'navai')); ?>',
			gotNothing: '<?php echo esc_js(__('未采集到', 'navai')); ?>'
		};

		// 获取TDK信息
		$('#fetch-tdk').on('click', function(e) {
			e.preventDefault();
			var url = $('#site_url').val().trim();
			if (!url) {
				navaiToast.warning(navaiContributeI18n.enterUrl);
				$('#site_url').focus();
				return;
			}

			var $btn = $(this);
			var originalText = $btn.html();
			$btn.html('<i data-lucide="loader-2" class="spinning"></i> ' + navaiContributeI18n.fetching);
			$btn.prop('disabled', true);

			// 重新初始化图标
			if (typeof lucide !== 'undefined') {
				lucide.createIcons();
			}

			$.ajax({
			url: navaiAjax.ajaxurl,
			type: 'POST',
			timeout: 60000,
			data: {
				action: 'navai_fetch_site_info',
				nonce: navaiAjax.nonce,
				url: url
			},
			success: function(response) {
				if (response.success) {
					var data = response.data;
					var gotFields = [];
					if (data.title) {
						$('#site_name').val(data.title);
						var titlePreview = data.title.length > 40 ? data.title.substring(0, 40) + '...' : data.title;
						gotFields.push('<li>' + navaiContributeI18n.gotTitle + ': <strong>' + $('<div>').text(titlePreview).html() + '</strong></li>');
					}
					if (data.description) {
						if (typeof tinymce !== 'undefined' && tinymce.get('site_desc')) {
							tinymce.get('site_desc').setContent(data.description);
						} else {
							$('#site_desc').val(data.description);
						}
						var descPreview = data.description.length > 60 ? data.description.substring(0, 60) + '...' : data.description;
						gotFields.push('<li>' + navaiContributeI18n.gotDesc + ': <strong>' + $('<div>').text(descPreview).html() + '</strong></li>');
					}

					// favicon处理：使用服务器返回的候选列表进行客户端回退加载
					var faviconCandidates = data.favicon_candidates || [];
					var iconLoaded = false;
					$('#site_icon_url').val(''); // 先清空

					if (faviconCandidates.length > 0) {
						gotFields.push('<li>' + navaiContributeI18n.gotIcon + ': <strong>&#10003;</strong></li>');
						// 依次尝试候选URL，成功后设置隐藏字段
						(function tryFavicon(idx) {
							if (idx >= faviconCandidates.length || iconLoaded) return;
							var tester = new Image();
							tester.onload = function() {
								if (!iconLoaded) {
									iconLoaded = true;
									$('#site_icon_url').val(faviconCandidates[idx]);
								}
							};
							tester.onerror = function() {
								tryFavicon(idx + 1);
							};
							tester.src = faviconCandidates[idx];
						})(0);
					}

					var missingFields = [];
					if (!data.title) missingFields.push(navaiContributeI18n.gotTitle);
					if (!data.description) missingFields.push(navaiContributeI18n.gotDesc);
					if (faviconCandidates.length === 0) missingFields.push(navaiContributeI18n.gotIcon);

					if (gotFields.length === 3) {
						navaiToast.success(navaiContributeI18n.fetchSuccess, '<ul>' + gotFields.join('') + '</ul>');
					} else if (gotFields.length > 0) {
						var body = '<ul>' + gotFields.join('') + '</ul>';
						if (missingFields.length) {
							body += '<div style="margin-top:4px;color:#999;">' + navaiContributeI18n.gotNothing + ': ' + missingFields.join('、') + '</div>';
						}
						navaiToast.warning(navaiContributeI18n.partialSuccess, body, 7000);
					} else {
						navaiToast.warning(navaiContributeI18n.noDataFetched, '', 7000);
					}
				} else {
					navaiToast.error(navaiContributeI18n.fetchFailed, response.data.message || navaiContributeI18n.unknownError);
				}
			},
				error: function(xhr, status, error) {
					var msg = (status === 'timeout') ? navaiContributeI18n.timeout : (error || status);
					navaiToast.error(navaiContributeI18n.fetchError, msg);
				},
				complete: function() {
					$btn.html(originalText);
					$btn.prop('disabled', false);
					if (typeof lucide !== 'undefined') {
						lucide.createIcons();
					}
				}
			});
		});

		// 添加 spinning 动画样式
		var style = document.createElement('style');
		style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .spinning { animation: spin 1s linear infinite; }';
		document.head.appendChild(style);
	});
})(jQuery);
</script>

<style>
/* 投稿页面样式 */
.contribute-page {
	max-width: 800px;
	margin: 0 auto;
	padding: 24px;
}

.contribute-header {
	text-align: center;
	margin-bottom: 32px;
}

.contribute-title {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 10px;
	font-size: 24px;
	font-weight: 700;
	color: var(--gray-900);
	margin-bottom: 8px;
}

.contribute-title i {
	width: 28px;
	height: 28px;
	color: var(--primary-color);
}

.contribute-subtitle {
	font-size: 14px;
	color: var(--gray-500);
}

/* 消息提示 */
.contribute-message {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 14px 18px;
	border-radius: 8px;
	margin-bottom: 24px;
	font-size: 14px;
}

.contribute-message i {
	width: 20px;
	height: 20px;
	flex-shrink: 0;
}

.contribute-message.success {
	background: #e8f5e9;
	color: #2e7d32;
	border: 1px solid #a5d6a7;
}

.contribute-message.error {
	background: #ffebee;
	color: #c62828;
	border: 1px solid #ef9a9a;
}

/* 表单样式 */
.contribute-form {
	background: var(--white);
	border-radius: 12px;
	padding: 32px;
	box-shadow: var(--shadow-sm);
	border: 1px solid var(--gray-200);
	margin-bottom: 24px;
}

.form-group {
	margin-bottom: 24px;
}

.form-group label {
	display: flex;
	align-items: center;
	gap: 6px;
	font-size: 14px;
	font-weight: 600;
	color: var(--gray-800);
	margin-bottom: 8px;
}

.form-group label i {
	width: 16px;
	height: 16px;
	color: var(--gray-500);
}

.form-group .required {
	color: var(--primary-color);
}

.form-group input[type="text"],
.form-group input[type="url"] {
	width: 100%;
	padding: 10px 14px;
	font-size: 14px;
	border: 1px solid var(--gray-300);
	border-radius: 8px;
	background: var(--white);
	transition: border-color 0.2s;
}

.form-group input[type="text"]:focus,
.form-group input[type="url"]:focus {
	outline: none;
	border-color: var(--primary-color);
}

.url-input-group {
	display: flex;
	gap: 8px;
}

.url-input-group input {
	flex: 1;
}

.btn-fetch-tdk {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 10px 16px;
	background: var(--primary-color);
	color: var(--white);
	border: none;
	border-radius: 8px;
	font-size: 13px;
	font-weight: 500;
	cursor: pointer;
	transition: background 0.2s;
	white-space: nowrap;
}

.btn-fetch-tdk:hover {
	background: var(--primary-hover);
}

.btn-fetch-tdk:disabled {
	opacity: 0.7;
	cursor: not-allowed;
}

.btn-fetch-tdk i {
	width: 16px;
	height: 16px;
}

/* 编辑器样式调整 */
.wp-editor-wrap {
	border-radius: 8px;
	overflow: hidden;
}

/* 分类选择 */
.category-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
	gap: 16px;
}

.category-group {
	background: var(--gray-50);
	border-radius: 8px;
	padding: 12px;
	border: 1px solid var(--gray-200);
}

.category-parent {
	font-weight: 600;
	margin-bottom: 8px;
	padding-bottom: 8px;
	border-bottom: 1px solid var(--gray-200);
}

.cat-parent-name {
	font-size: 14px;
	color: var(--gray-800);
	display: flex;
	align-items: center;
	gap: 6px;
}

.category-children {
	display: flex;
	flex-direction: column;
	gap: 6px;
	padding-left: 8px;
}

.radio-label {
	display: flex;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	font-size: 13px;
	color: var(--gray-700);
}

.radio-label input[type="radio"] {
	width: 16px;
	height: 16px;
	accent-color: var(--primary-color);
	cursor: pointer;
}

/* 提交按钮 */
.form-submit-area {
	text-align: center;
	padding-top: 16px;
	border-top: 1px solid var(--gray-200);
}

.btn-submit {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 12px 32px;
	background: var(--primary-color);
	color: var(--white);
	border: none;
	border-radius: 8px;
	font-size: 15px;
	font-weight: 600;
	cursor: pointer;
	transition: background 0.2s;
}

.btn-submit:hover {
	background: var(--primary-hover);
}

.btn-submit i {
	width: 18px;
	height: 18px;
}

/* 投稿须知 */
.contribute-rules {
	background: var(--gray-50);
	border-radius: 12px;
	padding: 24px;
	border: 1px solid var(--gray-200);
}

.rules-title {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 16px;
	font-weight: 600;
	color: var(--gray-800);
	margin-bottom: 12px;
}

.rules-title i {
	width: 20px;
	height: 20px;
	color: var(--primary-color);
}

.rules-content {
	font-size: 13px;
	color: var(--gray-600);
	line-height: 1.8;
}

.rules-content p {
	margin-bottom: 4px;
}

/* 旋转动画 */
.spinning {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}

/* 响应式 */
@media (max-width: 768px) {
	.contribute-page {
		padding: 16px;
	}

	.contribute-form {
		padding: 20px;
	}

	.url-input-group {
		flex-direction: column;
	}

	.category-grid {
		grid-template-columns: 1fr;
	}
}
</style>

<?php get_footer(); ?>

