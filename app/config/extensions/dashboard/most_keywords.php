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

		        'name'      => 'Title',
		        'field'     => 'title',
		        'required'  => 1,
		        'regex'     => '/^.{1,64}$/'
	        ),
	        '2' => array(
		        'name'      => 'Limit',
		        'field'     => 'limit',
		        'required'  => 1,
		        'regex'     => '/^[1-9]\d{0,1}$/'
	        ),
	        '3' => array(
    	        'name'      => 'order',
    	        'field'     => 'order',
    	        'required'  => 1,
    	        'regex'     => '/^[0-9]{1,5}$/'
            ),
        ),
        'to_all_users'  => '0'
    );
    