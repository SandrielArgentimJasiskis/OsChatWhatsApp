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
		        'name'      => 'Background Color',
		        'field'     => 'bg_color',
		        'required'  => 1,
		        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
	        ),
	        '2' => array(
		        'name'      => 'Buttons Background Color',
		        'field'     => 'buttons_bg_color',
		        'required'  => 1,
		        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
	        ),
	        '3' => array(
		        'name'      => 'Buttons Disabled Background Color',
		        'field'     => 'buttons_disabled_bg_color',
		        'required'  => 1,
		        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
	        ),
	        '4' => array(
		        'name'      => 'Extensions Title Background Color',
		        'field'     => 'extensions_title_bg_color',
		        'required'  => 1,
		        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
	        ),
	        '5' => array(
		        'name'      => 'Extensions Title Color',
		        'field'     => 'extensions_title_color',
		        'required'  => 1,
		        'regex'     => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
	        ),
	        '6' => array(
	            'name'      => 'Text Align',
	            'field'     => 'extensions_title_align',
	            'required'  => 1,
	            'regex'     => '/^[1-3]{1}$/'
            ),
            '7' => array(
	            'name'      => 'Text Weight',
	            'field'     => 'extensions_title_weight',
	            'required'  => 1,
	            'regex'     => '/^[1-2]{1}$/'
            ),
            '8' => array(
	            'name'      => 'Text Font',
	            'field'     => 'theme_font_id',
	            'required'  => 1,
	            'regex'     => '/^[0-9]/'
            ),
        ),
        'to_all_users'  => '1'
    );
    