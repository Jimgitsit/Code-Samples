<section id="userHeader">
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-3">
				{if isset($profilePic) && $profilePic != ''}
					<img class="profile-img" src="{$profilePic}">
				{else}
					<img class="profile-img" src="/img/profile_placeholder.png">
				{/if}
			</div>
			<div class="col-xs-3">
				<div class="info-block {if $estTotalReach > 0}info-block-with-sub{/if}">
					<div class="value">{$totalReach|number_format:0:".":","}</div>
					<div class="description">Total Reach</div>
					{if $estTotalReach > 0}
						<div class="sub">
							<div class="sub-left pull-left">
								<div class="value">{$actualTotalReach|number_format:0:".":","}</div>
								<div class="description">Actual</div>
							</div>
							<div class="sub-right pull-right">
								<div class="value">{$estTotalReach|number_format:0:".":","}</div>
								<div class="description">Esitimated</div>
							</div>
						</div>
					{/if}
				</div>
			</div>
			<div class="col-xs-3">
				<div class="info-block">
					{if isset($socialHistory.growth_rate_week_avg_percent)}
						<div class="value">
							{if $socialHistory.growth_rate_week_avg_percent > 0}
								<img class="growth-arrow" src="/img/growth-arrow-up.png">
							{elseif $socialHistory.growth_rate_week_avg_percent < 0}
								<img class="growth-arrow" src="/img/growth-arrow-down.png">
							{/if}
							{math equation="abs(x)" x=$socialHistory.growth_rate_week_avg_percent|number_format:0}%
						</div>
						<div class="description">Avg 7 Day<br/>Growth</div>
					{elseif isset($socialHistory.growth_rate_month_avg_percent)}
						<div class="value">
							{if $socialHistory.growth_rate_month_avg_percent > 0}
								<img class="growth-arrow" src="/img/growth-arrow-up.png">
							{elseif $socialHistory.growth_rate_month_avg_percent < 0}
								<img class="growth-arrow" src="/img/growth-arrow-down.png">
							{/if}
							{math equation="abs(x)" x=$socialHistory.growth_rate_month_avg_percent|number_format:0}%
						</div>
						<div class="description">Avg 30 Day<br/>Growth</div>
					{else}
						<div class="value">?</div>
						<div class="description">Avg Growth</div>
					{/if}
				</div>
			</div>
			<div class="col-xs-3">
				<div class="info-block">
					<div class="value">?</div>
					<div class="description">Overall</br>Engagement</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 col-span-12">
				<div id="nameWrap">
					<h2>{$user.first_name} {$user.last_name}</h2>
					<img id="deleteInfluencerBtn" class="tool-tip" src="/img/x-mark-16.png" data-toggle="tooltip" data-placement="top" title="Delete this Influencer">
				</div>
				<input data-user="{$user.email}" value="{if isset($profile.rating)}{$profile.rating}{else}0{/if}" type="number" class="rating" data-max="5" data-min="1" data-empty-value="0" />
				<div id="emailWrap">
					<a id="email" target="_blank" href="mailto:{$profile.email}">{$profile.email}</a>
					<img id="emailEditBtn" class="tool-tip" src="/img/edit-icon.png" data-toggle="tooltip" data-placement="top" title="Edit email">
				</div>
				<div id="emailEditWrap">
					<input id="emailEdit" value=""/>
					<div id="emailEditSaveBtn" class="pull-right">save</div>
					<div id="emailEditCancelBtn" class="pull-right">cancel</div>
				</div>
				<input id="influencerTags" placeholder="click here to add tags" value="{if isset($profile.tags_str)}{$profile.tags_str}{/if}"/>
				<input type="hidden" id="allTags" value="{$allTags}"/>
			</div>
		</div>
	</div>
</section>