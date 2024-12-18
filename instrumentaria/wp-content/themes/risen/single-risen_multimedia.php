<?php
/**
 * Single Multimedia Item Template
 */
 
// Get media URLs
$video_url = risen_multimedia_url( $post->ID, 'video' );
$audio_url = risen_multimedia_url( $post->ID, 'audio' );
$pdf_url = risen_multimedia_url( $post->ID, 'pdf' );

// Header
get_template_part( 'header', 'multimedia-archive');

?>

<div id="content">

	<div id="content-inner"<?php if ( risen_sidebar_enabled( 'multimedia' ) ) : ?> class="has-sidebar"<?php endif; ?>>

		<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header>

				<h1 id="multimedia-single-page-title" class="page-title">
					<?php the_title(); ?>
					<?php if ( $numpages > 1 ) : ?>
					<span><?php printf( __( '(Page %s)', 'risen' ), $page, $numpages ); ?></span>
					<?php endif; ?>
				</h1>
			
				<div id="multimedia-single-header-meta" class="box multimedia-header-meta">

					<div class="multimedia-time-speaker">
				
						<time datetime="<?php the_time( 'c' ); ?>"><?php echo risen_date_ago( get_the_time( 'U' ), 5 ); // show up to "5 days ago" but actual date if older ?></time>

						<?php
						/* translators: used between list items, there is a space after the comma */
						$speaker_list = get_the_term_list( $post->ID, 'risen_multimedia_speaker', '', __( ', ', 'risen' ) );
						if ( ! empty( $speaker_list ) ) :
						?>
						<span class="multimedia-header-meta-speaker"><?php echo sprintf( _x( 'by %s', 'multimedia speaker', 'risen'), $speaker_list ); ?></span>
						<?php endif; ?>
					
					</div>

					<ul class="multimedia-header-meta-icons risen-icon-list dark">
						<?php if ( ( comments_open() || get_comments_number() > 0 ) && ! post_password_required() ) : // show X comments if some were posted, even if no new comments are off; always hide if post is protected ?>
						<li><?php comments_popup_link( __( '0 Comments', 'risen' ), __( '1 Comment', 'risen' ), __( '% Comments', 'risen' ), 'single-icon comment-icon scroll-to-comments', '' ); ?><?php comments_popup_link( __( '0 Comments', 'risen' ), __( '1 Comment', 'risen' ), __( '% Comments', 'risen' ), 'risen-icon-label scroll-to-comments', '' ); ?></li>
						<?php endif; ?>
					</ul>
					
					<div class="clear"></div>
					
				</div>

			</header>
		
			<?php // video and/or audio player
			$video = risen_video( $video_url );
			if ( ( ! empty( $video['embed_code'] ) || $audio_url ) && ! post_password_required() ) : // we have video or audio and post is not password protected
			?>		
			<div id="multimedia-single-media-player">
			
				<?php if ( ! empty( $video['embed_code'] ) ) : ?>
				<?php echo $video['embed_code']; // has container with classes .video-container and .youtube-video (or .vimeo-video) ?>
				<?php endif; ?>
				
				<?php if ( $audio_url ) : ?>
				<div class="audio-container<?php echo ! empty( $video['embed_code'] ) ? ' hidden' : ''; // hide audio player if video player present ?>">
					<audio class="audio-player" src="<?php echo esc_url( $audio_url ); ?>" type="audio/mp3" controls="controls"></audio>
				</div>
				<?php endif; ?>
				
				<div class="clear"></div>
				
			</div>
			<?php endif; ?>
			
			<?php // show media options if there is more than one
			$media_options = 0;			
			$media_options += $video_url ? 1 : 0;
			$media_options += $audio_url ? 2 : 0; // 2 because it can be played or downloaded
			$media_options += $pdf_url ? 1 : 0;		
			if ( ( $media_options > 1 || $pdf_url ) && ! post_password_required() ) : // don't show if post is password protected		
			?>

			<div id="multimedia-single-options" class="box">
			
				<ul class="multimedia-header-meta-icons risen-icon-list">
					
					<?php if ( $video_url ) : ?>
					<li><a href="<?php echo esc_url( $video_url ); ?>" class="single-icon video-icon play-video-link"><?php _e( 'Play Video', 'risen' ); ?></a><a href="<?php echo esc_url( $video_url ); ?>" class="risen-icon-label play-video-link"><?php _e( 'Play Video', 'risen' ); ?></a></li>
					<?php endif; ?>
					
					<?php if ( $audio_url ) : ?>
					<li><a href="<?php echo esc_url( $audio_url ); ?>" class="single-icon audio-icon play-audio-link"><?php _e( 'Play Audio', 'risen' ); ?></a><a href="<?php echo esc_url( $audio_url ); ?>" class="risen-icon-label play-audio-link"><?php _e( 'Play Audio', 'risen' ); ?></a></li>
					<?php endif; ?>
				
					<?php if ( $audio_url ) : ?>
					<li><a href="<?php echo esc_url( risen_force_download_url( $audio_url ) ); ?>" class="single-icon audio-icon"><?php _e( 'Download Audio', 'risen' ); ?></a><a href="<?php echo esc_url( risen_force_download_url( $audio_url ) ); ?>" class="risen-icon-label"><?php _e( 'Download MP3', 'risen' ); ?></a></li>
					<?php endif; ?>
					
					<?php if ( $pdf_url ) : ?>
					<li><a href="<?php echo esc_url( risen_force_download_url( $pdf_url ) ); ?>" class="single-icon pdf-icon"><?php _e( 'Download PDF', 'risen' ); ?></a><a href="<?php echo esc_url( risen_force_download_url( $pdf_url ) ); ?>" class="risen-icon-label"><?php _e( 'Download PDF', 'risen' ); ?></a></li>
					<?php endif; ?>
						
				</ul>
				
				<div class="clear"></div>
				
			</div>
			
			<?php endif; ?>
		
			<div class="post-content"> <!-- confines heading font to this content -->
		
				<?php the_content() ?>
				
				<?php if ( ! get_the_content() ) : // if no content, let's show the excerpt here ?>
				<?php the_excerpt(); ?>
				<?php endif; ?>
			
			</div>
			
			<?php
			// multipage post nav: 1, 2, 3, etc. for when <!--nextpage--> is used in content
			if ( ! post_password_required() ) {
				wp_link_pages( array(
					'before'	=> '<div class="box multipage-nav"><span>' . __( 'Pages:', 'risen' ) . '</span>',
					'after'		=> '</div>'
				) );
			}
			?>

			<?php
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_term_list( $post->ID, 'risen_multimedia_category', '', __( ', ', 'risen' ) );
			/* translators: used between list items, there is a space after the comma */
			$tag_list = get_the_term_list( $post->ID, 'risen_multimedia_tag', '', __( ', ', 'risen' ) );
			if ( $categories_list || $tag_list || get_edit_post_link( $post->ID ) ) :
			?>
			<footer id="multimedia-single-footer-meta" class="box post-footer<?php echo ( get_edit_post_link() ? ' can-edit-post' : '' ); // add class if there will be an edit button ?>">

				<?php
				if ( ! empty( $categories_list ) ) :
				?>
				<div id="multimedia-single-categories"><?php printf( __( 'Posted in %s', 'risen' ), $categories_list ); ?></div>
				<?php endif; ?>
				
				<?php
				if ( ! empty( $tag_list ) ) :
				?>
				<div id="multimedia-single-tags"><?php printf( __( 'Tagged with %s', 'risen' ), $tag_list ); ?></div>
				<?php endif; ?>
				
				<?php edit_post_link( __( 'Edit Post', 'risen' ), '<span class="post-edit-link-container">', '</span>' ); // edit link for admin if logged in ?>

			</footer>
			<?php endif; ?>
			
		</article>

		<?php comments_template( '', true ); ?>

		<?php endwhile; // end of the loop. ?>

		<nav class="nav-left-right" id="multimedia-single-nav">
			<div class="nav-left"><?php next_post_link( '%link', sprintf( _x( '<span>&larr;</span> Newer %s', 'multimedia singular', 'risen' ), risen_option( 'multimedia_word_singular' ) ) ); ?></div>
			<div class="nav-right"><?php previous_post_link( '%link', sprintf( _x( 'Older %s <span>&rarr;</span>', 'multimedia singular', 'risen' ), risen_option( 'multimedia_word_singular' ) ) ); ?></div>
			<div class="clear"></div>
		</nav>
				
	</div>

</div>

<?php risen_show_sidebar( 'multimedia' ); ?>

<?php get_footer(); ?>