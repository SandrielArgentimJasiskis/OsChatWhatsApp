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
		        'regex'     => '/^.{1,32}$/'
	        ),
	        '2' => array(
		        'name'      => 'most_attendant_color',
		        'field'     => 'most_attendant_color',
		        'required'  => 1,
		        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
	        ),
	        '3' => array(
		        'name'      => 'most_attendant_bgcolor',
		        'field'     => 'most_attendant_bgcolor',
		        'required'  => 1,
		        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
	        ),
	        '4' => array(
		        'name'      => 'fill',
		        'field'     => 'fill',
		        'required'  => 1,
		        'regex'     => '/^[0-1]{1}$/'
	        ),
	        '5' => array(
    	        'name'      => 'order',
    	        'field'     => 'order',
    	        'required'  => 1,
    	        'regex'     => '/^[0-9]{1,5}$/'
            ),
        ),
        'to_all_users'  => '0'
    );
    