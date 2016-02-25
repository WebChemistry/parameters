<?php

namespace WebChemistry\Parameters;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\MemberAccessException;

class Provider implements \ArrayAccess {

	const PARAMETER_KEY = 'parameters';
	const NAMESPACE_STORAGE = 'WebChemistry.Parameters';

	/** @var ArrayAccessor */
	private $parameters;

	/** @var Cache */
	private $cache;

	/** @var array */
	private $defaults;

	/** @var \WebChemistry\Parameters\IDatabase */
	private $database;

	/**
	 * @param array $defaults
	 * @param bool $useCache
	 * @param IDatabase $database
	 * @param IStorage $storage
	 */
	public function __construct(array $defaults, $useCache, IDatabase $database = NULL, IStorage $storage = NULL) {
		$this->defaults = $defaults;
		if ($storage && $useCache) {
			$this->cache = new Cache($storage, self::NAMESPACE_STORAGE);
		}
		$this->database = $database;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function addNewParameter($name, $value) {
		$this->defaults[$name] = $this->parseValue($value);
		if ($this->parameters) {
			$this->parameters[$name] = $this->parseValue($value);
		}
	}

	/**
	 * @internal
	 */
	public function import() {
		if (!$this->database) {
			return;
		}
		$flush = FALSE;
		$parameters = $this->getParameters();
		foreach ($this->getDefaultParameters() as $name => $value) {
			if (!isset($parameters[$name])) {
				$flush = TRUE;
				$this->database->persist($name, $value);
			}
		}

		if ($flush) {
			$this->database->flush();
			$this->cleanParametersCache();
		}
	}

	/**
	 * @return array
	 */
	public function getDefaultParameters() {
		return $this->defaults;
	}

	/**
	 * @return ArrayAccessor
	 */
	public function getParameters() {
		if ($this->parameters === NULL) {
			$value = $this->cache ? $this->cache->load(self::PARAMETER_KEY) : NULL;
			if (!$value) {
				if ($this->database) {
					$value = $this->database->getPairs();
					if ($this->cache) {
						$this->cache->save(self::PARAMETER_KEY, $value);
					}
				} else {
					$value = $this->defaults;
				}
			}
			$this->parameters = new ArrayAccessor($value);
		}

		return $this->parameters;
	}

	/**
	 * @return void
	 */
	public function cleanParametersCache() {
		$this->parameters = NULL;
		if ($this->cache) {
			$this->cache->remove(self::PARAMETER_KEY);
		}
	}

	/**
	 * Rewrite values in database with current values
	 *
	 * @return bool FALSE - Values are not changed
	 */
	public function merge() {
		if (!$this->database) {
			return FALSE;
		}
		$diff = $this->parameters->getChanged();
		if (!$diff) {
			return FALSE;
		}
		$parameters = $this->getParameters()->getArray();

		foreach ($diff as $key) {
			$this->database->merge($key, $parameters[$key]);
		}
		$this->database->flush();
		$this->cleanParametersCache();

		return TRUE;
	}

	/**
	 * Truncate database
	 */
	public function cleanDatabase() {
		if ($this->database) {
			$this->database->clean();
		}
	}

	/************************* Magic methods **************************/

	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param string $name property name
	 * @return mixed property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function __get($name) {
		return $this->getParameters()->__get($name);
	}

	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param string $name property name
	 * @param mixed $value property value
	 * @return void
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value) {
		$this->getParameters()->__set($name, $value);
	}

	/**
	 * Is property defined?
	 *
	 * @param string $name property name
	 * @return bool
	 */
	public function __isset($name) {
		return $this->getParameters()->__isset($name);
	}

	/**
	 * Access to undeclared property.
	 *
	 * @param string $name property name
	 * @return void
	 * @throws MemberAccessException
	 */
	public function __unset($name) {
		$this->getParameters()->__unset($name);
	}

	/************************* ArrayAccess **************************/

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return $this->__isset($offset);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->__get($offset);
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value) {
		$this->__set($offset, $value);
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset) {
		$this->__unset($offset);
	}

}
