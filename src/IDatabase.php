<?php

namespace WebChemistry\Parameters;

interface IDatabase {

	/**
	 * @return array
	 */
	public function getPairs();

	/**
	 * @param string $id
	 * @param string|int|float $value
	 * @return void
	 */
	public function persist($id, $value);

	/**
	 * @param string $id
	 * @param string|int|float $value
	 * @return void
	 */
	public function merge($id, $value);

	/**
	 * @return void
	 */
	public function flush();

	/**
	 * @return void
	 */
	public function clean();
}