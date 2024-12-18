<?php
/**
 * Google Maps Helper Functions
 */

/**********************************
 * DATA
 **********************************/
 
/**
 * Zoom Levels Array
 */

if ( ! function_exists( 'risen_gmaps_zoom_levels' ) ) {

	function risen_gmaps_zoom_levels() {

		$zoom_levels = array();
		
		$zoom_min = 1; // 0 is actually lowest but then it's detected as not set and reverts to default
		$zoom_max = 21;
		
		for ( $z = $zoom_min; $z <= $zoom_max; $z++ ) {
			$zoom_levels[$z] = $z;
		}
		
		return $zoom_levels;
	
	}
	
}

/**
 * Map Types Array
 */

if ( ! function_exists( 'risen_gmaps_types' ) ) {

	function risen_gmaps_types() {

		$types = array(
			'ROADMAP'	=> _x( 'Road', 'map', 'risen' ),
			'SATELLITE'	=> _x( 'Satellite', 'map', 'risen' ),
			'HYBRID'	=> _x( 'Hybrid', 'map', 'risen' ),
			'TERRAIN'	=> _x( 'Terrain', 'map', 'risen' )
		);
		
		return apply_filters( 'risen_gmaps_types', $types );
	
	}
	
}

/**********************************
 * CONTENT OUTPUT
 **********************************/

/**
 * Zoom Level Select Options
 */

if ( ! function_exists( 'risen_gmaps_zoom_level_options' ) ) {

	function risen_gmaps_zoom_level_options( $selected = false ) {

		$zoom_levels = risen_gmaps_zoom_levels();
		
		$options = '';
		
		foreach( $zoom_levels as $zoom_level ) {
			$options .= '<option value="' . $zoom_level . '"' . ( $zoom_level == $selected ? ' selected="selected"' : '' ) . '>' . $zoom_level . '</option>';
		}
		
		return $options;
	
	}
	
}

/**
 * Zoom Level Select Options
 */

if ( ! function_exists( 'risen_gmaps_type_options' ) ) {

	function risen_gmaps_type_options( $selected = false ) {

		$types = risen_gmaps_types();
		
		$options = '';
		
		foreach( $types as $type_key => $type_name ) {
			$options .= '<option value="' . $type_key . '"' . ( $type_key == $selected ? ' selected="selected"' : '' ) . '>' . $type_name . '</option>';
		}
		
		return $options;
	
	}
	
}
 
/**
 * Display Google Map
 * Also available as shortcode
 * Only latitude and longitude are required
 */

if ( ! function_exists( 'risen_google_map' ) ) {

	function risen_google_map( $options = false ) {

		if ( ! empty( $options['latitude'] ) && ! empty( $options['longitude'] ) ) {

			// Type and zoom are optional
			$options['type'] = isset( $options['type'] ) ? strtoupper( $options['type'] ) : '';
			$options['zoom'] = isset( $options['zoom'] ) ? (int) $options['zoom'] : '';
	
			// Height percentage of width?
			$map_style = '';
			if ( ! empty( $options['height_percent'] ) ) {
				$options['height_percent'] = str_replace( '%', '', $options['height_percent'] );
				$map_style = ' style="padding-bottom: ' . $options['height_percent'] . '%;"';
			}
			
			// No border?
			$container_style = '';
			if ( ! empty( $options['border'] ) && 'no' == $options['border'] ) {
				$container_style = ' style="padding: 0px; border: 0px"';
			}			
	
			// Unique ID for this map so can have multiple maps on a page
			$google_map_id = 'google-map-' . rand( 1000000, 9999999 );
	
return <<< HTML
<div class="google-map-container"$container_style>
	<div id="$google_map_id" class="google-map"$map_style></div>
	<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function($) {
		var map = initMap('$google_map_id', '{$options['latitude']}', '{$options['longitude']}', '{$options['type']}', '{$options['zoom']}');
	});
	/* ]]> */
	</script>
</div>
HTML;

		} else if ( ! empty( $options['show_error'] ) ) {
			return __( '<p><b>Google Map Error:</b> <i>latitude</i> and <i>longitude</i> attributes are required. See documentation for help.</p>', 'risen' );
		}
	
	}
	
}
