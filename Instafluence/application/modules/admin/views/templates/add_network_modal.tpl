<div class="modal fade" id="addNetworkModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Add a Social Network</h4>
			</div>
			<div class="modal-body">

				<label class="bold">Select a social network</label>
				<select class="select2-no-clear form-control input-sm" id="selectSocialNetwork">
					<option></option>
					<option data-type="username">Instagram</option>
					<option data-type="url">Vine</option>
					<option data-type="url">Facebook</option>
					<option data-type="url">Foursquare</option>
					<option data-type="url">GooglePlus</option>
					<option data-type="url">LinkedIn</option>
					<option data-type="url">Pinterest</option>
					<option data-type="url">Tumblr</option>
					<option data-type="url">Twitter</option>
					<option data-type="url">Wordpress</option>
					<option data-type="url">YouTube</option>
					<!-- <option data-type="username">Snapchat</option> -->
				</select>

				<div id="usernameBlock">
					<label class="bold">Enter their username</label>
					<input type="text" id="addNetworkUsername" />
				</div>

				<div id="urlBlock">
					<label class="bold">Enter the URL to their <span id="urlSocialName"></span> page</label>
					<input type="text" id="addNetworkURL" />
				</div>

			</div>
			<div class="modal-footer">
				<img src="/img/busy1.gif" class="pull-left" id="addNetworkBusy">
				<button type="button" class="btn btn-default" id="cancelNetworkBtn" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="addNetworkBtn" disabled>Add</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
