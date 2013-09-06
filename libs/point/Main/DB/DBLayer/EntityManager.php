<?php

namespace point\Main\DB\DBLayer;

use \point\Main\Cache\Manager as CacheManager;

class EntityManager
{
	/**
	 * @param $model
	 * @param $pk_value
	 *
	 * @return baseLayer
	 * @throws DBLayerException
	 */
	public static function FindByPK ( $model, $pk_value )
	{
		if ( class_exists( $model ) ) {
			return new $model( $pk_value );
		}

		throw new DBLayerException( "Can not find class: $model" );
	}

	/**
	 * @param       $model
	 * @param array $where
	 * @param null  $pageLimit
	 * @param array $sorting
	 * @param int   $cacheTTL
	 *
	 * @return baseLayer[]
	 * @throws DBLayerException
	 */
	public static function FindBy($model, array $where = array(), $pageLimit = null, array $sorting = array(), $cacheTTL = 0)
	{
		if ( !class_exists( $model ) )
			throw new DBLayerException( "Can not find class: $model" );

		$metaClass = new $model();
		if ( !( $metaClass instanceof baseLayer ) )
			throw new DBLayerException( "Class $model does not instance of baseLayer" );

		$cacheTTL = (int) $cacheTTL;

		$select = array();
		foreach($metaClass->getMetaModel() as $field => $fieldProperty)
			$select[] = "`".$field."`";

		$result = array();
		$sql = "SELECT ".implode(", ", $select)." FROM `".$metaClass->getTable()."` " . ((!empty($where)) ? "WHERE ".implode(" AND ", $where) : "").(!empty($sorting) ? " ORDER BY ".implode(", ", $sorting) : "");

		$cache = CacheManager::getInstance();
		$cacheKey = "sql_orm_".md5($sql)."_" . (is_int($pageLimit) ? $pageLimit : 0);
		if (false === $selectedArray = $cache->Load($cacheKey, $cacheTTL))
		{
			global $DB;

			$selectedArray = array();
			$res = \point\Main\DB\Manager::getConnection()->Query($sql);

			while($element = $res->fetch())
				$selectedArray[] = $element;

			// Кэшируем запрос, если время жизни кеша больше 0 и это не постраничка
			if ($cacheTTL > 0 && is_null($pageLimit))
				$cache->Save($cacheKey, $selectedArray);
		}

		// Собираем коллекцию моделей
		foreach ($selectedArray as $element)
			$result[] = new $model($element);

		return $result;
	}
}