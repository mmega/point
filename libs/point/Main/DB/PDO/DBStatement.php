<?php

namespace point\Main\DB\PDO;

class DBStatement extends \PDOStatement
{
	protected $dbHandler = null;

	protected function __construct( Database $dbHandler )
	{
		$this->dbHandler = $dbHandler;
		$this->setFetchMode( \PDO::FETCH_ASSOC );
	}
}