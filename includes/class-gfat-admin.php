<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFAT_Admin {

	public function __construct() {
		add_filter( 'gform_form_settings_menu', array( $this, 'add_form_settings_tab' ), 10, 2 );
		add_action( 'gform_form_settings_page_gfat', array( $this, 'render_settings_page' ) );
	}

	public function add_form_settings_tab( $tabs, $form_id ) {
		$tabs[] = array(
			'name'  => 'gfat',
			'label' => __( 'Abandonment', 'gf-form-abandonment-tracker' ),
			'icon'  => 'dashicons-chart-area',
		);

		return $tabs;
	}

	public function render_settings_page() {
		$form_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		GFFormSettings::page_header();

		$total  = GFAT_DB::total_for_form( $form_id );
		$breakdown = GFAT_DB::counts_by_form( $form_id );

		echo '<h3>' . esc_html__( 'Form Abandonment', 'gf-form-abandonment-tracker' ) . '</h3>';
		echo '<p>' . sprintf(
			/* translators: %d: number of abandoned sessions */
			esc_html__( '%d visitor(s) started this form without submitting it.', 'gf-form-abandonment-tracker' ),
			$total
		) . '</p>';

		if ( empty( $breakdown ) ) {
			echo '<p>' . esc_html__( 'No abandonment data yet.', 'gf-form-abandonment-tracker' ) . '</p>';
		} else {
			echo '<table class="widefat striped" style="max-width:600px;">';
			echo '<thead><tr><th>' . esc_html__( 'Last Field Touched', 'gf-form-abandonment-tracker' ) . '</th><th>' . esc_html__( 'Drop-offs', 'gf-form-abandonment-tracker' ) . '</th></tr></thead><tbody>';

			foreach ( $breakdown as $row ) {
				$label = $row->last_field_id ? $row->last_field_id : __( '(no field touched)', 'gf-form-abandonment-tracker' );
				echo '<tr><td>' . esc_html( $label ) . '</td><td>' . esc_html( $row->total ) . '</td></tr>';
			}

			echo '</tbody></table>';
		}

		GFFormSettings::page_footer();
	}
}
