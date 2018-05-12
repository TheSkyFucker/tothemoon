<?php


$config = array(


	//user.resiger
	'user_register' => array(
		array(
			'field' => 'username',
			'label' => '用户名',
			'rules' => 'required|min_length[6]|max_length[16]|alpha_dash'
			),
		array(
			'field' => 'password',
			'label' => '密码',
			'rules' => 'required|min_length[6]|max_length[16]'
			),
		array(
			'field' => 'realname',
			'label' => '真实姓名',
			'rules' => 'required|min_length[1]|max_length[10]'
			)
		),

	//register
	'register'=> array(
		
		)

);