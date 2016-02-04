<?php

namespace WebChemistry\Parameters;

use Entity\Parameter;
use Kdyby\Doctrine\EntityManager;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\MemberAccessException;
use Nette\Object;
use Nette\Utils\ArrayHash;

class Provider extends \stdClass implements \ArrayAccess {

	const PARAMETER_KEY = 'parameters';

	/** @var ArrayAccessor */
	private $parameters;

	/** @var Cache */
	private $cache;

	/** @var array */
	private $defaults;

	/** @var bool */
	private $useCache;

	/** @var \WebChemistry\Parameters\IDatabase */
	private $database;

	/**
	 * @param array $parameters
	 * @param bool $useCache
	 * @param IDatabase $database
	 * @param IStorage $storage
	 */
	public function __construct(array $parameters, $useCache, IDatabase $database, IStorage $storage) {
		$this->defaults = $parameters;
		$this->cache = new Cache($storage, 'webchemistry');
		$this->useCache = $useCache;
		$this->database = $database;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function addNewParameter($name, $value) {
		$this->defaults[$name] = $this->parseValue($value);
	}

	/**
	 * @internal
	 */
	public function import() {
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
	 * @param string $name
	 * @return mixed
	 */
	protected function getParameter($name) {
		if ($this->parameters === NULL) {
			$this->getParameters();
		}

		return $this->parameters[$name];
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setParameter($name, $value) {
		if ($this->parameters === NULL) {
			$this->getParameters();
		}

		$this->parameters[$name] = $this->parseValue($value);
	}

	/**
	 * @return ArrayAccessor
	 */
	public function getParameters() {
		if ($this->parameters !== NULL) {
			return $this->parameters;
		}

		if ($this->useCache) {
			$value = $this->cache->load(self::PARAMETER_KEY);
		} else {
			$value = NULL;
		}

		if (!$value) {
			$value = $this->database->getPairs();

			if ($this->useCache) {
				$this->cache->save(self::PARAMETER_KEY, $value);
			}
		}

		return $this->parameters = new ArrayAccessor($value);
	}

	/**
	 * @return void
	 */
	public function cleanParametersCache() {
		$this->cache->remove(self::PARAMETER_KEY);
		$this->parameters = NULL;
	}

	/**
	 * @return bool
	 */
	public function isChanged() {
		return $this->getParameters()->isChanged();
	}

	/**
	 * Rewrite values in database with current values
	 *
	 * @return bool FALSE - Values are not changed
	 */
	public function merge() {
		if (!$this->isChanged()) {
			return FALSE;
		}

		$array = $this->getParameters()->getArray();

		foreach ($this->getParameters()->getChanged() as $key) {
			$this->database->merge($key, $array[$key]);
		}

		$this->database->flush();
		$this->cleanParametersCache();
		$this->parameters = NULL;

		return TRUE;
	}

	/**
	 * Truncate database
	 */
	public function cleanDatabase() {
		$this->database->clean();
	}

	/**
	 * Convert Traversable to array
	 *
	 * @param mixed $value
	 * @return string|array
	 */
	protected function parseValue($value) {
		if ($value instanceof \Traversable) {
			return $this->recursiveIteratorToArray($value);
		}

		return $value === NULL ? $value : (string) $value;
	}

	/**
	 * @param \Traversable $traversable
	 * @return array
	 */
	private function recursiveIteratorToArray(\Traversable $traversable) {
		$array = [];

		foreach ($traversable as $key => $value) {
			if (is_array($value) || $value instanceof \Traversable) {
				$array[$key] = $this->recursiveIteratorToArray($value);
			} else {
				$array[$key] = $value;
			}
		}

		return $array;
	}

	/************************* Magic methods **************************/
	
	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name) {
		$return = $this->offsetGet($name);

		return $return;
	}

	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value) {
		$this->offsetSet($name, $value);
	}

	/**
	 * Is property defined?
	 *
	 * @param  string  property name
	 * @return bool
	 */
	public function __isset($name) {
		return $this->offsetExists($name);
	}

	/**
	 * Access to undeclared property.
	 *
	 * @param  string  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	public function __unset($name) {
		$this->offsetUnset($name);
	}

	public function offsetExists($offset) {
		$parameters = $this->getParameters();
		return isset($parameters[$offset]);
	}

	public function offsetGet($offset) {
		$value = $this->getParameter($offset);

		return $value;
	}

	public function offsetSet($offset, $value) {
		$this->setParameter($offset, $value);
	}

	public function offsetUnset($offset) {
		$this->setParameter($offset, NULL);
	}

}
