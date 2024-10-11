<?php
    
    return array(
        'fields' => array(
            '0' => array(
		        'name'      => 'Status',
		        'field'     => 'status',
		        'required'  => 1,
		        'regex'     => '/^[0-1]{1}$/'
	        ),
            '1' => array(
		        'name'      => 'Site Key',
		        'field'     => 'site_key',
		        'required'  => 1,
		        'regex'     => '/^.{32,128}$/'
	        ),
	        '2' => array(
		        'name'      => 'Secret Key',
		        'field'     => 'secret_key',
		        'required'  => 1,
		        'regex'     => '/^.{32,128}$/'
	        ),
        ),
        'to_all_users' => '1'
    );