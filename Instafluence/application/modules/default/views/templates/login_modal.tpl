<div class="modal fade" id="modalLogin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="/img/close.png" alt="close" width="20" height="20"></button>

			<div class="modal-body">
				<h2>Login</h2>

				<form class="form" action="/login/" method="POST">
					<label for="email">Email</label>
					<input  id="email" maxlength="40" name="email" type="text" /><br>

					<label for="password">Password</label>
					<input  id="password" maxlength="80" name="password" size="20" type="password" /><br> <br>

					<input type="submit" name="formType" value="Login">
					<p>Don't have an account?<br /><a href="/signup/">Sign Up</a></p>
				</form>
			</div>
		</div>
	</div>
</div>