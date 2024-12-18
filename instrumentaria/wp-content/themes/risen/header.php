<?php
/**
 * Theme Header
 *
 * Outputs <head> and header content (logo, tagline, navigation)
 */

?><!DOCTYPE html>
<!--[if IE 8 ]><html class="ie ie8 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<!--[if lte IE 8]><meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=IE8" /><![endif]-->
<title><?php wp_title( '' ); // wp_title is filtered by includes/customizations.php risen_title() ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php wp_head(); // prints out JavaScript, CSS, etc. as needed by WordPress, theme, plugins, etc. ?>
</head>

<body <?php body_class(); ?>>

	<!-- Container Start -->
	
	<div id="container">
		
		<div id="container-inner">
		
			<!-- Header Start -->
				
			<header id="header">

				<div id="header-inner">
					
					<div id="header-content">
				
						<div id="logo"<?php if ( risen_option( 'logo_no_left_padding') ) : ?> class="logo-no-left-padding"<?php endif; ?>>	
							<a href="<?php echo esc_url( home_url() ); ?>"><img src="<?php echo esc_url( risen_logo_url() ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></a>
						</div>

						<div id="top-right">

							<div id="top-right-inner">

								<div id="top-right-content">

									<div id="tagline">
										<?php bloginfo( 'description' ); ?>
									</div>

								</div>

							</div>

						</div>

					</div>

				</div>
				
				<!-- Menu Start -->
				
				<nav id="header-menu">

					<div id="header-menu-inner">

						<?php
						wp_nav_menu( array(
							'theme_location'	=> 'header',
							'menu_id'			=> 'header-menu-links',
							'menu_class'		=> 'sf-menu',
							'container'			=> false, // don't wrap in div
							'fallback_cb'		=> false // don't show pages if no menu found - show nothing
						) );
						?>
						
						<?php risen_icons( 'header', 'light-icon' ); ?>
						
						<div class="clear"></div>
						
					</div>
				
					<div id="header-menu-bottom"></div>
				
				</nav>
				
				<!-- Menu End -->
					
			</header>
			
			<!-- Header End -->
