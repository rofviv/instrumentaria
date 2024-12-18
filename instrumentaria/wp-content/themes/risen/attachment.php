<?php
/**
 * Attachments
 *
 * When possible, redirect attachment to actual post for viewing
 */

if ( ! empty( $post->post_parent ) ) {
	$url = get_permalink( $post->post_parent );
}

if ( empty( $url ) ) {
	$url = home_url();
}

wp_redirect( $url );

exit;