<section id="analytics">
	<div class="container-fluid">
		<br/>
		<h3 class="text-center bold">User Connected Networks</h3>
		{if $socialConnectedCount > 0}
			<table class="table" id="socialTable">
				{foreach $social as $name => $accounts}
					{$backColor = "{$name}-color-lt"}
					{foreach $accounts as $accountIndex => $s}
						{if isset($s.connected) && $s.connected == true}
							<tr class="social-row {$backColor}">
								<td>
									{if isset($s.anchor)}
										<a href="{$s.anchor}" target="_blank"><img class="social-icon" src="/img/social_icons_32x32/{$name}.png"></a>
									{else}
										<img class="social-icon" src="/img/social_icons_32x32/{$name}.png">
									{/if}
								</td>
								<td>
									<span class="social-name">{$name|capitalize}</span>
									{if isset($s.error)}
										<span class="social-error" data-toggle="popover" data-content="{htmlentities($s.error)}" data-placement="bottom">error</span>
									{/if}
									<div class="user-name">{$s.username}</div>
								</td>
								<td>
									<div class="stat-label">Price</div>
									<div class="form-group price-input">
										<div class="input-group">
											<div class="input-group-addon">$</div>
											<input class="price form-control" type="text" maxlength="7" data-followers="{$s.total_reach}" data-type="social" data-network="{$name}" data-account-index="{$accountIndex}" value="{if isset($s.price)}{$s.price}{/if}">
										</div>
									</div>
									<div class="cpm-wrap"><div class="cpm">$3.223</div><div class="sup">cpm</div></div>
								</td>
								<td>
									<div class="stat-label">Followers</div>
									<div class="stat-value">{$s.total_reach|number_format:0:".":","}</div>
								</td>
								<td>
									<div class="stat-label">Growth</div>
									<div class="stat-value">
										{if isset($socialHistory[$name][$accountIndex]['followers']['growth_rate_week_percent'])}
											{if $socialHistory[$name][$accountIndex]['followers']['growth_rate_week_percent']|number_format:0:".":"," > 0}
												<img src="/img/growth-arrow-up.png">
											{elseif $socialHistory[$name][$accountIndex]['followers']['growth_rate_week_percent']|number_format:0:".":"," < 0}
												<img src="/img/growth-arrow-down.png">
											{/if}
											<span>{math equation="abs(x)" x=$socialHistory[$name][$accountIndex]['followers']['growth_rate_week_percent']|number_format:0:".":","}%</span><div class="sup">(1w)</div>
										{elseif isset($socialHistory[$name][$accountIndex]['followers']['growth_rate_month_percent'])}
											{if $socialHistory[$name][$accountIndex]['followers']['growth_rate_month_percent']|number_format:0:".":"," > 0}
												<img src="/img/growth-arrow-up.png">
											{elseif $socialHistory[$name][$accountIndex]['followers']['growth_rate_month_percent']|number_format:0:".":"," < 0}
												<img src="/img/growth-arrow-down.png">
											{/if}
											<span>{math equation="abs(x)" x=$socialHistory[$name][$accountIndex]['followers']['growth_rate_month_percent']|number_format:0:".":","}%</span><span class="sup">(1m)</span>
										{else}
											<span>?</span>
										{/if}
									</div>
								</td>
								<td>
									<div class="stat-label">Engagement</div>
									<div class="stat-value">?</div>
								</td>
								<td>
									{if ($name == 'facebook' && ((isset($s.include) && $s.include == true) || (isset($s.accounts) && count($s.accounts) > 0))) || $name == 'youtube'}
										<img class="expand-arrow" src="/img/expand-left.png" data-network="{$name}" data-account-index="{$accountIndex}" data-status="closed">
									{/if}
								</td>
							</tr>
							
							<!-- Special condition for Facebook, Separate Facebook pages -->
							{if $name == 'facebook'}
								{if isset($s.include) && $s.include == true}
									<!-- User's main Facebook page -->
									<tr class="expanded-row {$backColor}" data-network="{$name}" data-account-index="{$accountIndex}">
										<td>
											<a href="{$s.anchor}" target="_blank"><img class="social-icon" src="{$s.profile.picture.url}"></a>
										</td>
										<td>
											<span class="social-name">{$s.profile.first_name} {$s.profile.last_name}</span>
										</td>
										<td>
											
										</td>
										<td>
											<div class="stat-label">Followers</div>
											<div class="stat-value">{$s.followers|number_format:0:".":","}</div>
										</td>
										<td>
											
										</td>
										<td>
											<div class="stat-label">Engagement</div>
											<div class="stat-value">?</div>
										</td>
										<td>
	
										</td>
									</tr>
								{/if}
								
								{if isset($s.accounts)}
									{foreach $s.accounts as $pageIndex => $page}
										{if isset($page.connected) && $page.connected == true}
											<!-- Facebook pages that the user administers -->
											<tr class="expanded-row {$backColor}" data-network="{$name}" data-account-index="{$accountIndex}">
												<td>
													<a href="{$page.profile.link}" target="_blank"><img class="social-icon" src="{$page.picture}"></a>
												</td>
												<td>
													<span class="social-name">{$page.name}</span>
												</td>
												<td>
													
												</td>
												<td>
													<div class="stat-label">Followers</div>
													<div class="stat-value">{$page.profile.likes}</div>
												</td>
												<td>
													
												</td>
												<td>
													<div class="stat-label">Engagement</div>
													<div class="stat-value">?</div>
												</td>
												<td>
		
												</td>
											</tr>
										{/if}
									{/foreach}
								{/if}
							<!-- Special condition for Youtube, Separate Youtube pages -->
							{elseif $name == 'youtube'}
								
							{/if}
						{/if}
					{/foreach}
				{/foreach}
			</table>
		{else}
			<div class="no-networks-msg text-center">This influencer has not connected any social networks</div>
			<div class="invite-btn-wrap text-center">
				<div class="btn btn-if-default" id="inviteBtn">Send Invite</div>
				<div id="inviteMsg" {if isset($user.invite_sent_date)}style="display: block;"{/if}>{if isset($user.invite_sent_date)}An invite was last sent on {date('m/d/Y g:ia', $user.invite_sent_date->sec)}{/if}</div>
			</div>
		{/if}

		<h3 class="text-center bold" id="publicTitle">Admin Connected Networks</h3>
		{if $socialPublicConnectedCount > 0}
			<table class="table" id="socialTable">
				{foreach $socialPublic as $name => $accounts}
					{$backColor = "{$name}-color-lt"}
					{foreach $accounts as $accountIndex => $s}
						<tr class="social-row {$backColor}">
							<td>
								{if isset($s.anchor)}
									<a href="{$s.anchor}" target="_blank"><img class="social-icon" src="/img/social_icons_32x32/{$name}.png"></a>
								{elseif isset($s.url)}
									<a href="{$s.url}" target="_blank"><img class="social-icon" src="/img/social_icons_32x32/{$name}.png"></a>
								{else}
									<img class="social-icon" src="/img/social_icons_32x32/{$name}.png">
								{/if}
							</td>
							<td>
								<span class="social-name">{$name|capitalize}</span>
								{if isset($s.error) && (!isset($s.show_est) || $s.show_est == false)}
									<span class="social-error" data-toggle="popover" data-content="{htmlentities($s.error)}" data-placement="bottom">error</span>
								{/if}
								<div class="user-name">{$s.username}</div>
							</td>
							<td>
								<div class="stat-label">Price</div>
								<div class="form-group price-input">
									<div class="input-group">
										<div class="input-group-addon">$</div>
										<input class="price form-control" type="text" maxlength="7" data-type="social_public" data-followers="{$s.total_reach}" data-network="{$name}" data-account-index="{$accountIndex}" value="{if isset($s.price) && $s.price > 0}{$s.price}{/if}">
									</div>
								</div>
								<div class="cpm-wrap"><div class="cpm">$3.223</div><div class="sup">cpm</div></div>
							</td>
							{if isset($s.show_est) && $s.show_est == true}
								<td>
									<div class="stat-label">Est Followers</div>
									<input class="est-followers form-control text-center" type="text" maxlength="9" data-type="social_public" data-network="{$name}" data-account-index="{$accountIndex}" value="{if isset($s.est_followers) && $s.est_followers > 0}{$s.est_followers}{/if}">
								</td>
								<td></td>
								<td></td>
							{else}
								<td>
									<div class="stat-label">Followers</div>
									<div class="stat-value">{$s.total_reach|number_format:0:".":","}</div>
								</td>
								<td>
									<div class="stat-label">Growth</div>
									<div class="stat-value">
										{if isset($socialPublicHistory[$name][$accountIndex]['followers']['growth_rate_week_percent'])}
											{if $socialPublicHistory[$name][$accountIndex]['followers']['growth_rate_week_percent'] > 0}
												<img src="/img/growth-arrow-up.png">
											{elseif $socialPublicHistory[$name][$accountIndex]['followers']['growth_rate_week_percent'] < 0}
												<img src="/img/growth-arrow-down.png">
											{/if}
											<span>{$socialPublicHistory[$name][$accountIndex]['followers']['growth_rate_week_percent']}%</span>
										{else}
											<span>?</span>
										{/if}
									</div>
								</td>
								<td>
									<div class="stat-label">Engagement</div>
									<div class="stat-value">?</div>
								</td>
							{/if}
							<td>
								<img class="remove-public-btn" src="/img/x-mark-16.png" data-network="{$name}" data-account-index="{$accountIndex}">
							</td>
						</tr>
	
						{if $name == 'facebook'}
							{if isset($s.include) && $s.include == true}
								<tr class="expanded-row {$backColor}" data-network="{$name}" data-account-index="{$accountIndex}">
									<td>
										<a href="{$s.anchor}" target="_blank"><img class="social-icon" src="{$s.profile.picture.url}"></a>
									</td>
									<td>
										<span class="social-name">{$s.profile.first_name} {$s.profile.last_name}</span>
									</td>
									<td>
										
									</td>
									<td>
										<div class="stat-label">Followers</div>
										<div class="stat-value">{$s.followers|number_format:0:".":","}</div>
									</td>
									<td>
									</td>
									<td>
										<div class="stat-label">Engagement</div>
										<div class="stat-value">?</div>
									</td>
									<td>
	
									</td>
								</tr>
							{/if}

							{if isset($s.accounts)}
								{foreach $s.accounts as $pageIndex => $page}
									{if isset($page.connected) && $page.connected == true}
										<tr class="expanded-row" data-network="{$name}" data-account-index="{$accountIndex}">
											<td>
												<a href="{$page.profile.link}" target="_blank"><img class="social-icon" src="{$page.picture}"></a>
											</td>
											<td>
												<span class="social-name">{$page.name}</span>
											</td>
											<td>
												
											</td>
											<td>
												<div class="stat-label">Followers</div>
												<div class="stat-value">{$page.profile.likes}</div>
											</td>
											<td>
		
											</td>
											<td>
												<div class="stat-label">Engagement</div>
												<div class="stat-value">?</div>
											</td>
											<td>
		
											</td>
										</tr>
									{/if}
								{/foreach}
							{/if}
						{/if}
					{/foreach}
				{/foreach}
			</table>
		{else}
			<div class="no-networks-msg text-center">You have not connected any social networks for this influencer</div>
		{/if}
		
		<div>
			<div class="{if $socialPublicConnectedCount > 0}pull-left{else}text-center{/if}">
				<button class="btn btn-if-default" data-toggle="modal" data-target="#addNetworkModal"><img src="/img/add-icon-16-white.png">Add Network</button>
			</div>
			{if $socialConnectedCount > 0 || $socialPublicConnectedCount > 0}
				<div class="pull-right">
					<img src="/img/busy1.gif" class="pull-left" id="refreshBusy">
					<button id="refresh" class="btn btn-if-default"><img src="/img/refresh-icon-16-white.png">Refresh All</button>
				</div>
			{/if}
		</div>
		
	</div>
</section>