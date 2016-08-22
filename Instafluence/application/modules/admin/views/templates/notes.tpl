<section id="notes">
	<div class="container">
		<ul class="note-list">
			<li class="note-item note-template" style="display: none;">
				<div class="note-content editable" data-email="{$user.email}" data-note-id=""></div>
				<span class="note-date pull-right">{$smarty.now|date_format:"%m/%d/%Y"}</span>
				<div class="note-details">
					<div class="created">Created</div>
					<div class="created-name">{$adminName}</div>
					<div class="created-date">{$smarty.now|date_format:"%m/%d/%Y"}</div>
					<div class="last-edit">Last Edit</div>
					<div class="last-edit-name">{$adminName}</div>
					<div class="last-edit-date">{$smarty.now|date_format:"%m/%d/%Y"}</div>
				</div>
				<div class="clearfix"></div>
			</li>

			{foreach $notes as $id => $note}
				<li class="note-item">
					<div class="note-content editable" data-email="{$note.email}" data-note-id="{$id}">{$note.markup}</div>
					<span class="note-date pull-right">{date('m/d/Y', $note.modified_date->sec)}</span>
					<div class="note-details">
						<div class="created">Created</div>
						<div class="created-name">{$note.created_by}</div>
						<div class="created-date">{date('m/d/Y', $note.created_date->sec)}</div>
						<div class="last-edit">Last Edit</div>
						<div class="last-edit-name">{$note.created_by}</div>
						<div class="last-edit-date">{date('m/d/Y', $note.modified_date->sec)}</div>
					</div>
					<div class="clearfix"></div>
				</li>
			{/foreach}
		</ul>

		<div class="form-group text-center">
			<button id="addNoteBtn" class="btn btn-default cyan-outline-btn">Add Note</button>
		</div>

	</div>
</section>