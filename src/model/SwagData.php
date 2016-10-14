<?php

require_once __DIR__."/../../ext/wprecord/WpRecord.php";

/**
 * Represents settings about a Swag badge.
 */
class SwagData extends WpRecord {

	public $id;
	public $string;
	public $color;
	public $description;

	/**
	 * Construct.
	 */
	public function __construct() {
	}

	/**
	 * Initialize fields.
	 */
	static function initialize() {
		self::field("id", "integer not null auto_increment");
		self::field("string", "varchar(255) not null");
		self::field("color", "varchar(255) not null");
		self::field("description", "text not null");
	}
}