<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="/img/close.png" alt="close" width="20" height="20"></button>
      <div class="modal-body">

        <h2>Let's Talk</h2>
        <p>Get in touch at 602-680-7309,<br/>or select one of the options below:</p>
        <ul>
	        <li class="contact-item"><a href="#brand-form"><span class="contact-tab-text">I have an app/brand</span></a></li>
	        <li class="contact-item"><a href="#influencer-form"><span class="contact-tab-text">I'm an influencer</span></a></li>
        </ul>
        <br class="clear">

        <!-- tab content -->
        <div class="tab-content">
        	<div class="tab-pane" id="brand-form">
        		<!-- <form class="form" action="https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8" method="POST" id="brand-contact-form"> -->
				<form class="form" action="/contactformsubmit/" method="POST" id="brand-contact-form">
					<input type=hidden name="oid" value="00DG0000000iGWQ">
					<!-- <input type=hidden name="retURL" value="http://instafluence.com/confirmation/"> -->
					<input type=hidden name="retURL" value="http://instafluence.local/confirmation/">
					
					<label for="first_name">First Name<span class="red-text">*</span></label>
					<input  id="first_name" maxlength="40" name="first_name" size="20" type="text" />
					<br>

					<label for="last_name">Last Name<span class="red-text">*</span></label>
					<input  id="last_name" maxlength="80" name="last_name" size="20" type="text" />
					<br>

					<label for="email">Email<span class="red-text">*</span></label>
					<input  id="email" maxlength="80" name="email" size="20" type="text" />
					<br>

					<label for="company">Company<span class="red-text">*</span></label>
					<input  id="company" maxlength="40" name="company" size="20" type="text" />
					<br>

					<label for="phone">Phone Number<span class="red-text">*</span></label>
					<input  id="phone" maxlength="40" name="phone" size="20" type="text" />
					<br>

					<label for="comments">What would you like to accomplish?<span class="red-text">*</span></label>
					<textarea  id="comments" name="comments" rows="5" wrap="soft"></textarea>
					<br>
					
					<!-- What is this for??? -->
					<input type="text" id="comp" hidden name="comp">
					
					<span class="confirmation-field"><label for="email_confirmation">Email confirmation:</label><input type="text" name="email_confirmation" value="" /></span>

					<input type="submit" name="formType" value="Submit">

				</form>
        	</div>
        	 
        	<div class="tab-pane" id="influencer-form">
        		<form class="form" action="/signup/" method="POST">
					<!--
					<label for="first_name">First Name<span class="red-text">*</span></label><input  id="first_name" maxlength="40" name="first_name" size="20" type="text" /><br>

					<label for="last_name">Last Name<span class="red-text">*</span></label><input  id="last_name" maxlength="80" name="last_name" size="20" type="text" /><br>

					<label for="email">Email<span class="red-text">*</span></label><input  id="email" maxlength="80" name="email" size="20" type="text" /><br>

					<label for="password">Password<span class="red-text">*</span></label><input  id="password" maxlength="80" name="password" size="20" type="password" /><br>

					<label for="password_confirm">Retype Password<span class="red-text">*</span></label><input  id="password_confirm" maxlength="80" name="password_confirm" size="20" type="password" /><br>
					-->
					<input type="submit" value="Sign Up" id="influencer-submit-btn">

				</form>
        	</div>
        </div>
      </div>

    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
