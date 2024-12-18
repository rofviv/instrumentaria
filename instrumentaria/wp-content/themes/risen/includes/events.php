<?php
/**
 * Events (Calendar) Functions
 *
 * Post type, meta boxes, admin columns, widgets, etc.
 */

/**********************************
 * POST TYPE
 **********************************/
 
function risen_event_post_type() {

	register_post_type(
		'risen_event',
		array(
			'labels' 	=> array(
				'name'					=> _x( 'Events', 'post type general name', 'risen' ),
				'singular_name'			=> _x( 'Event', 'post type singular name', 'risen' ),
				'add_new' 				=> _x( 'Add New', 'event', 'risen' ),
				'add_new_item' 			=> __( 'Add Event', 'risen' ),
				'edit_item' 			=> __( 'Edit Event', 'risen' ),
				'new_item' 				=> __( 'New Event', 'risen' ),
				'all_items' 			=> __( 'All Events', 'risen' ),
				'view_item' 			=> __( 'View Event', 'risen' ),
				'search_items' 			=> __( 'Search Events', 'risen' ),
				'not_found' 			=> __( 'No events found', 'risen' ),
				'not_found_in_trash' 	=> __( 'No events found in Trash', 'risen' )
			),
			'public' 			=> true,
			'has_archive' 		=> false,
			'show_in_nav_menus' => true,
			'rewrite'			=> array(
				'slug' 	=> 'event-items', // best to use slug different than page using Event/Calendar template or there could be conflicts
				'with_front' => false
			),
			'supports' 			=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'author', 'revisions' )
		)
	);
	
}

/**********************************
 * META BOXES
 **********************************/

/*
UPLOAD BUTTONS NOTE
If you want to use media upload buttons, you MUST configure post type to support 'editor'. Otherwise, the "Insert into Post" button will not be available
If you don't want to show the editor, use a style like this in admin-style.css: .screen-post_type-risen_slide #postdivrich{ display: none } 
Use this HTML after an <input>" <input type="button" value="Upload" class="upload_button button risen-upload-file" />
*/

/**
 * Setup Meta Boxes
 */
 
function risen_event_meta_boxes_setup() {

	// This post type only
	$screen = get_current_screen();
	if ( 'risen_event' == $screen->post_type ) {

		// Add Meta Boxes
		add_action( 'add_meta_boxes', 'risen_event_meta_boxes_add' );

		// Save Meta Boxes
		add_action( 'save_post', 'risen_event_meta_boxes_save', 10, 2 );

	}
	
}

/**
 * Add Meta Boxes
 */
 
function risen_event_meta_boxes_add() {

	// Date & Time
	add_meta_box(
		'risen_event_dates',					// Unique meta box ID
		__( 'Date & Time', 'risen' ),			// Title of meta box
		'risen_event_dates_meta_box_html',		// Callback function to output HTML
		'risen_event',							// Post Type
		'normal',								// Context - Where the meta box appear: normal (left above standard meta boxes), advanced (left below standard boxes), side
		'high'									// Priority - high, core, default or low (see this: http://www.wproots.com/ultimate-guide-to-meta-boxes-in-wordpress/)
	);

	// Location
	add_meta_box(
		'risen_event_location',					// Unique meta box ID
		__( 'Location', 'risen' ),				// Title of meta box
		'risen_event_location_meta_box_html',	// Callback function to output HTML
		'risen_event',							// Post Type
		'normal',								// Context - Where the meta box appear: normal (left above standard meta boxes), advanced (left below standard boxes), side
		'high'									// Priority - high, core, default or low (see this: http://www.wproots.com/ultimate-guide-to-meta-boxes-in-wordpress/)
	);

}

/**
 * Save Meta Boxes
 */

