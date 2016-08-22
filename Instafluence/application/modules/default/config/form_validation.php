<?php

$config = array(
	'login' => array(
		array(
			'field' => 'email',
			'label' => 'Email',
			'rules' => 'trim|required|valid_email'
		),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'trim|required'
		)
	),
	'signup' => array(
		array(
			'field' => 'first_name',
			'label' => 'First Name',
			'rules' => 'trim|required'
		),
		array(
			'field' => 'last_name',
			'label' => 'Last Name',
			'rules' => 'trim|required'
		),
		array(
			'field' => 'email',
			'label' => 'Email',
			'rules' => 'trim|required|valid_email|callback_unique_email'
		),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'trim|required|min_length[6]'
		),
		array(
			'field' => 'password_confirm',
			'label' => 'Password Confirmation',
			'rules' => 'trim|required|matches[password]'
		),
	),
	'setpassword' => array(
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'trim|required|min_length[6]'
		),
		array(
			'field' => 'password_confirm',
			'label' => 'Password Confirmation',
			'rules' => 'trim|required|matches[password]'
		)
	)
);