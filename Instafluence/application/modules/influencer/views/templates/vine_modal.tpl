<div class="modal fade" id="vineModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Connect to Vine</h4>
			</div>
			<div class="modal-body">

				<label for="email">Email</label>
				<input id="email" maxlength="40" type="text" /><br>

				<label for="password">Password</label>
				<input id="password" maxlength="80" size="20" type="password" /><br> <br>

				<h4>If you connect to Vine via Twitter:</h4>
				<p>Log into <a href="http://vine.co/" target="_blank">Vine</a> in your browser and go to settings. From there add your email address and reset your password (your new password can be the same as your existing Twitter password). After this you can use that email address and password here.</p>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="vineConnectBtn">Connect</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->