function risen_event_meta_boxes_save( $post_id, $post ) {

	// Validate date values
	// Set them blank if they are invalid
	$date_fields = array( '_risen_event_start_date', '_risen_event_end_date' );
	foreach( $date_fields as $date_field ) {

		// Get and trim
		$date_value = isset( $_POST[$date_field] ) ? trim( $_POST[$date_field] ) : '';
		
		// Check format and values
		if ( ! empty( $date_value ) ) {
		
			// Extract Y-m-d
			list( $y, $m, $d ) = explode( '-', $date_value );

			// Valid date
			if ( strlen( $y ) == 4 && checkdate( $m, $d, $y ) ) { // valid year, date exists

				// Pad month and day with 0 (force 2012-6-1 into 2012-06-01)
				$m = str_pad( $m, 2, '0', STR_PAD_LEFT );
				$d = str_pad( $d, 2, '0', STR_PAD_LEFT );
				
				// Form the most proper date
				$date_value = "$y-$m-$d";
			
			}
			
			// Invalid date (such as February 31 - no such thing! - or "201-31-12")
			else {
				$date_value = ''; // wipe it to avoid sorting issues; user must re-enter
			}
			
		}
		
		// Set trimmed or wiped date
		$_POST[$date_field] = $date_value;
		
	}
	
	// If end date given but start date empty, make end date start date
	if ( empty( $_POST['_risen_event_start_date'] ) && ! empty( $_POST['_risen_event_end_date'] ) ) {
		$_POST['_risen_event_start_date'] = $_POST['_risen_event_end_date'];
		$_POST['_risen_event_end_date'] = '';
	}
	
	// If end date is empty or earlier than start date, use start date as end date
	// Note: end date is required for proper ordering
	if ( ! empty( $_POST['_risen_event_start_date'] )
		 && (
			empty( $_POST['_risen_event_end_date'] )
			|| ( $_POST['_risen_event_end_date'] < $_POST['_risen_event_start_date'] )
		)
	) {
		$_POST['_risen_event_end_date'] = $_POST['_risen_event_start_date'];
	}

	// Date & Time
	$meta_box_id = 'risen_event_dates';
	$meta_keys = array( // fields to validate and save
		'_risen_event_start_date',
		'_risen_event_end_date',
		'_risen_event_time'
	);
	risen_meta_box_save( $meta_box_id, $meta_keys, $post_id, $post );
	
	// Location
	$meta_box_id = 'risen_event_location';
	$meta_keys = array( // fields to validate and save
		'_risen_event_venue',
		'_risen_event_address',
		'_risen_event_map_lat',
		'_risen_event_map_lng',
		'_risen_event_map_type',
		'_risen_event_map_zoom'
	);
	risen_meta_box_save( $meta_box_id, $meta_keys, $post_id, $post );

}

/**
 * Date & Time Meta Box HTML
 */

