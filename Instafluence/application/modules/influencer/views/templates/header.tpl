<!DOCTYPE html>
<html>
<head>
<!-- 	<title>Instagram Marketing | Market on Instagram | Instafluence</title> -->
	<title>Instafluence | {$pageTitle}</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
	<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8">
	<meta name="description" content="The most effective form of Instagram and Vine marketing. We connect brands with influencers for tasteful, genuine ads using social media platforms like Instagram, Facebook, Twitter, Vine and YouTube.">

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

	<!-- fonts, favicon, and touch icons -->
	<link rel="shortcut icon" href="/img/favicon.png">
	<script type="text/javascript" src="//use.typekit.net/aiq0qnk.js"></script>
	{literal}
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
	{/literal}

	<!-- scripts -->
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="/js/bootstrap.min.js"></script>

	<!-- styles -->
	<link rel="stylesheet" href="/css/vendors/bootstrap.min.css">
	<link rel="stylesheet" href="/css/reset.css">
	<link rel="stylesheet" href="/css/style.css">
	<!-- <link rel="stylesheet" href="/css/responsive.css"> -->
	<link rel="stylesheet" href="/lib/select2/select2.css">

</head>
<body>

<nav class="navbar navbar-default navbar-loggedin {$pageTitle}" role="navigation">
	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<a class="navbar-brand" href="/"><span class="icon icon-logo"></span></a>
			<p class="nav-page-title">{$pageTitle}</p>
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav navbar-right">
				<li>
					<a {if $activeMenuItem == 'account'}class="active"{/if} href="/account/"><p>Account</p></a>
				</li>
				<li>
					<a {if $activeMenuItem == 'profile'}class="active"{/if} href="/profile/"><p>Profile</p></a>
				</li>
				<li>
					<a href="/influencer/logout/"><p>Logout</p></a>
				</li>
			</ul>
		</div>

	</div>
</nav>

<!-- <nav class="navbar navbar-default {$pageTitle}" role="navigation">
	<div class="container">
		Brand and toggle get grouped for better mobile display
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/"><img src="/img/logo.png" alt="instafluence"/></a>
		</div>

		Collect the nav links, forms, and other content for toggling
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav navbar-right">
				<li>
					<a {if $activeMenuItem == 'account'}class="active"{/if} href="/account/"><p>Account</p></a>
				</li>
				<li>
					<a {if $activeMenuItem == 'profile'}class="active"{/if} href="/profile/"><p>Profile</p></a>
				</li>
				<li>
					<a href="/influencer/logout/"><p>Logout</p></a>
				</li>
			</ul>
		</div>

	</div>
</nav> -->

<!-- <section id="messaging">
	<div class="msg-wrap">
		<div class="msg-box">
			<div class="msg-content">This is a test.</div>
		</div>
	</div>
</section> -->



<!--
	<header>
		<div class="container">
			<a href="/">
				<img src="/img/logo.png" alt="instafluence"/>
			</a>
			<ul>
				<!--
				<li>
					<img src="http://www.gravatar.com/avatar/{$avatar}?size=80&d={$default_avatar}" class="avatar" />
				</li>
				--
				<li {if $activeMenuItem == 'profile'}class="active"{/if}>
					<a href="/profile/"><p>Profile</p></a>
				</li>

				<li {if $activeMenuItem == 'account'}class="active"{/if}>
					<a href="/account/"><p>Account</p></a>
				</li>

				<li>
					<a href="/influencer/logout/"><p>Logout</p></a>
				</li>
			</ul>
		</div>
	</header>
-->
