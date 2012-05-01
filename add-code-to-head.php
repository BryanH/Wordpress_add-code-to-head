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

// add the admin options page
add_action('admin_menu', 'add_head_admin');
function add_head_admin() {
	add_options_page('Add Code to Head', 'Add Code to Head', 'manage_options', 'acth_plugin', 'plugin_options_page');
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
add_action('admin_init', 'add_head_admin_init');

function add_head_admin_init() {
	register_setting( 'acth_options', 'acth_options', 'acth_options_validate' );
	add_settings_section('acth_main_section', '', 'acth_main_section', 'acth_plugin');
	add_settings_field('acth_string', 'Code', 'acth_text_field', 'acth_plugin', 'acth_main_section');
}

function acth_main_section() {?>
<!--<p>Main section</p>-->
<?php }

// Code for field
function acth_text_field() {
	$options = get_option('acth_options'); ?>
        <textarea id="acth_string" name="acth_string" rows="20" cols="90"><?php _e($options['text_string']); ?></textarea>
<?php }


// Validate input
function acth_options_validate() {
}
?>
