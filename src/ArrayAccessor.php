<?php

namespace WebChemistry\Parameters;

use Traversable;

class ArrayAccessor extends \stdClass implements \ArrayAccess {

	/** @var array */
	private $changed = [];

	/** @var array */
	private $data = [];

	/** @var \WebChemistry\Parameters\ArrayAccessor */
	private $parent;

	/** @var string */
	private $keyName;

	/** @var bool */
	private $recursive;

	/**
	 * @param array $array
	 * @param bool $recursive
	 * @param ArrayAccessor $parent
	 * @param string $keyName
	 */
	public function __construct(array $array, $recursive = TRUE, ArrayAccessor $parent = NULL, $keyName = NULL) {
		$this->parent = $parent;
		$this->keyName = $keyName;
		$this->recursive = $recursive;

		foreach ($array as $key => $value) {
			if ($recursive && is_array($value)) {
				$this->data[$key] = new self($value, $recursive, $this, $key);
			} else {
				$this->data[$key] = $value;
			}
		}
	}

	/**
	 * Convert ArrayAccessor and his childrens to array
	 *
	 * @return array
	 */
	public function getArray() {
		$return = [];

		foreach ($this->data as $key => $value) {
			if ($value instanceof ArrayAccessor) {
				$return[$key] = $value->getArray();
			} else {
				$return[$key] = $value;
			}
		}

		return $return;
	}

	/**
	 * @param string $name
	 * @internal
	 */
	public function changed($name) {
		if (!in_array($name, $this->changed)) {
			$this->changed[] = $name;

			if ($this->parent) {
				$this->parent->changed($this->keyName);
			}
		}
	}

	/**
	 * @return array
	 */
	public function getChanged() {
		return $this->changed;
	}

	/**
	 * @return bool
	 */
	public function isChanged() {
		return (bool) $this->changed;
	}

	public function __get($name) {
		return $this->data[$name];
	}

	public function __set($name, $value) {
		$this->changed($name);

		if ($this->recursive && is_array($value)) {
			$value = new self($value, $this->recursive, $this, $name);
		}

		$this->data[$name] = $value;
	}

	public function __isset($name) {
		return array_key_exists($name, $this->data);
	}

	public function __unset($name) {
		$this->changed($name);
		$this->data[$name] = NULL;
	}

	public function offsetExists($offset) {
		return $this->__isset($offset);
	}

	public function offsetGet($offset) {
		return $this->__get($offset);
	}

	public function offsetSet($offset, $value) {
		$this->__set($offset, $value);
	}

	public function offsetUnset($offset) {
		$this->__unset($offset);
	}

}
