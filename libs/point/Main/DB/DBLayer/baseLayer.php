<?php

namespace point\Main\DB\DBLayer;

use point\Main\DB\Manager;

abstract class baseLayer implements \Countable, \Serializable
{
	const MODE_TYPE_ORIGIN = 1;
	const MODE_TYPE_MODIFIED = 2;

	/**
	 * @var string Тип таблицы
	 */
	protected $storageEngine = "InnoDB";
	/**
	 * @var string Кодировка таблицы по умолчанию
	 */
	protected $charset = "utf8";
	/**
	 * @var string Кодировка сравнения таблицы
	 */
	protected $collation = "utf8_general_ci";

	/**
	 * @var string Имя таблицы
	 */
	protected $tableName = null;
	/**
	 * @var array Описаная модель таблицы
	 */
	protected $_fields = null;

	/**
	 * @var string Первичный ключ (определяется автомтаически на основе описаной модели)
	 */
	private $primaryKey = null;

	/**
	 * @param null|int|string|array $model Значение первичного ключа или ассоциированный массив выборки
	 *
	 * @throws DBLayerException
	 *
	 * Создает новую модель. В случае указания $model знаечния уникального ключа модель сама достанет информацию о себе.
	 * Если передать в качестве $model ассоциированный массив то модель создастся без обращения к БД и будет заполнена указанными в массиве значениями.
	 */
	function __construct ( $model = null )
	{
		if ( is_null( $this->_fields ) || empty( $this->_fields ) ) {
			throw new DBLayerException( "You should describe the metadata model!" );
		}

		$this->RefreshFromModel( $model );
	}

	/**
	 * @return int|null|string
	 * Возвращает первичный ключ
	 */
	public function getPrimaryKey ()
	{
		if ( is_null( $this->primaryKey ) ) {
			foreach ( $this->_fields as $key => $keyProperty )
				if ( isset( $keyProperty[ "KEY" ] ) && $keyProperty[ "KEY" ] == "PRIMARY" ) {
					$this->primaryKey = $key;
					break;
				}
		}

		return $this->primaryKey;
	}

	/**
	 * @param $value новое значение для первичного ключа
	 *
	 * @throws DBLayerException
	 *
	 * Изменяет значение первичного ключа (по умолчанию данное поле ReadOnly)
	 */
	protected function setPrimaryKeyValue ( $value )
	{
		if ( !$this->getPrimaryKey() )
			throw new DBLayerException( "Can not verify the existence of a record without a declared primary key" );

		$this->_fields[ $this->getPrimaryKey() ][ "VALUE" ] = $value;
	}

	/**
	 * @param $name Название поля
	 *
	 * @return bool
	 * @throws DBLayerException
	 *
	 * Проверяет доступность на запись поля $name
	 */
	protected function isReadOnly ( $name )
	{
		if ( !isset( $this->_fields[ $name ] ) )
			throw new DBLayerException( "Field {$name} does not exist" );

		return ( isset( $this->_fields[ $name ][ "AUTO_INCREMENT" ] ) && $this->_fields[ $name ][ "AUTO_INCREMENT" ] === true
			|| isset( $this->_fields[ $name ][ "READ_ONLY" ] ) && $this->_fields[ $name ][ "READ_ONLY" ] === true );
	}

	/**
	 * @return bool
	 * @throws DBLayerException
	 *
	 * Проверяет, существует ли запись с указанным первичным ключом в таблице
	 */
	protected function isExists ()
	{
		if ( !$this->getPrimaryKey() )
			throw new DBLayerException( "Can not verify the existence of a record without a declared primary key" );

		if ( !intval( $this->{$this->getPrimaryKey()} ) )
			return false;

		$query = "SELECT COUNT('{$this->getPrimaryKey()}') FROM `{$this->tableName}` WHERE `{$this->getPrimaryKey()}`='" . $this->{$this->getPrimaryKey()} . "';";

		$res = Manager::getConnection()->Query( $query );

		return ( $res->rowCount() > 0 );
	}

