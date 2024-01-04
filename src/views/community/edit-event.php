<?php
/**
 * Event Submission Form
 * The wrapper template for the event submission form.
 *
 * Override this template in your own theme by creating a file at
 * [your-theme]/tribe-events/community/edit-event.php
 *
 * @link https://evnt.is/1ao4 Help article for Community Events & Tickets template files.
 *
 * @since 3.1
 * @since 4.8.2 Updated template link.
 * @since 4.8.10 Use datepicker format from the date utils library to autofill the start and end dates.
 *
 * @version 4.8.10
 *
 * @var int|string $tribe_event_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! isset( $tribe_event_id ) ) {
	$tribe_event_id = null;
}

$datepicker_format = Tribe__Date_Utils::get_datepicker_format_index();

/** @var Tribe__Events__Community__Main $main */
$main = tribe( 'community.main' );

// Variables for Title view.
$events_label_singular = tribe_get_event_label_singular();

// Variables for the Terms view.
$terms_enabled = $main->getOption( 'termsEnabled' );

$terms_description = $main->getOption( 'termsDescription' );

// Variables for the Website view.
$event_url = $_POST['EventURL'] ?? tribe_get_event_website_url();
$event_url = esc_attr( $event_url );


?>

<?php tribe_get_template_part( 'community/modules/header-links' ); ?>

<?php do_action( 'tribe_events_community_form_before_template', $tribe_event_id ); ?>

<form id="tec_ce_submission_form" method="post" enctype="multipart/form-data" data-datepicker_format="<?php echo esc_attr( $datepicker_format ); ?>">
	<input type="hidden" name="post_ID" id="post_ID" value="<?php echo absint( $tribe_event_id ); ?>"/>
	<?php wp_nonce_field( 'ecp_event_submission' ); ?>

	<?php tribe_get_template_part( 'community/modules/title', null, [ 'events_label_singular' => $events_label_singular ] ); ?>

	<?php tribe_get_template_part( 'community/modules/description' ); ?>

	<?php tribe_get_template_part( 'community/modules/datepickers' ); ?>

	<?php tribe_get_template_part( 'community/modules/image' ); ?>

	<?php tribe_get_template_part( 'community/modules/taxonomy', null, [ 'taxonomy' => Tribe__Events__Main::TAXONOMY ] ); ?>

	<?php tribe_get_template_part( 'community/modules/taxonomy', null, [ 'taxonomy' => 'post_tag' ] ); ?>

	<?php
	/**
	 * Action hook before loading linked post types template parts.
	 *
	 * Useful if you want to insert your own additional custom linked post types.
	 *
	 * @since 4.5.13
	 *
	 * @param int|string $tribe_event_id The Event ID.
	 */
	do_action( 'tribe_events_community_form_before_linked_posts', $tribe_event_id );
	?>

	<?php tribe_get_template_part( 'community/modules/venue' ); ?>

	<?php tribe_get_template_part( 'community/modules/organizer' ); ?>

	<?php
	/**
	 * Action hook after loading linked post types template parts.
	 *
	 * Useful if you want to insert your own additional custom linked post types.
	 *
	 * @since 4.5.13
	 *
	 * @param int|string $tribe_event_id The Event ID.
	 */
	do_action( 'tribe_events_community_form_after_linked_posts', $tribe_event_id );
	?>

	<?php tribe_get_template_part( 'community/modules/website', null, [ 'event_url' => $event_url ] ); ?>

	<?php tribe_get_template_part( 'community/modules/series' ); ?>

	<?php tribe_get_template_part( 'community/modules/custom' ); ?>

	<?php tribe_get_template_part( 'community/modules/cost' ); ?>

	<?php tribe_get_template_part( 'community/modules/spam-control' ); ?>

	<?php tribe_get_template_part( 'community/modules/terms', null, [ 'terms_enabled' => $terms_enabled, 'terms_description' => $terms_description ] ); ?>

	<?php tribe_get_template_part( 'community/modules/submit' ); ?>
</form>

<?php do_action( 'tribe_events_community_form_after_template', $tribe_event_id ); ?>
