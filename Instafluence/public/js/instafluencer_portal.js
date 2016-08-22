$(document).ready(function() {
	$('#signup-form').bootstrapValidator({
		feedbackIcons: {
			valid: 'glyphicon glyphicon-ok',
			invalid: 'glyphicon glyphicon-remove',
			validating: 'glyphicon glyphicon-refresh'
		},
		fields: {
			first_name: {
				validators: {
					notEmpty: {
						message: 'First name is required'
					}
				}
			},
			last_name: {
				validators: {
					notEmpty: {
						message: 'Last name is required'
					}
				}
			},
			email: {
				validators: {
					notEmpty: {
						message: 'Email address is required'
					},
					emailAddress: {
						message: 'Please enter a valid email address'
					}
				}
			},
			password: {
				validators: {
					notEmpty: {
						message: 'Password is required'
					},
					identical: {
						field: 'password_confirm',
						message: 'The passwords do not match'
					}
				}
			},
			password_confirm: {
				validators: {
					notEmpty: {
						message: 'Please confirm your password'
					},
					identical: {
						field: 'password',
						message: 'The passwords do not match'
					}
				}
			}
		}
	});
});