	/**
	 * @return null|string
	 *
	 * Возвращает sql запрос на вставку нового элемента в таблицу
	 */
	protected function getInsertSQL ()
	{
		$column = array ();
		$values = array ();
		foreach ( $this->_fields as $fieldName => $fieldProperty ) {
			if ( $this->isReadOnly( $fieldName ) )
				continue;

			$value = null;
			if ( isset( $fieldProperty[ "VALUE" ] ) )
				$value = $fieldProperty[ "VALUE" ];
			elseif ( isset( $fieldProperty[ "DEFAULT" ] ) )
				$value = $fieldProperty[ "DEFAULT" ];

			if ( is_null( $value ) ) continue;

			$column[] = "`" . $fieldName . "`";
			$values[] = Manager::getConnection()->getConnectionPool()->quote($value);
		}

		if ( empty( $column ) && empty( $values ) )
			return null;

		$sql = "INSERT INTO `" . $this->tableName . "` (" . implode( ", ", $column ) . ") VALUES (" . implode( ", ", $values ) . ");";
		return $sql;
	}

	/**
	 * @return null|string
	 *
	 * Вохвращает sql запрос на обновление элемента с указанным первичным ключом (только измененные поля)
	 */
	protected function getUpdateSQL ()
	{
		if ( !$this->getPrimaryKey() )
			return null;

		if ( !( $this->{$this->getPrimaryKey()} ) )
			return null;

		$updateItems = array();
		foreach($this->_fields as $fieldName => $fieldProperty)
		{
			if (isset($fieldProperty["FIELD_CVS"]) && $fieldProperty["FIELD_CVS"] == self::MODE_TYPE_MODIFIED)
				if (isset($fieldProperty["VALUE"]))
					$updateItems[] = "`".$fieldName."`=".Manager::getConnection()->getConnectionPool()->quote($fieldProperty["VALUE"]);
		}

		if ( empty( $updateItems ) )
			return null;

		$sql = "UPDATE `" . $this->tableName . "` SET " . implode( ", ", $updateItems ) . " WHERE `{$this->getPrimaryKey()}`='" . $this->{$this->getPrimaryKey()} . "';";
		return $sql;
	}

	/**
	 * @return null|string
	 *
	 * Возвращает sql запрос на выборку значений элемента с указанным первичным ключом
	 */
	protected function getSelectSQL ()
	{
		if ( !$this->getPrimaryKey() )
			return null;

		if ( !( $this->{$this->getPrimaryKey()} ) )
			return null;

		$select = array ();
		foreach ( $this->_fields as $fieldName => $fieldProperty )
			$select[ ] = "`" . $fieldName . "`";

		if ( empty( $select ) )
			return null;

		$sql = "SELECT " . implode( ", ", $select ) . " FROM `" . $this->tableName . "` WHERE `{$this->getPrimaryKey()}`='" . $this->{$this->getPrimaryKey()} . "' LIMIT 1;";
		return $sql;
	}

	/**
	 * @return null|string
	 *
	 * Возвращает sql запрос на удаление записи с указанным первичным ключом
	 */
	protected function getDeleteSql ()
	{
		if ( !$this->getPrimaryKey() )
			return null;

		if ( !( $this->{$this->getPrimaryKey()} ) )
			return null;

		$sql = "DELETE FROM `" . $this->tableName . "` WHERE `{$this->getPrimaryKey()}`='" . $this->{$this->getPrimaryKey()} . "' LIMIT 1;";
		return $sql;
	}

	/**
	 * @param array $fields Ассоциированный массив поле=>знаечние
	 *
	 * Обновляет все значения модели из входящего массива $fields
	 */
	protected function updateFieldsValues ( array $fields )
	{
		foreach ( $fields as $fieldColumn => $fieldValue ) {
			$this->_fields[ $fieldColumn ][ "VALUE" ] = $fieldValue;
			$this->_fields[ $fieldColumn ][ "FIELD_CVS" ] = self::MODE_TYPE_ORIGIN;
		}
	}

	/**
	 * @throws DBLayerException
	 *
	 * Достает значения полей по первичному ключу и обновляет значения для текущей модели
	 */
	protected function refreshFields ()
	{
		$sql = $this->getSelectSQL();

		if ( !$sql )
			throw new DBLayerException( "Empty query" );

		$res = Manager::getConnection()->Query( $sql );
		if ( $fields = $res->fetch() )
			$this->updateFieldsValues( $fields );
		else
			throw new DBLayerException( "Model with " . $this->getPrimaryKey() . " = " . $this->{$this->getPrimaryKey()} . " not found" );
	}

