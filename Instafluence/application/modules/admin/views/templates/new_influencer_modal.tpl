<div class="modal fade" id="newInfluencerModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Add a New Influencer</h4>
			</div>
			<div class="modal-body">

				<form role="form" id="newInfluencerForm">
					<div class="form-group">
						<label for="firstName">First Name</label>
						<input type="text" id="firstName" name="first_name" class="form-control" minlength="2" maxlength="50" required />
					</div>
					<div class="form-group">
						<label for="lastName">Last Name</label>
						<input type="text" id="lastName" name="last_name" class="form-control" minlength="2" maxlength="50" required />
					</div>
					<div class="form-group">
						<label for="email">Email</label>
						<input type="email" id="email" name="email" class="form-control" minlength="2" maxlength="50" required />
					</div>
				</form>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="continueBtn">Continue</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
