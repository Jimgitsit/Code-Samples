<?php
/**
 * The Header template for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script>
	<![endif]-->
	<?php wp_head(); ?>
	<link rel="shortcut icon" href="../../../img/favicon.png">
	<script type="text/javascript" src="//use.typekit.net/aiq0qnk.js"></script>
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
	<link rel="stylesheet" href="../../../css/style.css">
	<style>
		header { background:none; }
		.wp-logo { padding:20px 0 0 20px; float: left; }
		.entry-meta { color:black; }
		.wp-header { width: 100%; height: 90px; background: #fff; -webkit-box-shadow: 0 1px 2px rgba(32,37,35,.2); -moz-box-shadow: 0 1px 2px rgba(32,37,35,.2); box-shadow: 0 1px 2px rgba(32,37,35,.2) }
		.wp-container { max-width: 960px; margin: 0 auto; }
		.wp-nav { float: right; list-style-type: none; padding: 16px 20px 0 0; }
		.wp-logo img { width: 250px; height: auto; }
		footer, #respond { display: none; visibility: hidden; }
		.entry-header { height: auto; }
	</style>
</head>

<body <?php body_class(); ?>>
	<div id="page" class="hfeed site">
		<!-- Commenting out Wordpress default header -->
		<!--
		<header id="masthead" class="site-header" role="banner">
			<a class="home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
				<h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
			</a>

			<div id="navbar" class="navbar">
				<nav id="site-navigation" class="navigation main-navigation" role="navigation">
					<h3 class="menu-toggle"><?php _e( 'Menu', 'twentythirteen' ); ?></h3>
					<a class="screen-reader-text skip-link" href="#content" title="<?php esc_attr_e( 'Skip to content', 'twentythirteen' ); ?>"><?php _e( 'Skip to content', 'twentythirteen' ); ?></a>
					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
					<?php get_search_form(); ?>
				</nav>
			</div>
		</header>
		-->

		<!-- Our new header -->
		<div class="wp-header">
			<div class="wp-container">
				<div class="wp-logo">
					<a href="/"><img src="http://instafluence.com/img/logo.png" alt="Instafluence" /></a>
				</div>
				<ul class="wp-nav">
					<li><a href="/">Home</a></li>
				</ul>
			</div>
		</div>



		<div id="main" class="site-main">
