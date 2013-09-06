<?php

namespace point\Main\Cache;

class Manager implements ICache
{
	private static $_instance = null;

	/**
	 * @return $this
	 */
	public static function getInstance()
	{
		if (!self::$_instance)
			self::$_instance = new self();

		return self::$_instance;
	}

	private function __clone() {}
	private function __construct() {}

	/**
	 * @var ICache
	 */
	protected $provider = null;

	/**
	 * @param ICache $provider
	 *
	 * @return $this
	 */
	public function initProvider(ICache $provider)
	{
		$this->provider = $provider;
		return $this;
	}

	/**
	 * @return string
	 * @throws CacheException
	 */
	public function current()
	{
		if (!$this->provider)
			throw new CacheException("Provider can not be null");

		return $this->provider->current();
	}

	/**
	 * @param $valueID
	 * @param $value
	 *
	 * @return bool
	 * @throws CacheException
	 */
	public function Save($valueID, $value)
	{
		if (!$this->provider)
			throw new CacheException("Provider can not be null");

		return $this->provider->Save($valueID, $value);
	}

	/**
	 * @param     $valueID
	 * @param int $ttl
	 *
	 * @return mixed
	 * @throws CacheException
	 */
	public function Load($valueID, $ttl = 3600)
	{
		if (!$this->provider)
			throw new CacheException("Provider can not be null");

		return $this->provider->Load($valueID, $ttl);
	}

	/**
	 * @param $valueID
	 *
	 * @return bool
	 * @throws CacheException
	 */
	public function Delete($valueID)
	{
		if (!$this->provider)
			throw new CacheException("Provider can not be null");

		return $this->provider->Delete($valueID);
	}

	/**
	 * @return bool
	 * @throws CacheException
	 */
	public function Clear()
	{
		if (!$this->provider)
			throw new CacheException("Provider can not be null");

		return $this->provider->Clear();
	}
}