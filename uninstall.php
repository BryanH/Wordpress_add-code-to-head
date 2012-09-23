<?php
/*
 * Tasks to perform when uninstalling plugin
 */
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	$message = "<h2 style='color:red'>Error in plugin</h2>
	Plugin <span style='color:blue;font-family:monospace'>add-code-to-head</span> says:</p>
	<p>Don't call this file directly. If you wish to uninstall, use the admin tool to do it.</p>";
	wp_die( $message );
} else {
	delete_option( 'acth_options' );
}
?>