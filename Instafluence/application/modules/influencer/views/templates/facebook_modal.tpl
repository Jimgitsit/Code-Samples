<div class="modal fade" id="facebookModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Select Facebook Pages</h4>
			</div>
			<div class="modal-body">

				<p>Select wich pages to use with your Instafluence account.</p>
				
				<ul class="page-list">
					
					{if isset($social.facebook[$newAccountIndex].profile.picture.url)}
					<li class="page-item">
						<div class="page-wrapper">
							<img src="{$social.facebook[$newAccountIndex].profile.picture.url}" class="profile-pic">
							<label class="page-name">
								<span>{$social.facebook[$newAccountIndex].profile.name}</span>
							</label>
							<input type="checkbox" class="page-check" value="-1" />
						</div>
						<div class="clearfix"></div>
					</li>
					{/if}
					
					{if isset($social.facebook[$newAccountIndex].accounts)}
						{foreach $social.facebook[$newAccountIndex].accounts as $index => $account}
							<li class="page-item">
								<div class="page-wrapper">
									<img src="{$account.picture}" class="">
									<label class="page-name">
										<span>{$account.name}</span>
									</label>
									<input type="checkbox" class="page-check" value="{$index}" />
								</div>
								<div class="clearfix"></div>
							</li>
						{/foreach}
					{/if}
					
				</ul>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" id="facebookCancelBtn" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="facebookConnectBtn" data-account-index="{$newAccountIndex}">Connect</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->