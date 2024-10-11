<?php
    
    return array(
        'fields' => array(
            '0' => array(
		        'name'      => 'Status',
		        'field'     => 'status',
		        'required'  => 1,
		        'regex'     => '/^[0-1]{1}$/'
	        )
        ),
        'validate' => array(
            'status'   => '1',
            'controller' => 'pages/extensions',
            'method' => 'validateUniqueExensionStatusType'
        ),
        'to_all_users' => '1'
    );