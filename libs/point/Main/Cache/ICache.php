<?php

namespace point\Main\Cache;

interface ICache
{
	/**
	 * @return string
	 */
	public function current();

	/**
	 * @param $valueID
	 * @param $value
	 * @return mixed
	 */
	public function Save($valueID, $value);

	/**
	 * @param $valueID
	 * @param int $ttl
	 * @return mixed
	 */
	public function Load($valueID, $ttl = 3600);

	/**
	 * @param $valueID
	 * @return bool
	 */
	public function Delete($valueID);

	/**
	 * @return bool
	 */
	public function Clear();
}