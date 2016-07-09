<?php

namespace WebChemistry\Parameters;

interface IEntity {

	/**
	 * @param string $id
	 */
	public function setId($id);

	/**
	 * @param string $content
	 */
	public function setContent($content);

	/**
	 * @param bool $serialized
	 */
	public function setIsSerialized($serialized);

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getContent();

	/**
	 * @return string
	 */
	public function getIsSerialized();

}
