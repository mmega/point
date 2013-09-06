<?php

namespace point\Main\Cache\Provider;

use point\Main\Cache\ICache;

class File implements ICache
{
	protected $path = null;

	function __construct($path = "self_cache")
	{
		$this->path = $path;
	}

	/**
	 * @return string
	 */
	public function current()
	{
		return "File";
	}

	/**
	 * @param $valueID
	 * @param $value
	 *
	 * @return bool
	 */
	public function Save($valueID, $value)
	{
		$path = $this->getCachePath($valueID);
		if (!file_exists($path))
			mkdir($path, 0777, true);

		$file_name = $this->getCacheFile($valueID);

		$statusSave = false;
		$fileResource = fopen($file_name, "w+");
		if (flock($fileResource, LOCK_EX))
		{
			fwrite($fileResource, serialize($value));
			flock($fileResource, LOCK_UN);
			$statusSave = true;
		}
		fclose($fileResource);

		return $statusSave;
	}

	/**
	 * @param     $valueID
	 * @param int $ttl
	 *
	 * @return bool|mixed
	 */
	public function Load($valueID, $ttl = 3600)
	{
		$file_name = $this->getCacheFile($valueID);

		if (!file_exists($file_name))
			return false;

		if ((filemtime($file_name) + $ttl) < time())
			return false;

		if (!$data = file($file_name))
			return false;

		return unserialize(implode("", $data));
	}

	/**
	 * @param $valueID
	 *
	 * @return bool
	 */
	public function Delete($valueID)
	{
		$file_name = $this->getCacheFile($valueID);
		return unlink($file_name);
	}

	/**
	 * @return bool
	 */
	public function Clear()
	{
		if (!file_exists($this->getCacheRootPath()))
			return false;

		foreach (
			new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($this->getCacheRootPath(), \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::CHILD_FIRST
			) as $path
		)
		{
			$path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname);
		}

		return true;
	}

	/**
	 * @param $valueID
	 *
	 * @return string
	 */
	protected function getCacheName($valueID)
	{
		return md5($valueID);
	}

	/**
	 * @return string
	 */
	protected function getCacheRootPath()
	{
		return X_POINT_CACHE_DIR;
	}

	/**
	 * @param $valueID
	 *
	 * @return string
	 */
	protected function getCachePath($valueID)
	{
		$md5 = $this->getCacheName($valueID);
		$first_literal = array($md5{0}, $md5{1}, $md5{2}, $md5{3});
		return $this->getCacheRootPath() . implode(DIRECTORY_SEPARATOR, $first_literal) . DIRECTORY_SEPARATOR;
	}

	/**
	 * @param $valueID
	 *
	 * @return string
	 */
	protected function getCacheFile($valueID)
	{
		return $this->getCachePath($valueID) . $this->getCacheName($valueID);
	}
}