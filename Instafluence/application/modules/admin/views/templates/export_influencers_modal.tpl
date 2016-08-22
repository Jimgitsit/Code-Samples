<div class="modal fade" id="exportInfluencersModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Export Influencers</h4>
			</div>
			<div class="modal-body">

				<form role="form" id="exportInfluencerForm">
					Choose a format:
					<div class="radio">
						<label>
							<input type="radio" id="format-csv" checked /> CSV
						</label>
					</div>

					Info to Export:<br /> <br />
					<div class="check">
						<label>
							<input type="checkbox" name="Name" checked /> Name
						</label>

						<label>
							<input type="checkbox" name="Email" checked /> Email
						</label>

						<label>
							<input type="checkbox" name="PayPal" /> PayPal Email
						</label>

						<label>
							<input type="checkbox" name="Vine Link" /> Vine Link
						</label>

						<label>
							<input type="checkbox" name="Vine Following" /> Vine Following
						</label>

						<label>
							<input type="checkbox" name="Vine Price" /> Vine Price
						</label>

						<label>
							<input type="checkbox" name="Instagram Link" /> Instagram Link
						</label>

						<label>
							<input type="checkbox" name="Instagram Following" /> Instagram Following
						</label>

						<label>
							<input type="checkbox" name="Instagram Price" /> Instagram Price
						</label>

						<label>
							<input type="checkbox" name="Twitter Link" /> Twitter Link
						</label>

						<label>
							<input type="checkbox" name="Twitter Following" /> Twitter Following
						</label>

						<label>
							<input type="checkbox" name="Twitter Price" /> Twitter Price
						</label>
					</div>
				</form>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="exportFilter">Export</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
