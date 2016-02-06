<?php

namespace WebChemistry\Parameters\Database;

use Doctrine\ORM\EntityManager;
use Nette\Object;
use WebChemistry\Parameters\IDatabase;

class Doctrine extends Object implements IDatabase {

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @return array
	 */
	public function getPairs() {
		$return = [];

		foreach ($this->entityManager->getRepository('Entity\Parameter')->findAll() as $row) {
			$return[$row->id] = $row->content;
		}

		return $return;
	}

	/**
	 * @param string $id
	 * @param mixed $value
	 * @return void
	 */
	public function persist($id, $value) {
		$entity = new \Entity\Parameter;
		$entity->id = $id;
		$entity->content = is_array($value) ? serialize($value) : ($value === NULL ? $value : (string) $value);
		$entity->setIsSerialized(is_array($value));

		$this->entityManager->persist($entity);
	}

	/**
	 * @param string $id
	 * @param string|int|float $value
	 * @return void
	 */
	public function merge($id, $value) {
		$entity = new \Entity\Parameter;
		$entity->id = $id;
		$entity->content = is_array($value) ? serialize($value) : ($value === NULL ? $value : (string) $value);
		$entity->setIsSerialized(is_array($value));

		$this->entityManager->merge($entity);
	}

	/**
	 * @return void
	 */
	public function flush() {
		$this->entityManager->flush();
	}

	/**
	 * @return void
	 */
	public function clean() {
		$this->entityManager->getRepository('Entity\Parameter')
			->createQueryBuilder('e')
			->delete()
			->getQuery()
			->getResult();
	}

}
