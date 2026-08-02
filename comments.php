<?php
/**
 * 评论模板文件
 *
 * @package NavAi
 * @author 老九
 * @version 1.29.22
 */

// 防止直接访问
if ( ! defined('ABSPATH')) {
	exit;
}

// 密码保护检查
if (post_password_required()) {
	echo '<p class="navai-notice">' . esc_html__('请输入密码查看评论。', 'navai') . '</p>';
	return;
}

/**
 * 获取评论者的 UA 信息（操作系统、浏览器）
 */
function navai_get_comment_ua_info($comment_id) {
	$comment = get_comment($comment_id);
	if (!$comment) return '';

	$ua = $comment->comment_agent;
	if (empty($ua)) return '';

	$os = '';
	$browser = '';

	// 操作系统检测
	if (preg_match('/Windows NT 10/i', $ua)) {
		$os = 'Windows 10';
	} elseif (preg_match('/Windows NT 6\.3/i', $ua)) {
		$os = 'Windows 8.1';
	} elseif (preg_match('/Windows NT 6\.1/i', $ua)) {
		$os = 'Windows 7';
	} elseif (preg_match('/Windows/i', $ua)) {
		$os = 'Windows';
	} elseif (preg_match('/Mac OS X (\d+[._]\d+)/i', $ua, $m)) {
		$os = 'macOS ' . str_replace('_', '.', $m[1]);
	} elseif (preg_match('/Mac/i', $ua)) {
		$os = 'macOS';
	} elseif (preg_match('/Android (\d+[\.\d]*)/i', $ua, $m)) {
		$os = 'Android ' . $m[1];
	} elseif (preg_match('/iPhone OS (\d+[\._\d]*)/i', $ua, $m)) {
		$os = 'iOS ' . str_replace('_', '.', $m[1]);
	} elseif (preg_match('/iPad/i', $ua)) {
		$os = 'iPadOS';
	} elseif (preg_match('/Linux/i', $ua)) {
		$os = 'Linux';
	} elseif (preg_match('/Ubuntu/i', $ua)) {
		$os = 'Ubuntu';
	}

	// 浏览器检测
	if (preg_match('/Edg\/(\d+)/i', $ua, $m)) {
		$browser = 'Edge ' . $m[1];
	} elseif (preg_match('/Chrome\/(\d+)/i', $ua, $m) && !preg_match('/Edg/i', $ua)) {
		$browser = 'Chrome ' . $m[1];
	} elseif (preg_match('/Firefox\/(\d+)/i', $ua, $m)) {
		$browser = 'Firefox ' . $m[1];
	} elseif (preg_match('/Safari\/(\d+)/i', $ua, $m) && !preg_match('/Chrome/i', $ua)) {
		$browser = 'Safari ' . $m[1];
	} elseif (preg_match('/Opera|OPR\/(\d+)/i', $ua, $m)) {
		$browser = 'Opera ' . $m[1];
	} elseif (preg_match('/MiuiBrowser/i', $ua)) {
		$browser = 'MIUI Browser';
	} elseif (preg_match('/UCBrowser/i', $ua)) {
		$browser = 'UC Browser';
	} elseif (preg_match('/QQBrowser/i', $ua)) {
		$browser = 'QQ Browser';
	}

	$parts = array();
	if ($os) $parts[] = $os;
	if ($browser) $parts[] = $browser;

	return implode(' / ', $parts);
}

/**
 * 获取评论者 IP 归属地（使用 WordPress 内置 API 或简易查询）
 */
