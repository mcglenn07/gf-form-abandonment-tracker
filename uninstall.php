<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'gfat_abandonments';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
