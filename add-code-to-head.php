<?php
/**
 * Plugin Name: Add Code to Head
 * Plugin URI:  http://hbjitney.com/add-code-to-header.html
 * Description: Adds custom HTML code (JavaScript, CSS, etc.) to each public page's &lt;head&gt;.
 * Version:     1.23
 * Author:      HBJitney, LLC
 * Author URI:  http://hbjitney.com/
 * License:     GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: add-code-to-head
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

// Block direct file access. Prevent direct execution.
defined( 'ABSPATH' ) || exit;

define( 'ACTH_OPTION_KEY', 'acth_options' );
define( 'ACTH_VERSION',    '1.20' );

/**
 * Main plugin class.
 *
 * Wraps all hooks and callbacks to avoid polluting the global namespace.
 */
class AddCodeToHead {

	/** @var AddCodeToHead|null Singleton instance. */
	private static ?AddCodeToHead $instance = null;

	/**
	 * Return (and, on first call, create) the singleton instance.
	 *
	 * @return AddCodeToHead
	 */
	public static function get_instance(): AddCodeToHead {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use AddCodeToHead::get_instance() instead.
	 * Registers all WordPress action/filter hooks.
	 */
	private function __construct() {
		add_action( 'admin_menu',          array( $this, 'add_admin' ) );
		add_action( 'admin_init',          array( $this, 'admin_init' ) );
		add_action( 'wp_head',             array( $this, 'display' ) );
		// Add "Settings" shortcut link on the Plugins list screen.
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),
		            array( $this, 'add_settings_link' ) );
	}

	// -------------------------------------------------------------------------
	// Admin menu
	// -------------------------------------------------------------------------

	/**
	 * Register the options page under Settings.
	 */
	public function add_admin(): void {
		add_options_page(
			__( 'Add Code to Head', 'add-code-to-head' ),
			__( 'Add Code to Head', 'add-code-to-head' ),
			'manage_options',
			'acth_plugin',
			array( $this, 'plugin_options_page' )
		);
	}

	/**
	 * Inject our "Settings" link on the Installed Plugins page
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=acth_plugin' ) ),
			esc_html__( 'Settings', 'add-code-to-head' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	// -------------------------------------------------------------------------
	// Options page rendering
	// -------------------------------------------------------------------------

	/**
	 * Render the options page.
	 * Capability is already enforced by add_options_page(), but we re-check
	 * here as a belt-and-suspenders guard before outputting anything.
	 */
	public function plugin_options_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Add Code to Head', 'add-code-to-head' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( ACTH_OPTION_KEY );
				do_settings_sections( 'acth_plugin' );
				submit_button( __( 'Save Changes', 'add-code-to-head' ) );
				?>
			</form>
		</div>
		<?php
	}

	// -------------------------------------------------------------------------
	// Settings API registration
	// -------------------------------------------------------------------------

	public function admin_init(): void {
		register_setting(
			ACTH_OPTION_KEY,
			ACTH_OPTION_KEY,
			array( $this, 'options_validate' )
		);

		add_settings_section(
			'acth_section',
			'', // No section title
			'__return_null',
			'acth_plugin',
			array(
				// Note: before_section requires WP 6.1+
				'before_section' => $this->warning_html(),
			)
		);

		add_settings_field(
			'acth_string',
			__( 'Code', 'add-code-to-head' ),
			array( $this, 'text_field' ),
			'acth_plugin',
			'acth_section'
		);
	}

	/**
	 * Build the warning banner shown above the textarea.
	 * Kept as a method so the string is constructed once
   * and can be unit-tested.
	 *
	 * @return string Safe HTML string.
	 */
	private function warning_html(): string {
		return sprintf(
			'<div class="notice notice-warning"><p><strong>%s</strong><br>%s</p><p>%s</p></div>',
			esc_html__( '⚠️ WARNING:', 'add-code-to-head' ),
			esc_html__( 'All code entered here is added to every public page on the blog. Any script you add here will run on every page, for every visitor.', 'add-code-to-head' ),
			esc_html__( 'Only Trusted Administrators should use this function.', 'add-code-to-head' )
		);
	}

	// -------------------------------------------------------------------------
	// Field rendering
	// -------------------------------------------------------------------------

	/**
	 * Render the textarea field.
	 */
	public function text_field(): void {
		$options = get_option( ACTH_OPTION_KEY );
		$val     = isset( $options['text_string'] ) ? $options['text_string'] : '';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_textarea() applied.
		printf(
			'<textarea id="%s" name="%s[text_string]" rows="20" cols="90">%s</textarea>',
			esc_attr( ACTH_OPTION_KEY ),
			esc_attr( ACTH_OPTION_KEY ),
			esc_textarea( $val )
		);
	}

	// -------------------------------------------------------------------------
	// Sanitization / validation
	// -------------------------------------------------------------------------

	/**
	 * Sanitize incoming option value before it is stored.
	 *
	 * Users with `unfiltered_html` (administrators on single-site installs) may
	 * save arbitrary markup — that is the intentional purpose of this plugin.
	 * All other roles have their input stripped to safe post-level HTML.
	 *
	 * @param array $input Raw POST input.
	 * @return array Sanitized options array.
	 */
	public function options_validate( array $input ): array {
		$new = array();
		$new['text_string'] = isset( $input['text_string'] ) ? trim( $input['text_string'] ) : '';

		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$new['text_string'] = wp_kses_post( $new['text_string'] );
		}

		return $new;
	}

	// -------------------------------------------------------------------------
	// Front-end output
	// -------------------------------------------------------------------------

	/**
	 * Echo the saved code into the public <head>.
	 *
	 * The is_admin() guard prevents accidental output if hook timing ever changes.
	 *
	 * NOTE: Output is intentionally NOT escaped — arbitrary HTML/JS injection
	 * into the <head> is the entire purpose of this plugin. Access is controlled
	 * at save-time via options_validate() and the manage_options capability.
	 */
	public function display(): void {
		if ( is_admin() ) {
			return;
		}

		$options = get_option( ACTH_OPTION_KEY );
		$code    = isset( $options['text_string'] ) ? $options['text_string'] : '';

		if ( '' !== $code ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "\n" . $code . "\n";
		}
	}
}

// ****************************************************************************
// *                             === BOOTSTRAP ===                            *
// *           Direct instantiation via the singleton factory on the          *
// *     'plugins_loaded' hook, the correct point to initialize a plugin.     *
// ****************************************************************************
add_action( 'plugins_loaded', array( 'AddCodeToHead', 'get_instance' ) );
