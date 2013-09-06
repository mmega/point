<?php

namespace point\Main\DB\PDO;

class Database extends \PDO
{
	public function __construct ( $dsn, $username="", $password="", $driver_options = array() )
	{
		parent::__construct( $dsn,$username,$password, $driver_options );
		$this->setAttribute( \PDO::ATTR_STATEMENT_CLASS, array( '\point\Main\DB\PDO\DBStatement', array( $this ) ) );
	}
}