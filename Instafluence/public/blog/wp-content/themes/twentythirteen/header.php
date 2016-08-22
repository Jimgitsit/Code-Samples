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
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8">
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
	<link rel="stylesheet" href="../../../css/blog.css">
	<style>
		a,p,h1,h2,h3,h4,h5,h6 {-webkit-font-smoothing:antialiased;}
		#top-header{background:#5dc7d6;color:white;padding:20px 0;position:fixed;top:0px;width:100%;z-index:11; font-family:"adelle-sans";left:0;}
		#top-header .container,#bottom-footer .container {max-width:1000px;margin:0 auto;padding:0 15px;}
		#top-header img{float:left;}
		#top-header ul{float:right;position:relative;top:5px;margin:0;padding:0;list-style:none;}
		#top-header ul li{float:left;margin-right:98px;padding:0px;}
		#top-header ul li a {color:#FFF;text-decoration:none;}
		#top-header button{width:120px;height:30px;position:relative;top:2px;}
		#top-header p{position:relative;top:5px;margin:0;font-size:18px;font-weight:600;}
		#top-header .white-outline-btn {border-radius:3px;border:2px solid white;color:white;background:none;padding:0;}
		#page {margin-top:80px;}

		#bottom-footer{background:#252525;padding:60px 0;color:white;}
		#bottom-footer img{float:left;}
		#bottom-footer ul{float:right;position:relative;top:14px;padding:0;margin:0;list-style:none;}
		#bottom-footer ul li{float:left;margin-right:40px;}
		#bottom-footer ul li a{text-decoration:none;color:#FFF;}
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
		<!-- <div class="wp-header">
			<div class="wp-container">
				<div class="wp-logo">
					<a href="/"><img src="http://instafluence.com/img/logo.png" alt="Instafluence" /></a>
				</div>
				<ul class="wp-nav">
					<li><a href="/">Home</a></li>
				</ul>
			</div>
		</div> -->
		<header id="top-header">
			<div class="container">
				<a href="/"><img src="http://instafluence.com/img/logo.png" alt="instafluence"/></a>
				<ul>
					<li>
						<a href="http://instafluence.com/about-instafluence"><p>About Us</p></a>
					</li>
					<li>
						<a href="http://instafluence.com/portfolio"><p>Portfolio</p></a>
					</li>
					<!-- <li class="outlined">
						<a href="#" data-toggle="modal" data-target="#myModal"><button class="white-outline-btn">Let's Talk</button></a>
					</li> -->
				</ul>
			</div>
		</header>



		<div id="main" class="site-main">
