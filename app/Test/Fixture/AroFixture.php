<?php
/**
 * AroFixture
 *
 */
class AroFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'parent_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10),
		'model' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'foreign_key' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10),
		'alias' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'lft' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10),
		'rght' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'parent_id' => null,
			'model' => 'UserRole',
			'foreign_key' => 1, // admin user role
			'alias' => null,
			'lft' => 1,
			'rght' => 4
		),
		array(
			'id' => 2,
			'parent_id' => 1,
			'model' => 'User',
			'foreign_key' => 42, // admin user
			'alias' => null,
			'lft' => 2,
			'rght' => 3
		),
		array(
			'id' => 3,
			'parent_id' => null,
			'model' => 'UserRole',
			'foreign_key' => 2, // managers user role
			'alias' => null,
			'lft' => 5,
			'rght' => 6
		),
		array(
			'id' => 4,
			'parent_id' => null,
			'model' => 'UserRole',
			'foreign_key' => 3, // users user role
			'alias' => null,
			'lft' => 7,
			'rght' => 12
		),
		array(
			'id' => 5,
			'parent_id' => null,
			'model' => 'UserRole',
			'foreign_key' => 5, // guests user role
			'alias' => null,
			'lft' => 13,
			'rght' => 14
		),
		array(
			'id' => 6,
			'parent_id' => 4,
			'model' => 'User',
			'foreign_key' => 2, // user in "users" user role
			'alias' => null,
			'lft' => 8,
			'rght' => 9
		),
		array(
			'id' => 7,
			'parent_id' => 4,
			'model' => 'User',
			'foreign_key' => 100, // user in "users" user role
			'alias' => null,
			'lft' => 10,
			'rght' => 11
		),
	);
}
