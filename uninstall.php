<?php
/*
 * Tasks to perform when uninstalling plugin
 */
if( !defined( WP_UNINSTALL_PLUGIN ) ) {
	wp_die( "Don't call this file directly. If you wish to uninstall, use the admin tool to do it." );
} else {
	delete_option( 'acth_options' );
}
?>