function risen_event_dates_meta_box_html( $object, $box ) {

	$nonce_params = risen_meta_box_nonce_params( $box['id'] );
	wp_nonce_field( $nonce_params['action'], $nonce_params['key'] );

	?>
	
	<?php $meta_key = '_risen_event_start_date'; ?>
	<p>
		<div class="risen-meta-name">
			<label for="<?php echo $meta_key; ?>"><?php _e( 'Start Date', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value risen-meta-small">
			<input type="text" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo esc_attr( get_post_meta( $object->ID, $meta_key, true ) ); ?>" size="30" />
			<p class="description">
				<?php _e( 'Date must be in YYYY-MM-DD format such as "2012-09-30" for September 30, 2012.', 'risen' ); ?>
			</p>
		</div>
	</p>
	
	<?php $meta_key = '_risen_event_end_date'; ?>
	<p>
		<div class="risen-meta-name">
			<label for="<?php echo $meta_key; ?>"><?php _e( 'End Date', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value risen-meta-small">
			<input type="text" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo esc_attr( get_post_meta( $object->ID, $meta_key, true ) ); ?>" size="30" />
			<p class="description">
				<?php _e( 'Provide an end date if this is a multi-day event.', 'risen' ); ?>
			</p>
		</div>
	</p>
	
	<?php $meta_key = '_risen_event_time'; ?>
	<p>
		<div class="risen-meta-name">
			<label for="<?php echo $meta_key; ?>"><?php _e( 'Time <span>(Optional)</span>', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value risen-meta-medium">
			<input type="text" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo esc_attr( get_post_meta( $object->ID, $meta_key, true ) ); ?>" size="30" />
			<p class="description">
				<?php _e( 'Optionally provide a time such as "8:00 am &ndash; 2:00 pm"', 'risen' ); ?>
			</p>
		</div>
	</p>
	
	<?php

}

/**
 * Location Meta Box HTML
 */

function risen_event_location_meta_box_html( $object, $box ) {

	$screen = get_current_screen();
	
	$nonce_params = risen_meta_box_nonce_params( $box['id'] );
	wp_nonce_field( $nonce_params['action'], $nonce_params['key'] );
	
	?>
	
	<?php $meta_key = '_risen_event_venue'; ?>
	<p>
		<div class="risen-meta-name">
			<label for="<?php echo $meta_key; ?>"><?php _e( 'Venue <span>(Optional)</span>', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value risen-meta-medium">
			<input type="text" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo esc_attr( get_post_meta( $object->ID, $meta_key, true ) ); ?>" size="30" />
			<p class="description">
				<?php _e( 'You can provide a building name, room number or other location name to help people find the event.', 'risen' ); ?>
			</p>
		</div>
	</p>
	
	<?php
	$meta_key = '_risen_event_address';
	$meta_value = get_post_meta( $object->ID, $meta_key, true );
	?>
	<p>
		<div class="risen-meta-name">
			<label for="<?php echo $meta_key; ?>"><?php _e( 'Address <span>(Optional)</span>', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value">
			<textarea name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>"><?php echo esc_textarea( $meta_value ); ?></textarea>
			<p class="description">
				<?php _e( 'You can enter an address if it is necessary for people to find this event.', 'risen' ); ?>
			</p>
		</div>
	</p>
	
	<p>
		<div class="risen-meta-name">
			<label><?php _ex( 'Google Map <span>(Optional)</span>', 'events meta box', 'risen' ); ?></label>
		</div>
	</p>
	
	<p class="description">
		<?php _e( 'Provide the details below if you want to show a map for this event.', 'risen' ); ?>
	</p>
	
	<?php $meta_key = '_risen_event_map_lat'; ?>
	<p>
		<div class="risen-meta-name risen-meta-name-secondary">
			<label for="<?php echo $meta_key; ?>"><?php _e( 'Latitude', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value risen-meta-medium">
			<input type="text" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo esc_attr( get_post_meta( $object->ID, $meta_key, true ) ); ?>" size="30" />
			<p class="description">
				<?php _e( 'You can <a href="http://churchthemes.com/get-latitude-longitude" target="_blank">use this</a> to convert an address into coordinates.', 'risen' ); ?>
			</p>
		</div>
	</p>
	
	<?php $meta_key = '_risen_event_map_lng'; ?>
	<p>
		<div class="risen-meta-name risen-meta-name-secondary">
			<label for="<?php echo $meta_key; ?>"><?php _e( 'Longitude', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value risen-meta-medium">
			<input type="text" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo esc_attr( get_post_meta( $object->ID, $meta_key, true ) ); ?>" size="30" />
		</div>
	</p>
	
	<?php
	$meta_key = '_risen_event_map_type';
	$meta_value = get_post_meta( $object->ID, $meta_key, true );
	if ( 'post' == $screen->base && 'add' == $screen->action ) { // if this is first add, use a default value
		$meta_value = 'HYBRID';
	}
	?>
	<p>
		<div class="risen-meta-name risen-meta-name-secondary">
			<label for="<?php echo $meta_key; ?>"><?php _ex( 'Type', 'map', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value">
			<select name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>">
				<?php echo risen_gmaps_type_options( $meta_value ); ?>			
			</select>
			<p class="description">
				<?php _e( 'You can show a road map, satellite imagery, a combination of both or terrain.', 'risen' ); ?>
			</p>
		</div>
	</p>
	
	<?php
	$meta_key = '_risen_event_map_zoom';
	$meta_value = get_post_meta( $object->ID, $meta_key, true );
	if ( 'post' == $screen->base && 'add' == $screen->action ) { // if this is first add, use a default value
		$meta_value = '14';
	}
	?>
	<p>
		<div class="risen-meta-name risen-meta-name-secondary">
			<label for="<?php echo $meta_key; ?>"><?php _e( 'Zoom Level', 'risen' ); ?></label>
		</div>
		<div class="risen-meta-value">
			<select name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>">
				<?php echo risen_gmaps_zoom_level_options( $meta_value ); ?>			
			</select>
			<p class="description">
				<?php _e( 'A lower number is more zoomed out while a higher number is more zoomed in.', 'risen' ); ?>
			</p>
		</div>
	</p>

	<?php

}

/**********************************
 * ADMIN COLUMNS
 **********************************/

/**
 * Add/remove event list columns
 */

function risen_event_columns( $columns ) {

	// insert media types, speakers, categories after title
	$insert_array = array(
		'risen_event_dates' => _x( 'When', 'events admin column', 'risen' ),
		'risen_event_venue' => _x( 'Where', 'events admin column', 'risen' )
	);
	$columns = risen_array_merge_after_key( $columns, $insert_array, 'title' );

	// remove author
	unset( $columns['author'] );
	
	return $columns;

}

/**
 * Add content to new columns
 */

function risen_event_columns_content( $column ) {

	global $post;
	
	switch ( $column ) {

		// Dates
		case 'risen_event_dates' :
		
			$dates = array();
		
			$start_date = trim( get_post_meta( $post->ID , '_risen_event_start_date' , true ) );
			if ( ! empty( $start_date ) ) {
				$dates[] = date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ); // translated date
			}
			
			$end_date = get_post_meta( $post->ID , '_risen_event_end_date' , true );
			if ( ! empty( $end_date ) ) {
				$dates[] = date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ); // translated date
			}
			
			echo implode( _x( ' &ndash; ', 'date range separator', 'risen' ), $dates );
			
			$time = get_post_meta( $post->ID , '_risen_event_time' , true );
			if ( ! empty( $time ) ) {
				echo '<div class="description"><i>' . $time . '</i></div>';
			}

			break;

		// Venue
		case 'risen_event_venue' :
		
			echo get_post_meta( $post->ID , '_risen_event_venue' , true );
		
			break;
			
	}

}

/**
 * Enable sorting for new columns
 */

function risen_event_columns_sorting( $columns ) {

	$columns['risen_event_dates'] = '_risen_event_start_date';
	$columns['risen_event_venue'] = '_risen_event_venue';

	return $columns;

}

/**
 * Set how to sort columns (default sorting, custom fields)
 */
 
function risen_event_columns_sorting_request( $args ) {

	// admin area only
	if ( is_admin() ) {
	
		$screen = get_current_screen();

		// only on this post type's list
		if ( 'risen_event' == $screen->post_type && 'edit' == $screen->base ) {

			// orderby has been set, tell how to order
			if ( isset( $args['orderby'] ) ) {

				switch ( $args['orderby'] ) {

					// Start Date
					case '_risen_event_start_date' :

						$args['meta_key'] = '_risen_event_start_date';
						$args['orderby'] = 'meta_value'; // alphabetically (meta_value_num for numeric)
						
						break;

					// Venue
					case '_risen_event_venue' :

						$args['meta_key'] = '_risen_event_venue';
						$args['orderby'] = 'meta_value'; // alphabetically (meta_value_num for numeric)
						
						break;
						
				}
				
			}
			
			// orderby not set, tell which column to sort by default
			else {

				$args['meta_key'] = '_risen_event_start_date';
				$args['orderby'] = 'meta_value'; // alphabetically (meta_value_num for numeric)
				$args['order'] = 'DESC';

			}
			
		}
		
	}
 
	return $args;

}

/**********************************
 * CONTENT OUTPUT
 **********************************/

/*
 * Output featured image for single post
 * If post has its own featured image, use that
 * If no featured image, use featured image from page using Upcoming or Past Events template
 */
 
if ( ! function_exists( 'risen_events_header_image' ) ) {

	function risen_events_header_image( $return = false ) {
		
		global $post;

		// Has its own featured image
		if ( ! empty( $post->ID ) && has_post_thumbnail() ) {
			$use_post_id = $post->ID;
		}
		
		// Use image from Upcoming or Past Events template if Theme Options allows
		else if ( risen_option( 'events_header_image_single' ) ) {
		
			// Use Upcoming or Past template?
			$template = risen_event_parent_page_template( $post ); // determines template based on event's end date being in past or not
		
			// get (newest) page using Events template
			$page = risen_get_page_by_template( $template );
			
			// show featured image from that page
			if ( ! empty( $page->ID ) ) {
				$use_post_id = $page->ID;
			}
		
		}

		// Return or output image HTML
		if ( ! empty( $use_post_id ) ) {
		
			$thumbnail = get_the_post_thumbnail( $use_post_id, 'risen-header', array ( 'class' => 'page-header-image', 'title' => '' ) );
			
			if ( $return ) {
				return $thumbnail;
			} else {
				echo $thumbnail;
			}

		}
		
	}
	
}

/**********************************
 * DATA
 **********************************/

/*
 * Return Upcoming or Past Events page template based on single event's dates
 */
 
if ( ! function_exists( 'risen_event_parent_page_template' ) ) {

	function risen_event_parent_page_template( $post ) {
	
		$template = 'tpl-events.php'; // Upcoming by default
		
		$end_date = get_post_meta( $post->ID , '_risen_event_end_date' , true ); // get end date (could be start date)
		$date_today = date_i18n( 'Y-m-d' ); // today, localized
		
		if ( ! empty( $end_date ) && $end_date < $date_today ) { // event is in past - end date is before today
			$template = 'tpl-events-past.php';
		}
	
		return $template;
	
	}
	
}
 
/*
 * Make sure old event data is proper for latest theme version
 */

if ( ! function_exists( 'risen_events_prepare_data' ) ) {

	function risen_events_prepare_data( $force = false ) {

		// Version 1.1.0 Event Data Changes
		// Set _risen_event_end_date for all events, if not done already
		if ( ! empty( $force ) || get_option( 'risen_events_end_dates_set' ) !== '1' ) { // don't run if already did it, unless forcing a run (theme activation)

			// Select all events to check/update
			$posts = get_posts( array(
				'post_type'			=> 'risen_event',
				'post_status'		=> 'publish,pending,draft,auto-draft,future,private,inherit,trash', // all to be safe
				'numberposts'		=> -1 // no limit
			) );
	
			// Loop to set _risen_event_end_date on those without it
			foreach( $posts as $post ) {

				// Get dates
				$end_date = get_post_meta( $post->ID , '_risen_event_end_date' , true );
				$start_date = get_post_meta( $post->ID , '_risen_event_start_date' , true );

				// No end date
				if ( empty ( $end_date ) ) {
					
					// If no end date, use start date as end date
					$new_end_date = $start_date;
					
					// Save the value
					update_post_meta( $post->ID, '_risen_event_end_date', $new_end_date );

				}

			}	

			// Flag this task as done so it is not repeated
			update_option( 'risen_events_end_dates_set', '1' );

		}
		
		// In future if need more data adjustments can do here
		
	}
	
}
	
/**********************************
 * WIDGETS
 **********************************/

/**
 * Upcoming Events
 */

if ( ! class_exists( 'Risen_Events_Widget' ) ) {

	class Risen_Events_Widget extends WP_Widget {

		// Register widget with WordPress
		public function __construct() {
		
			parent::__construct(
				'risen-events',
				_x( 'Events', 'events widget', 'risen' ),
				array(
					'description' => _x( 'Shows upcoming or past events.', 'events widget', 'risen' )
				)			
			);

		}

		// Back-end widget form
		public function form( $instance ) {

			// Set defaults
			$instance = wp_parse_args( (array) $instance, array(
				'title' => _x( 'Events', 'events widget', 'risen' ),
				'limit' => '5', // also change in update(),
				'timeframe' => 'upcoming',
				'date' => '1',
				'time' => '',
				'excerpt' => '',
				'image' => ''
			) );

			?>
			
			<?php $field = 'title'; ?>
			<p>
				<label for="<?php echo $this->get_field_id( $field ); ?>"><?php _e( 'Title:', 'risen' ); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id( $field ); ?>" name="<?php echo $this->get_field_name( $field ); ?>" type="text" value="<?php echo esc_attr( $instance[$field] ); ?>" />
			</p>
			
			<?php $field = 'limit'; ?>
			<p>
				<label for="<?php echo $this->get_field_id( $field ); ?>"><?php _e( 'Number of items to show:', 'risen' ); ?></label> 
				<input style="width:40px;" id="<?php echo $this->get_field_id( $field ); ?>" name="<?php echo $this->get_field_name( $field ); ?>" type="text" value="<?php echo esc_attr( $instance[$field] ); ?>" />
			</p>
			
			<?php $field = 'timeframe'; ?>
			<p>
				<input id="<?php echo $this->get_field_id( $field ); ?>_upcoming" name="<?php echo $this->get_field_name( $field ); ?>" type="radio" value="upcoming"<?php if ( 'upcoming' == $instance[$field] ) : ?> checked="checked"<?php endif; ?> /> <label for="<?php echo $this->get_field_id( $field ); ?>_upcoming"><?php _ex( 'Upcoming', 'events widget', 'risen' ); ?></label>
				&nbsp;<input id="<?php echo $this->get_field_id( $field ); ?>_past" name="<?php echo $this->get_field_name( $field ); ?>" type="radio" value="past"<?php if ( 'past' == $instance[$field] ) : ?> checked="checked"<?php endif; ?> /> <label for="<?php echo $this->get_field_id( $field ); ?>_past"><?php _ex( 'Past', 'events widget', 'risen' ); ?></label>
			</p>
			
			<p>
				
				<?php $field = 'date'; ?>
				<label for="<?php echo $this->get_field_id( $field ); ?>">
					<input type="checkbox" value="1" id="<?php echo $this->get_field_id( $field ); ?>" name="<?php echo $this->get_field_name( $field ); ?>"<?php if ( ! empty( $instance[$field] ) ) : ?> checked="checked"<?php endif; ?> />
					<?php _e( 'Show date', 'risen' ); ?>
				</label>
				
				<br />
				
				<?php $field = 'time'; ?>
				<label for="<?php echo $this->get_field_id( $field ); ?>">
					<input type="checkbox" value="1" id="<?php echo $this->get_field_id( $field ); ?>" name="<?php echo $this->get_field_name( $field ); ?>"<?php if ( ! empty( $instance[$field] ) ) : ?> checked="checked"<?php endif; ?> />
					<?php _e( 'Show time', 'risen' ); ?>
				</label>
				
				<br />
			
				<?php $field = 'excerpt'; ?>
				<label for="<?php echo $this->get_field_id( $field ); ?>">
					<input type="checkbox" value="1" id="<?php echo $this->get_field_id( $field ); ?>" name="<?php echo $this->get_field_name( $field ); ?>"<?php if ( ! empty( $instance[$field] ) ) : ?> checked="checked"<?php endif; ?> />
					<?php _e( 'Show excerpt', 'risen' ); ?>
				</label>
				
				<br />
				
				<?php $field = 'image'; ?>
				<label for="<?php echo $this->get_field_id( $field ); ?>">
					<input type="checkbox" value="1" id="<?php echo $this->get_field_id( $field ); ?>" name="<?php echo $this->get_field_name( $field ); ?>"<?php if ( ! empty( $instance[$field] ) ) : ?> checked="checked"<?php endif; ?> />
					<?php _e( 'Show image (homepage only)', 'risen' ); ?>
				</label>
				
			</p>
			
			<?php
			
		}

		// Sanitize widget form values as they are saved
		public function update( $new_instance, $old_instance ) {

			$instance = array();
			
			$instance['title'] = trim( strip_tags( $new_instance['title'] ) );
			$instance['limit'] = isset( $new_instance['limit'] ) && (int) $new_instance['limit'] > 0 ? (int) $new_instance['limit'] : 5; // default if not positive number
			$instance['timeframe'] = in_array( $new_instance['timeframe'], array( 'upcoming', 'past' ) ) ? $new_instance['timeframe'] : 'upcoming';
			$instance['date'] = ! empty( $new_instance['date'] ) ? '1' : '';
			$instance['time'] = ! empty( $new_instance['time'] ) ? '1' : '';
			$instance['excerpt'] = ! empty( $new_instance['excerpt'] ) ? '1' : '';
			$instance['image'] = ! empty( $new_instance['image'] ) ? '1' : '';

			return $instance;
			
		}
		
		// Front-end display of widget
		public function widget( $args, $instance ) {
		
			global $post;

			// HTML Before
			echo $args['before_widget'];
			
			// Title
			$title = apply_filters( 'widget_title', $instance['title'] );
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			
			// Default timeframe (older versions of Risen 1.1.0 and earlier did not save this value
			$instance['timeframe'] = ! empty( $instance['timeframe'] ) ? $instance['timeframe'] : 'upcoming';
			
			// Show upcoming events
			$compare = '>=';  // all events with start OR end date today or later
			$meta_key = '_risen_event_start_date'; // order by this; want earliest starting date first
			$order = 'ASC'; // sort from soonest to latest
			
			// Show past events
			if ( 'past' == $instance['timeframe'] ) {
				$compare = '<'; // all events with start AND end date BEFORE today
				$meta_key = '_risen_event_end_date'; // order by this; want finish date first
				$order = 'DESC'; // sort from most recently past to oldest
			}

			// Get events
			risen_events_prepare_data(); // make sure event data is ready for current theme version
			$posts = get_posts( array(
				'post_type'			=> 'risen_event',
				'numberposts'		=> $instance['limit'],
				'meta_query' => array(
					array(
						'key' => '_risen_event_end_date', // the latest date that the event goes to (could be start date)
						'value' => date_i18n( 'Y-m-d' ), // today's date, localized
						'compare' => $compare,
						'type' => 'DATE'
					),
				),
				'meta_key' 			=> $meta_key,
				'orderby'			=> 'meta_value',
				'order'				=> $order
			) );
			
			// Loop Posts
			$i = 0;
			foreach( $posts as $post ) :

				// count so we can identiy first and tell if any were found
				$i++;
			
				// get date range
				$start_date = get_post_meta( $post->ID , '_risen_event_start_date' , true );
				$end_date = get_post_meta( $post->ID , '_risen_event_end_date' , true );
				
				// make the_title() , the_permalink() and so on work
				setup_postdata( $post );

			?>
			
			<article class="events-widget-item<?php if ( 1 == $i ) : ?> events-widget-item-first<?php endif; ?>">
			
				<?php if ( is_home() && ! empty( $instance['image'] ) && has_post_thumbnail() ) : ?>
				<div class="image-frame events-widget-item-thumb widget-thumb">
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'risen-tiny-thumb', array( 'title' => '' ) ); ?></a>
				</div>
				<?php endif; ?>
				
				<header>

					<h1 class="events-widget-item-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>

					<?php							
					$date_format = get_option( 'date_format' );
					if ( $start_date && ! empty( $instance['date'] ) ) :
					?>
					<div class="events-widget-item-date">
						<?php if ( $end_date != $start_date ) : // date range ?>
						<?php
						printf( __( '%s &ndash; %s', 'risen' ),
							date_i18n( $date_format, strtotime( $start_date ) ),
							date_i18n( $date_format, strtotime( $end_date ) )
						);
						?>
						<?php else : // start date only ?>
						<?php echo date_i18n( $date_format, strtotime( $start_date ) ); ?>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					
					<?php							
					$time = get_post_meta( $post->ID , '_risen_event_time' , true );
					if ( $time && ! empty( $instance['time'] ) ) :
					?>
					<div class="events-widget-item-time"><?php echo $time; ?></div>
					<?php endif; ?>
					
				</header>
				
				<?php if ( get_the_excerpt() && ! empty( $instance['excerpt'] ) ): ?>
				<div class="events-widget-item-excerpt">
					<?php the_excerpt(); ?>
				</div>
				<?php endif; ?>
				
				<div class="clear"></div>
				
			</article>
			
			<?php
			
			// End Loop
			endforeach;
			
			// No items found
			if ( empty( $i ) ) {
				_e( 'There are no events to show.', 'risen' );
			}
			
			// HTML After
			echo $args['after_widget'];

		}

	}
	
}