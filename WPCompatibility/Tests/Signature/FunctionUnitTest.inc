<?php
//register_block_type('test', array('foo'));
//function get_posts() {
//
//}
//get_posts('one',
//'two',
//$foo
//);
//echo "test";
wp_localize_script('test',
	'test',
	array(
		'ajax_nonce' => wp_create_nonce('test'),
	)
);