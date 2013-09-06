<?php

class AutoLoad
{
	private $_fileExtension = array( '.php', '.class.php' );

	private $_namespace;
	private $_includePath;
	private $_namespaceSeparator = '\\';

	/**
	 * @param string $ns
	 * @param string $includePath
	 */
	public function __construct($ns = null, $includePath = null)
	{
		$this->_namespace = $ns;
		$this->_includePath = $includePath;
	}

	/**
	 * Sets the namespace separator used by classes in the namespace of this class loader.
	 *
	 * @param string $sep The separator to use.
	 * @return $this
	 */
	public function setNamespaceSeparator($sep)
	{
		$this->_namespaceSeparator = $sep;
		return $this;
	}

	/**
	 * Gets the namespace separator used by classes in the namespace of this class loader.
	 *
	 * @return string
	 */
	public function getNamespaceSeparator()
	{
		return $this->_namespaceSeparator;
	}

	/**
	 * Sets the base include path for all class files in the namespace of this class loader.
	 *
	 * @param string $includePath
	 * @return $this
	 */
	public function setIncludePath($includePath)
	{
		$this->_includePath = $includePath;
		return $this;
	}

	/**
	 * Gets the base include path for all class files in the namespace of this class loader.
	 *
	 * @return string $includePath
	 */
	public function getIncludePath()
	{
		return $this->_includePath;
	}

	/**
	 * Sets the file extension of class files in the namespace of this class loader.
	 *
	 * @param string $fileExtension
	 * @return $this
	 */
	public function setFileExtension($fileExtension)
	{
		$this->_fileExtension = $fileExtension;
		return $this;
	}

	/**
	 * Gets the file extension of class files in the namespace of this class loader.
	 *
	 * @return string $fileExtension
	 */
	public function getFileExtension()
	{
		return $this->_fileExtension;
	}

	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	}

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $className The name of the class to load.
	 * @return bool
	 */
	public function loadClass($className)
	{
		if (null === $this->_namespace
			|| $this->_namespace.$this->_namespaceSeparator === substr($className, 0, strlen($this->_namespace.$this->_namespaceSeparator)))
		{
			$fileName = '';
			if (false !== ($lastNsPos = strripos($className, $this->_namespaceSeparator))) {
				$namespace = substr($className, 0, $lastNsPos);
				$className = substr($className, $lastNsPos + 1);
				$fileName = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
			}

			$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className);
			$fileClassPathWithoutExtension = ($this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '') . $fileName;
			foreach ($this->_fileExtension as $fileExtension)
			{
				$fullClassPath = $fileClassPathWithoutExtension . $fileExtension;
				if (file_exists($fullClassPath))
				{
					require_once $fullClassPath;
					return true;
				}
			}
		}

		return false;
	}
}