<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFAT_Tracker {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_gfat_log_abandonment', array( $this, 'ajax_log_abandonment' ) );
		add_action( 'wp_ajax_nopriv_gfat_log_abandonment', array( $this, 'ajax_log_abandonment' ) );
		add_action( 'gform_after_submission', array( $this, 'clear_on_submission' ), 10, 2 );
	}

	public function enqueue_assets() {
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		wp_enqueue_script(
			'gfat-tracker',
			GFAT_URL . 'assets/js/tracker.js',
			array(),
			GFAT_VERSION,
			true
		);

		wp_localize_script(
			'gfat-tracker',
			'gfatTracker',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gfat_track_nonce' ),
			)
		);
	}

	public function ajax_log_abandonment() {
		check_ajax_referer( 'gfat_track_nonce', 'nonce' );

		$form_id       = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$last_field_id = isset( $_POST['last_field_id'] ) ? sanitize_text_field( wp_unslash( $_POST['last_field_id'] ) ) : '';
		$page_url      = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';
		$session_token = isset( $_POST['session_token'] ) ? sanitize_text_field( wp_unslash( $_POST['session_token'] ) ) : '';

		if ( ! $form_id || ! $session_token ) {
			wp_send_json_error();
		}

		GFAT_DB::insert( $form_id, $last_field_id, $page_url, $session_token );
		wp_send_json_success();
	}

	public function clear_on_submission( $entry, $form ) {
		$session_token = isset( $_POST['gfat_session_token'] ) ? sanitize_text_field( wp_unslash( $_POST['gfat_session_token'] ) ) : '';

		if ( $session_token ) {
			GFAT_DB::clear_session( $session_token );
		}
	}
}
