<?php

header ( "Content-Type: text/html; charset=utf-8" );

header ( "X-Framework: Point" );
header_remove( "X-Powered-By" );

define ( "DS", DIRECTORY_SEPARATOR );
define ( "X_POINT_ROOT_DIR", realpath(__DIR__ . "/../") );
define ( "X_POINT_TEMP_DIR", X_POINT_ROOT_DIR . DS . "tmp" );
define ( "X_POINT_CACHE_DIR", X_POINT_TEMP_DIR . DS . "cache" );

require_once "../app/bootstrap.php";

$db = \point\Main\DB\Manager::getConnection();

class File extends \point\Main\DB\DBLayer\baseLayer
{
	protected $tableName = "b_file";

	protected $_fields = array(
		"ID" => array(
			"TYPE" => "INT",
			"AUTO_INCREMENT" => true,
			"KEY" => "PRIMARY",
		),
		"TIMESTAMP_X" => array(
			"TYPE" => "TIMESTAMP"
		),
		"MODULE_ID" => array(
			"TYPE" => "VARCHAR",
			"LENGTH" => 50
		),
		"HEIGHT" => array(
			"TYPE" => "INT"
		),
		"WIDTH" => array(
			"TYPE" => "INT"
		),
		"FILE_SIZE" => array(
			"TYPE" => "INT"
		),
		"CONTENT_TYPE" => array(
			"TYPE" => "VARCHAR",
			"LENGTH" => 255
		),
		"SUBDIR" => array(
			"TYPE" => "VARCHAR",
			"LENGTH" => 255
		),
		"FILE_NAME" => array(
			"TYPE" => "VARCHAR",
			"LENGTH" => 255
		),
		"ORIGINAL_NAME" => array(
			"TYPE" => "VARCHAR",
			"LENGTH" => 255
		),
		"DESCRIPTION" => array(
			"TYPE" => "VARCHAR",
			"LENGTH" => 255
		),
		"HANDLER_ID" => array(
			"TYPE" => "VARCHAR",
			"LENGTH" => 50
		)
	);

	/**
	 * @return \DateTime
	 */
	public function getTimeStamp()
	{
		return new \DateTime($this->TIMESTAMP_X, new \DateTimezone("Europe/Moscow"));
	}

	/**
	 * @return string
	 */
	public function getFullPath()
	{
		return "/upload/" . $this->SUBDIR . "/" . $this->FILE_NAME;
	}
}

$collection = \point\Main\DB\DBLayer\EntityManager::FindBy("File", array("`ID` > 357836"));
foreach ( $collection as $item )
{
	pre($item);
}

print "Point framework";