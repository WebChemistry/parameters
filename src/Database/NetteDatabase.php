<?php

namespace WebChemistry\Parameters\Database;

use Nette\Database\Context;
use WebChemistry\Parameters\IDatabase;

class NetteDatabase implements IDatabase {

	const TABLE_NAME = 'parameter';

	/** @var \Nette\Database\Context */
	private $context;

	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * @return array
	 */
	public function getPairs() {
		$result = $this->context->table(self::TABLE_NAME)->fetchAll();
		$array = [];

		foreach ($result as $row) {
			$array[$row->id] =  $row->is_serialized ? unserialize($row->content) : $row->content;
		}

		return $array;
	}

	public function persist($id, $value) {
		$this->context->table(self::TABLE_NAME)->insert([
			'id' => $id,
			'content' => is_array($value) ? serialize($value) : ($value === NULL ? $value : (string) $value),
			'is_serialized' => is_array($value)
		]);
	}

	public function merge($id, $value) {
		$this->context->table(self::TABLE_NAME)->where('id = ?', $id)->update([
			'content' => is_array($value) ? serialize($value) : ($value === NULL ? $value : (string) $value),
			'is_serialized' => is_array($value)
		]);
	}

	public function flush() {
		return;
	}

	public function clean() {
		$this->context->query('TRUNCATE TABLE ' . self::TABLE_NAME . ';');
	}

}