	/**
	 * @param $field Название поля
	 * @param $value Новое значение поля
	 *
	 * Вносит изменения в значение текущей модели (без проверки на ReadOnly)
	 */
	public function writeFieldValue ( $field, $value )
	{
		$this->_fields[ $field ][ "VALUE" ] = $value;
		$this->_fields[ $field ][ "FIELD_CVS" ] = self::MODE_TYPE_MODIFIED;
	}

	/**
	 * @param int|string|array $model Значение первичного ключа или ассоцииррованный массив выборки
	 *
	 * Заполняет текущую модель знаечниями из ассоциированного массива,
	 * либо достает данные из БД по указанному значению первичного ключа.
	 */
	public function RefreshFromModel ( $model )
	{
		if ( !is_null( $model ) ) {
			if ( is_array( $model ) ) {
				$this->updateFieldsValues( $model );
			}
			else {
				$this->setPrimaryKeyValue( $model );
				$this->refreshFields();
			}
		}
	}

	/**
	 * @return int ID последней добавленной записи
	 * @throws DBLayerException
	 *
	 * Сохраняет измнения модели.
	 * В случае, если это новая модель - создаст новую запись в БД.
	 * Если запись уже существует и указан первичный ключ - запись будет обновлена.
	 */
	public function Save ()
	{
		if ( !$this->isExists() )
			$sql = $this->getInsertSQL();
		else
			$sql = $this->getUpdateSQL();

		if ( !$sql )
			throw new DBLayerException( "Empty query" );

		Manager::getConnection()->Query( $sql );
		$lastID = Manager::getConnection()->lastInsertId();
		if ( intval( $lastID ) )
			$this->setPrimaryKeyValue( $lastID );

		$this->refreshFields();

		return $this->{$this->getPrimaryKey()};
	}

	/**
	 * @throws DBLayerException
	 *
	 * Удаляет запись из БД по указанному первичному ключу
	 */
	public function Delete ()
	{
		$sql = $this->getDeleteSql();

		if ( !$sql )
			throw new DBLayerException( "Empty query" );

		Manager::getConnection()->Query( $sql );
	}

	/**
	 * @return string
	 *
	 * Возвращает имя текущей таблицы
	 */
	public function getTable ()
	{
		return $this->tableName;
	}

	/**
	 * @return array
	 *
	 * Возвращает описанную модель текущей БД
	 */
	public function getMetaModel ()
	{
		return $this->_fields;
	}

	/**
	 * @param $name Название поля
	 *
	 * @return null|mixed
	 * @throws DBLayerException
	 *
	 * Магический метод, позваляет получать значение поля модели как свойство
	 */
	public function __get ( $name )
	{
		if ( !isset( $this->_fields[ $name ] ) )
			throw new DBLayerException( "Field {$name} does not exist" );

		return isset( $this->_fields[ $name ][ "VALUE" ] ) ? $this->_fields[ $name ][ "VALUE" ] : null;
	}

	/**
	 * @param $name  Название поля
	 * @param $value Значение поля
	 *
	 * @throws DBLayerException
	 *
	 * Магический метод, позваляет записывать измнения в поля модели как в свойства
	 */
	public function __set ( $name, $value )
	{
		if ( $this->isReadOnly( $name ) )
			throw new DBLayerException( "Field {$name} is ReadOnly mode!" );

		$this->writeFieldValue( $name, $value );
	}

	/**
	 * @return int Количество полей в модели
	 */
	public function count()
	{
		return count($this->_fields);
	}

	/**
	 * @return string Сериализует значение модели
	 */
	public function serialize()
	{
		return serialize($this->_fields);
	}

	/**
	 * @param string $serialized Сериализованные данные модели
	 *
	 * Восстанавлиает состояние модели после сериализации
	 */
	public function unserialize($serialized)
	{
		$this->_fields = unserialize($serialized);
	}

}