<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFAT_DB {

	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'gfat_abandonments';
	}

	public static function install() {
		global $wpdb;

		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			form_id BIGINT UNSIGNED NOT NULL,
			last_field_id VARCHAR(50) DEFAULT '' NOT NULL,
			page_url VARCHAR(500) DEFAULT '' NOT NULL,
			session_token VARCHAR(64) DEFAULT '' NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY form_id (form_id),
			KEY session_token (session_token)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function insert( $form_id, $last_field_id, $page_url, $session_token ) {
		global $wpdb;

		$wpdb->insert(
			self::table_name(),
			array(
				'form_id'       => $form_id,
				'last_field_id' => $last_field_id,
				'page_url'      => $page_url,
				'session_token' => $session_token,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Removes a tracked abandonment when the same session successfully submits the form.
	 */
	public static function clear_session( $session_token ) {
		global $wpdb;

		$wpdb->delete(
			self::table_name(),
			array( 'session_token' => $session_token ),
			array( '%s' )
		);
	}

	public static function counts_by_form( $form_id ) {
		global $wpdb;
		$table = self::table_name();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT last_field_id, COUNT(*) as total
				 FROM {$table}
				 WHERE form_id = %d
				 GROUP BY last_field_id
				 ORDER BY total DESC",
				$form_id
			)
		);
	}

	public static function total_for_form( $form_id ) {
		global $wpdb;
		$table = self::table_name();

		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE form_id = %d", $form_id )
		);
	}
}
