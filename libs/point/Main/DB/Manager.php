<?php

namespace point\Main\DB;

use point\Main\DB\PDO\Database;

class Manager
{
	private static $connection = null;

	/**
	 * @var \point\Main\DB\PDO\Database
	 */
	protected $connectionPool = null;

	protected $engine = null;
	protected $host = null;
	protected $database = null;
	protected $user = null;
	protected $password = null;
	protected $dsn = null;
	protected $driverOptions = null;

	private function __clone() {}
	private function __construct() {}

	/**
	 * @return $this
	 */
	public static function getConnection()
	{
		if (!self::$connection)
			self::$connection = new self();

		return self::$connection;
	}

	/**
	 * @return void
	 *
	 * Инициализция значений для открытия пула к БД из конфига
	 */
	protected function initConnectionSettings()
	{
		$this->host = "localhost";
		$this->engine = "mysql";
		$this->database = "tbff_v1";
		$this->user = "tbff_v1";
		$this->password = "5LgOKHoz";
		$this->dsn = $this->engine.":dbname=".$this->database.";host=".$this->host;
		$this->driverOptions = array();
	}

	/**
	 * @return Database
	 *
	 * Возвращает теущий открытый PDO ConnectionPool, если он есть. Если его нет - открывает его, заьем возвращает.
	 */
	public function getConnectionPool()
	{
		if (!$this->connectionPool)
		{
			$this->initConnectionSettings();
			$this->connectionPool = new Database( $this->dsn, $this->user, $this->password, $this->driverOptions );
		}

		return $this->connectionPool;
	}

	/**
	 * @return bool
	 */
	public function beginTransaction()
	{
		return $this->getConnectionPool()->beginTransaction();
	}

	/**
	 * @return bool
	 */
	public function Commit()
	{
		return $this->getConnectionPool()->commit();
	}

	/**
	 * @return bool
	 */
	public function rollBack()
	{
		return $this->getConnectionPool()->rollBack();
	}

	/**
	 * @param $queryString
	 *
	 * @return \point\Main\DB\PDO\DBStatement
	 */
	public function Query($queryString)
	{
		pre($queryString);
		return $this->getConnectionPool()->query($queryString);
	}

	/**
	 * @return string
	 */
	public function lastInsertId()
	{
		return $this->getConnectionPool()->lastInsertId();
	}
}