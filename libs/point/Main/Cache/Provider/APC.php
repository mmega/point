<?php

namespace point\Main\Cache\Provider;

use point\Main\Cache\ICache;

class APC implements ICache
{
	protected $ttl = null;

	/**
	 * @return string
	 */
	public function current()
	{
		return "APC";
	}

	/**
	 * @param $valueID
	 * @param $value
	 *
	 * @return array|bool
	 */
	public function Save($valueID, $value)
	{
		if (!$this->ttl)
			$this->ttl = 3600;

		return apc_store($valueID, serialize($value), $this->ttl);
	}

	/**
	 * @param     $valueID
	 * @param int $ttl
	 *
	 * @return mixed
	 */
	public function Load($valueID, $ttl = 3600)
	{
		$this->ttl = $ttl;
		return unserialize(apc_fetch($valueID));
	}

	/**
	 * @param $valueID
	 *
	 * @return bool|\string[]
	 */
	public function Delete($valueID)
	{
		return apc_delete($valueID);
	}

	/**
	 * @return bool
	 */
	public function Clear()
	{
		return apc_clear_cache() && apc_clear_cache("user");
	}
}