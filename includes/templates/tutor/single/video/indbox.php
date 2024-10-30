<?php
/**
 * Display Video Integrate Dropbox
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_info = tutor_utils()->get_video_info();
$poster     = tutor_utils()->avalue_dot( 'poster_indbox', $video_info );

do_action( 'tutor_lesson/single/before/video/indbox' );

?>

<?php if ( $video_info ) : ?>
	<div class="tutor-video-player">
		<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $jsonData ?? null ) ); ?>">
		<div class="loading-spinner" aria-hidden="true"></div>
		<video poster="<?php echo esc_url( $poster ); ?>" class="tutorPlayer" playsinline controls >
			<source src="<?php echo esc_url( tutor_utils()->array_get( 'source_indbox', $video_info ) ); ?>" type="<?php echo esc_attr( tutor_utils()->avalue_dot( 'type', $video_info ) ); ?>">
		</video>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_lesson/single/after/video/indbox' ); ?>