function navai_get_comment_location($comment_id) {
	$location = get_comment_meta($comment_id, 'comment_location', true);
	if ( ! empty($location)) {
		return $location;
	}

	$comment = get_comment($comment_id);
	if (!$comment) return '';

	$ip = $comment->comment_author_IP;
	if (empty($ip) || $ip === '127.0.0.1') {
		return __('本地', 'navai');
	}

	// 尝试使用 ipinfo.io 免费接口查询（有速率限制）
	$transient_key = 'navai_ip_loc_' . md5($ip);
	$cached = get_transient($transient_key);
	if ($cached !== false) {
		$location = $cached;
		update_comment_meta($comment_id, 'comment_location', $location);
		return $location;
	}

	$response = wp_remote_get('https://ipinfo.io/' . $ip . '/json', array(
		'timeout' => 3,
		'headers' => array('User-Agent' => 'NavAi Theme'),
	));

	if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
		$data = json_decode(wp_remote_retrieve_body($response), true);
		if ( ! empty($data['country']) && !empty($data['region'])) {
			$location = $data['country'] . ' ' . $data['region'];
		} elseif (!empty($data['country'])) {
			$location = $data['country'];
		} else {
			$location = '';
		}
	} else {
		$location = '';
	}

	set_transient($transient_key, $location, 86400 * 7); // 缓存7天
	if ( ! empty($location)) {
		update_comment_meta($comment_id, 'comment_location', $location);
	}

	return $location;
}

/**
 * 自定义评论输出回调函数（支持嵌套）
 */
function navai_comment_callback($comment, $args, $depth) {
	$tag = ('div' === $args['style']) ? 'div' : 'li';
	$add_below = 'div-comment';
	$comment_id = $comment->comment_ID;
	$depth_class = 'depth-' . $depth;
	?>
	<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? $depth_class : $depth_class . ' parent'); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<div class="comment-author-info">
				<?php
				if (0 != $args['avatar_size']) {
					echo get_avatar($comment, $args['avatar_size'], '', '', array(
						'class' => 'comment-avatar',
					));
				}
				?>
				<span class="comment-author-name"><?php echo get_comment_author_link(); ?></span>
			</div>

			<div class="comment-main">
				<div class="comment-content">
					<?php if ('0' == $comment->comment_approved) : ?>
						<p class="comment-awaiting-moderation"><?php esc_html_e('您的评论正在等待审核。', 'navai'); ?></p>
					<?php endif; ?>
					<?php comment_text(); ?>
				</div>

				<div class="comment-info-bar">
					<a href="<?php echo esc_url(get_comment_link($comment, $args)); ?>" class="comment-date">
						<time datetime="<?php comment_time('c'); ?>">
							<?php printf('%1$s %2$s', get_comment_date('Y-m-d', $comment), get_comment_time('H:i')); ?>
						</time>
					</a>
					<?php
					$ua_info = navai_get_comment_ua_info($comment_id);
					if ($ua_info) :
					?>
						<span class="comment-ua"><?php echo esc_html($ua_info); ?></span>
					<?php endif; ?>
					<?php
					$location = navai_get_comment_location($comment_id);
					if ($location) :
					?>
						<span class="comment-location"><?php echo esc_html($location); ?></span>
					<?php endif; ?>
					<?php edit_comment_link(esc_html__('编辑', 'navai'), '<span class="edit-link">', '</span>'); ?>
					<span class="reply-link">
					<?php
					comment_reply_link(array_merge($args, array(
						'add_below' => $add_below,
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
					)));
					?>
					</span>
				</div>
			</div>
		</article><!-- .comment-body -->
	<?php
}

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
?>

