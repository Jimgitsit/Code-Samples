<header class="admin-header">
	<div class="container">
		<a href="/">
			<img src="/img/logo.png" alt="instafluence"/>
		</a>
		<ul>
			<li {if $activeMenuItem == 'influencers'}class="active"{/if}>
				<a href="/influencers/">
					<p>Influencers</p>
				</a>
			</li>
			
			<li {if $activeMenuItem == 'influencer_edit'}class="active"{/if}>
				<a href="/admin/logout/">
					<p>Logout</p>
				</a>
			</li>

			
			<!-- 
			<li>
				<a href="/about-instafluence/"><p>About Us</p></a>
			</li>
			<li class="outlined">
				<a href="/portfolio/"><p>Portfolio</p></a>
			</li>
			<li>
				<a href="#" data-toggle="modal" data-target="#myModal"><button class="white-outline-btn">Let's Talk</button></a>
			</li>
			 -->
		</ul>
	</div>
</header>