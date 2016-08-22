<section id="userDetailNav">
	<ul class="nav nav-tabs user-detail-tabs" role="tablist" id="userDetailTabs">
		<li class="active user-detail-tab"><a href="#analytics" role="tab" data-toggle="tab">Analytics</a></li>
		<li class="user-detail-tab"><a href="#profile" role="tab" data-toggle="tab">Profile</a></li>
		<li class="user-detail-tab"><a href="#notes" role="tab" data-toggle="tab">Notes</a></li>
		<li class="user-detail-tab"><a href="#messages" role="tab" data-toggle="tab">Messages</a></li>
		<li class="user-detail-tab"><a href="#campaigns" role="tab" data-toggle="tab">Campaigns</a></li>
		<li class="user-detail-tab"><a href="#contracts" role="tab" data-toggle="tab">Contracts</a></li>
	</ul>
	
	<div class="tabs-bottom"></div>
	
	<div class="tab-content">
		<div class="tab-pane fade in active" id="analytics">
			{include file='templates/analytics.tpl'}
		</div>
		<div class="tab-pane fade" id="profile">
			{include file='templates/profile.tpl'}
		</div>
		<div class="tab-pane fade" id="notes">
			{include file='templates/notes.tpl'}
		</div>
		<div class="tab-pane fade" id="messages">
			<p class="coming-soon text-center">COMING SOON!</p>
		</div>
		<div class="tab-pane fade" id="campaigns">
			<p class="coming-soon text-center">COMING SOON!</p>
		</div>
		<div class="tab-pane fade" id="contracts">
			<p class="coming-soon text-center">COMING SOON!</p>
		</div>
	</div>
</section>