<div id="comments-area" class="comments-area">

	<?php if (have_comments()) : ?>
		<h3 class="comments-title">
			<?php
			$comment_count = get_comments_number();
			if ($comment_count == 1) {
				esc_html_e('1条评论', 'navai');
			} else {
				printf(esc_html__('%s条评论', 'navai'), number_format_i18n($comment_count));
			}
			?>
		</h3>

		<ol class="comment-list">
			<?php
			wp_list_comments(array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 40,
				'callback'    => 'navai_comment_callback',
			));
			?>
		</ol>

		<?php
		the_comments_pagination(array(
			'prev_text' => '<span class="nav-prev">&larr; ' . esc_html__('上一页', 'navai') . '</span>',
			'next_text' => '<span class="nav-next">' . esc_html__('下一页', 'navai') . ' &rarr;</span>',
		));
		?>

	<?php endif; ?>

	<?php
	$commenter       = wp_get_current_commenter();
	$req             = get_option('require_name_email');
	$aria_req        = ($req ? " aria-required='true'" : '');
	$must_login      = get_option('comment_registration') && !is_user_logged_in();
	$can_register    = get_option('users_can_register');
	?>

	<div id="respond" class="comment-respond">
		<h3 id="reply-title" class="comment-reply-title"><?php esc_html_e('发表评论', 'navai'); ?></h3>

		<?php if ($must_login) : ?>
		<!-- 需要登录才能评论 -->
		<div class="comment-must-login">
			<svg class="comment-must-login-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="48" height="48">
				<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
				<path d="M7 11V7a5 5 0 0 1 10 0v4"/>
			</svg>
			<p class="comment-must-login-text"><?php esc_html_e('登录后才能发表评论', 'navai'); ?></p>
			<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="comment-must-login-btn">
				<?php esc_html_e('立即登录', 'navai'); ?>
			</a>
			<?php if ($can_register) : ?>
			<a href="<?php echo esc_url(wp_registration_url()); ?>" class="comment-must-login-register">
				<?php esc_html_e('还没有账号？立即注册', 'navai'); ?>
			</a>
			<?php endif; ?>
		</div>
		<?php else : ?>
		<form action="<?php echo esc_url(site_url('wp-comments-post.php')); ?>" method="post" id="commentform" class="comment-form">
			<p class="comment-form-comment">
				<label for="comment"><?php esc_html_e('评论内容', 'navai'); ?></label>
				<textarea id="comment" name="comment" cols="45" rows="4" aria-required="true" placeholder="<?php esc_attr_e('输入评论内容...', 'navai'); ?>"></textarea>
			</p>

			<?php if (!$is_logged_in) : ?>
			<div class="comment-form-row">
				<p class="comment-form-author">
					<label for="author"><?php esc_html_e('昵称', 'navai'); ?><?php echo ($req ? '<span class="required">*</span>' : ''); ?></label>
					<input id="author" name="author" type="text" value="<?php echo esc_attr($commenter['comment_author']); ?>" size="30" <?php echo $aria_req; ?> placeholder="<?php esc_attr_e('昵称', 'navai'); ?>" />
				</p>
				<p class="comment-form-email">
					<label for="email"><?php esc_html_e('邮箱', 'navai'); ?><?php echo ($req ? '<span class="required">*</span>' : ''); ?></label>
					<input id="email" name="email" type="email" value="<?php echo esc_attr($commenter['comment_author_email']); ?>" size="30" <?php echo $aria_req; ?> placeholder="<?php esc_attr_e('邮箱', 'navai'); ?>" />
				</p>
			</div>
			<?php else : ?>
			<div class="comment-form-logged-in">
				<p><?php printf(esc_html__('您已登录为 %s。', 'navai'), '<strong>' . esc_html($current_user->display_name) . '</strong>'); ?><?php wp_loginout(get_permalink()); ?></p>
			</div>
			<?php endif; ?>

			<p class="form-submit">
				<button type="submit" class="submit"><?php esc_html_e('发表评论', 'navai'); ?></button>
				<?php comment_id_fields(); ?>
			</p>
			<?php do_action('comment_form', get_the_ID()); ?>
		</form>
		<?php endif; ?>
	</div>

</div>

<?php
// 前端防护：如果需要登录但表单仍在显示（缓存等场景），JS 拦截提交
if ($must_login) :
?>
<script>
(function() {
	var form = document.getElementById('commentform');
	if (form) {
		form.addEventListener('submit', function(e) {
			e.preventDefault();
			var loginUrl = '<?php echo esc_js(wp_login_url(get_permalink())); ?>';
			window.location.href = loginUrl;
		}, false);
	}
})();
</script>
<?php endif; ?>

