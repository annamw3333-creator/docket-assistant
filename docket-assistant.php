<?php
/**
 * Plugin Name:       Docket Assistant
 * Plugin URI:        https://wordpress.org/plugins/docket-assistant/
 * Description:       Adds your Docket website chat to every public page. Connects to the Docket service — paste your desk URL and bot ID in settings.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Docket
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       docket-assistant
 *
 * @package DocketAssistant
 */

if (!defined('ABSPATH')) {
	exit;
}

define('DOCKET_ASSISTANT_VERSION', '1.0.0');
define('DOCKET_ASSISTANT_FILE', __FILE__);

/**
 * Default option payload.
 *
 * @return array{service_url: string, bot_id: string}
 */
function docket_assistant_defaults() {
	return array(
		'service_url' => '',
		'bot_id'      => '',
	);
}

/**
 * @return array{service_url: string, bot_id: string}
 */
function docket_assistant_get_settings() {
	$stored = get_option('docket_assistant_settings', array());
	if (!is_array($stored)) {
		$stored = array();
	}
	return wp_parse_args($stored, docket_assistant_defaults());
}

/**
 * @param mixed $input Raw settings from the form.
 * @return array{service_url: string, bot_id: string}
 */
function docket_assistant_sanitize_settings($input) {
	$clean = docket_assistant_defaults();
	if (!is_array($input)) {
		return $clean;
	}

	$url = isset($input['service_url']) ? trim((string) $input['service_url']) : '';
	$url = esc_url_raw($url, array('http', 'https'));
	$clean['service_url'] = $url ? untrailingslashit($url) : '';

	$id = isset($input['bot_id']) ? (string) $input['bot_id'] : '';
	$id = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $id));
	$clean['bot_id'] = substr($id, 0, 64);

	return $clean;
}

/**
 * @return bool
 */
function docket_assistant_is_configured() {
	$opts = docket_assistant_get_settings();
	return ($opts['service_url'] !== '' && $opts['bot_id'] !== '');
}

/**
 * @return string
 */
function docket_assistant_widget_url() {
	$opts = docket_assistant_get_settings();
	if ($opts['service_url'] === '' || $opts['bot_id'] === '') {
		return '';
	}
	return trailingslashit($opts['service_url']) . 'widget/' . rawurlencode($opts['bot_id']);
}

add_action(
	'admin_init',
	function () {
		register_setting(
			'docket_assistant',
			'docket_assistant_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'docket_assistant_sanitize_settings',
				'default'           => docket_assistant_defaults(),
				'show_in_rest'      => false,
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_options_page(
			__('Docket Assistant', 'docket-assistant'),
			__('Docket Assistant', 'docket-assistant'),
			'manage_options',
			'docket-assistant',
			'docket_assistant_render_settings'
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename(DOCKET_ASSISTANT_FILE),
	function ($links) {
		if (!is_array($links)) {
			$links = array();
		}
		$url = admin_url('options-general.php?page=docket-assistant');
		array_unshift(
			$links,
			'<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'docket-assistant') . '</a>'
		);
		return $links;
	}
);

add_action(
	'admin_notices',
	function () {
		if (!current_user_can('manage_options')) {
			return;
		}
		if (docket_assistant_is_configured()) {
			return;
		}
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen) {
			return;
		}
		$on_plugins  = ($screen->id === 'plugins');
		$on_settings = ($screen->id === 'settings_page_docket-assistant');
		if (!$on_plugins && !$on_settings) {
			return;
		}
		$url = admin_url('options-general.php?page=docket-assistant');
		echo '<div class="notice notice-warning"><p>';
		echo esc_html__('Docket Assistant is installed but not connected.', 'docket-assistant');
		echo ' <a href="' . esc_url($url) . '">';
		echo esc_html__('Add your Docket URL and bot ID', 'docket-assistant');
		echo '</a>.</p></div>';
	}
);

/**
 * Settings screen.
 */
function docket_assistant_render_settings() {
	if (!current_user_can('manage_options')) {
		return;
	}
	$opts = docket_assistant_get_settings();
	?>
	<div class="wrap">
		<h1><?php echo esc_html__('Docket Assistant', 'docket-assistant'); ?></h1>
		<p><?php echo esc_html__('This plugin is a connector for the Docket service. Chat replies are generated on your Docket desk, not inside WordPress. You need a Docket account and a published assistant.', 'docket-assistant'); ?></p>
		<form action="options.php" method="post">
			<?php settings_fields('docket_assistant'); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="docket_assistant_service_url"><?php echo esc_html__('Docket URL', 'docket-assistant'); ?></label>
					</th>
					<td>
						<input
							type="url"
							class="regular-text code"
							id="docket_assistant_service_url"
							name="docket_assistant_settings[service_url]"
							value="<?php echo esc_attr($opts['service_url']); ?>"
							placeholder="https://your-docket-site.example"
							autocomplete="off"
						/>
						<p class="description"><?php echo esc_html__('The public address of your Docket desk. Copy it from Add to site in Docket. Must start with https:// (or http:// on a local desk).', 'docket-assistant'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="docket_assistant_bot_id"><?php echo esc_html__('Bot ID', 'docket-assistant'); ?></label>
					</th>
					<td>
						<input
							type="text"
							class="regular-text code"
							id="docket_assistant_bot_id"
							name="docket_assistant_settings[bot_id]"
							value="<?php echo esc_attr($opts['bot_id']); ?>"
							maxlength="64"
							autocomplete="off"
							spellcheck="false"
						/>
						<p class="description"><?php echo esc_html__('The ID of the published assistant. Copy it from Add to site in Docket after you generate the desk.', 'docket-assistant'); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(__('Save connection', 'docket-assistant')); ?>
		</form>
	</div>
	<?php
}

add_action(
	'wp_enqueue_scripts',
	function () {
		if (is_admin() || !docket_assistant_is_configured()) {
			return;
		}
		$widget = docket_assistant_widget_url();
		if ($widget === '') {
			return;
		}
		wp_enqueue_script(
			'docket-assistant',
			plugins_url('assets/launcher.js', DOCKET_ASSISTANT_FILE),
			array(),
			DOCKET_ASSISTANT_VERSION,
			true
		);
		wp_localize_script(
			'docket-assistant',
			'docketAssistant',
			array(
				'widgetUrl'  => esc_url_raw($widget),
				'openLabel'  => __('Open chat', 'docket-assistant'),
				'closeLabel' => __('Close chat', 'docket-assistant'),
			)
		);
	}
);
