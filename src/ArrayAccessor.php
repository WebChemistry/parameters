<?php

namespace WebChemistry\Parameters;

class ArrayAccessor implements \ArrayAccess {

	/** @var array */
	private $changed = [];

	/** @var array */
	private $data = [];

	/** @var array keys */
	private $arrayAccessors = [];

	/**
	 * @param array $array
	 */
	public function __construct(array $array) {
		$this->data = $array;
	}

	/**
	 * Convert ArrayAccessor and his children to array
	 *
	 * @return array
	 */
	public function getArray() {
		$return = $this->data;
		foreach ($this->arrayAccessors as $key => $void) {
			$return[$key] = $return[$key]->getArray();
		}

		return $return;
	}

	/**
	 * @return array
	 */
	public function getChanged() {
		foreach ($this->arrayAccessors as $key => $void) {
			if (!isset($this->changed[$key]) && $this->data[$key]->getChanged()) {
				$this->changed[$key] = TRUE;
			}
		}

		return array_keys($this->changed);
	}

	/**
	 * Convert Traversable to array
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	private function parseValue($value) {
		if ($value instanceof \Traversable) {
			return $this->recursiveIteratorToArray($value);
		}

		return $value;
	}

	/**
	 * @param \Traversable $traversable
	 * @return array
	 */
	private function recursiveIteratorToArray(\Traversable $traversable) {
		$array = [];
		foreach ($traversable as $key => $value) {
			$array[$key] = $value instanceof \Traversable ? $this->recursiveIteratorToArray($value) : $value;
		}

		return $array;
	}

	/**
	 * @param string|int $name
	 * @return mixed|ArrayAccessor
	 * @throws ParameterNotExistsException
	 */
	public function __get($name) {
		if (!$this->__isset($name)) {
			throw new ParameterNotExistsException($name);
		}
		$value = &$this->data[$name];
		if (is_array($value)) {
			$this->arrayAccessors[$name] = TRUE;
			$value = new self($value); // Lazy
		}

		return $value;
	}

	/**
	 * @param string|int $name
	 * @param mixed|ArrayAccessor $value
	 */
	public function __set($name, $value) {
		if ($value instanceof ArrayAccessor) {
			$this->arrayAccessors[$name] = TRUE;
		} else if (isset($this->arrayAccessors[$name])) {
			unset($this->arrayAccessors[$name]);
		}

		$this->changed[$name] = TRUE;
		$this->data[$name] = $this->parseValue($value);
	}

	/**
	 * @param string|int $name
	 * @return bool
	 */
	public function __isset($name) {
		return array_key_exists($name, $this->data);
	}

	/**
	 * @param string $name
	 */
	public function __unset($name) {
		if (isset($this->arrayAccessors[$name])) {
			unset($this->arrayAccessors[$name]);
		}
		$this->changed[$name] = TRUE;
		$this->data[$name] = NULL;
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return $this->__isset($offset);
	}

	/**
	 * @param string|int $offset
	 * @return mixed|ArrayAccessor
	 */
	public function offsetGet($offset) {
		return $this->__get($offset);
	}

	/**
	 * @param string|int $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value) {
		$this->__set($offset, $value);
	}

	/**
	 * @param string|int $offset
	 */
	public function offsetUnset($offset) {
		$this->__unset($offset);
	}

}
