<!DOCTYPE html>
<html>
<head>

	<title>Instafluence | {$pageTitle}</title>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
	<meta name="description" content="The most effective form of Instagram and Vine marketing. We connect brands with influencers for tasteful, genuine ads using social media platforms like Instagram, Facebook, Twitter, Vine and YouTube.">

	<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/css/bootstrapValidator.min.css"/>
	<link rel="stylesheet" type="text/css" href="/css/reset.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">

	<script type="text/javascript" src="//use.typekit.net/aiq0qnk.js"></script>
	{literal}<script type="text/javascript">try{Typekit.load();}catch(e){}</script>{/literal}

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

</head>
<body>

<nav class="navbar navbar-default {$pageTitle}" role="navigation" style="background: {if $pageTitle == 'Influencer'}#0ec5a4{elseif $pageTitle == 'App'}#ff5d5f{elseif $pageTitle == 'Brand'}#5dc7d6{/if}">
	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/"><img src="/img/logo.png" alt="instafluence"/></a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav navbar-right">
				<li>
					<a href="/about-instafluence/"><p>About Us</p></a>
				</li>
				<li>
					<a href="/portfolio/"><p>Portfolio</p></a>
				</li>
				<li>
					<a href="/blog/"><p>Blog</p></a>
				</li>
				<li>
					<a href="#" class="button" data-toggle="modal" data-target="#myModal"><button class="white-outline-btn">Let's Talk</button></a>
				</li>
				<li>
					{if isset($auth)}
						<a href="/login/" class="button"><button class="white-outline-btn">Login</button></a>
					{else if}
						<!--<a href="#" data-toggle="modal" data-target="#modalLogin"><button class="white-outline-btn">Login</button></a>-->
						<a href="/login/" class="button"><button class="white-outline-btn">Login</button></a>
					{/if}
				</li>
			</ul>
		</div>
	</div>
</nav>
