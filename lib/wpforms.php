<?php
/**
 * StudioPress WPForms helper functions.
 *
 * Creates a form during one-click theme setup if one has not been created already.
 *
 * @package StudioPress
 * @author  StudioPress
 * @license GPL-2.0-or-later
 * @link    https://www.studiopress.com/
 */

/**
 * Creates a WPForms form if one added by a StudioPress theme does not exist.
 *
 * @since 2.10.0
 *
 * @return int|null ID of form if one exists or gets created. Null if form creation fails or WPForms is inactive.
 */
function studiopress_maybe_create_wpforms_form() { // phpcs:ignore -- studiopress prefix for functions shared between themes.

	if ( ! function_exists( 'wpforms' ) ) {
		return;
	}

	$existing_form_id = get_option( 'genesis_onboarding_wpforms_id' );

	if ( $existing_form_id ) {
		$wpform = get_post( $existing_form_id );

		// Don't create another form if a valid one already exists.
		if ( $wpform && 'wpforms' === $wpform->post_type ) {
			return $existing_form_id;
		}

		// Stored ID no longer points to a WPForms form.
		delete_option( 'genesis_onboarding_wpforms_id' );
	}

	// Creates a form using the WPForms 'contact' template.
	$new_form_id = wpforms()->form->add(
		esc_html__( 'Simple Contact Form', 'genesis-sample' ),
		array(),
		array(
			'template' => 'contact',
			'builder'  => false,
		)
	);

	if ( $new_form_id ) {
		update_option( 'genesis_onboarding_wpforms_id', $new_form_id, false );
		return $new_form_id;
	}

}

/**
 * Replace contact page placeholder content with a block displaying the form.
 *
 * @since 2.10.0
 *
 * @param array $content The content config.
 * @param array $imported_posts Imported posts with content short name as keys and IDs as values.
 */
function studiopress_insert_contact_form( $content, $imported_posts ) { // phpcs:ignore -- studiopress prefix for functions shared between themes.

	$form_id = studiopress_maybe_create_wpforms_form();

	if ( $form_id && array_key_exists( 'contact', $imported_posts ) ) {
		$contact_page = array(
			'ID'           => $imported_posts['contact'],
			'post_content' => "<!-- wp:wpforms/form-selector {\"formId\":\"{$form_id}\"} /-->",
		);

		wp_update_post( $contact_page );
	}

}