<?php
/*
 * Plugin Name: Add Code to Head
 * Plugin URI: http://www.hbjitney.com/wordpress/add-code-to-head
 * Description: Adds any code to a page's head (javascript, css, etc)
 * Version: 1.0
 * Author: Bryan Hanks, PMP
 * Author URI: http://hbjitney.com/
 * License: GPL3

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class AddCodeToHead {
	function __construct() {
		// add the admin options page
		add_action( 'admin_menu', array( $this, 'add_admin' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp_head', array( $this, 'display' ) );
	}

	function add_admin() {
		add_options_page('Add Code to Head', 'Add Code to Head', 'manage_options', 'acth_plugin', array( $this, 'plugin_options_page' ) );
	}

	function plugin_options_page() { ?>
	<div class="plugin-options">
	 <h2><span>Add Code to Head</span></h2>
	 <form action="options.php" method="post">
	  <?php
	  settings_fields('acth_options');
	  do_settings_sections('acth_plugin'); ?>

	  <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	 </form>
	</div>
	<?php }

	// define fields

	function admin_init() {
		register_setting( 'acth_options', 'acth_options', array( $this, 'options_validate' ) );
		add_settings_section( 'acth_section', '', array( $this, 'main_section' ), 'acth_plugin' );
		add_settings_field( 'acth_string', 'Code', array( $this, 'text_field'), 'acth_plugin', 'acth_section');
	}

	function main_section() { ?>
	<!--<p>Main section</p>-->
	<?php }

	// Code for field
	function text_field() {
		$options = get_option('acth_options'); ?>
	        <textarea id="acth_options" name="acth_options[text_string]" rows="20" cols="90"><?php _e($options['text_string']); ?></textarea>
	<?php }


	// Validate input
	function options_validate($input) {
		$newinput['text_string'] = trim( $input['text_string'] );
		return $newinput;
	}

	// Display it
	function display() {
		if( !is_admin() ) {
			$options = get_option('acth_options');
			_e( "<!--- ADD TEXT TO HEAD plugin ************************ -->" );
			_e( $options['text_string'] );
			_e( "<!--- /ADD TEXT TO HEAD plugin *********************** -->" );
		}
	}
}
new AddCodeToHead();
?>
