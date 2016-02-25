<?php

namespace WebChemistry\Parameters;

interface IEntity {

	/**
	 * @param int $id
	 */
	public function setId($id);

	/**
	 * @param mixed $content
	 */
	public function setContent($content);

	/**
	 * @param bool $serialized
	 * @return mixed
	 */
	public function setIsSerialized($serialized);

}
