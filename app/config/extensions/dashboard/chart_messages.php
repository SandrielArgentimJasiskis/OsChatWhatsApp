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
    	        'name'      => 'message_sent_color',
    	        'field'     => 'message_sent_color',
    	        'required'  => 1,
    	        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
            ),
            '3' => array(
    	        'name'      => 'message_read_color',
    	        'field'     => 'message_read_color',
    	        'required'  => 1,
    	        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
            ),
            '3' => array(
    	        'name'      => 'message_bgcolor',
    	        'field'     => 'message_bgcolor',
    	        'required'  => 1,
    	        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
            ),
            '4' => array(
    	        'name'      => 'Fill',
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
        'to_all_users' => '0'
    );
    