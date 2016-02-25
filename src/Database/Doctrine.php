<?php

namespace WebChemistry\Parameters\Database;

use Doctrine\ORM\EntityManager;
use Nette\Object;
use WebChemistry\Parameters\IDatabase;
use WebChemistry\Parameters\IEntity;

class Doctrine extends Object implements IDatabase {

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var string */
	private $entity;

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
		foreach ($this->entityManager->getRepository($this->entity)->findAll() as $row) {
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
		$entity = $this->createEntity();
		$entity->setId($id);
		$entity->setContent(is_array($value) ? serialize($value) : $value);
		$entity->setIsSerialized(is_array($value));

		$this->entityManager->persist($entity);
	}

	/**
	 * @param string $id
	 * @param string|int|float $value
	 * @return void
	 */
	public function merge($id, $value) {
		$entity = $this->createEntity();
		$entity->setId($id);
		$entity->setContent(is_array($value) ? serialize($value) : $value);
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
		$this->entityManager->getRepository($this->entity)
			->createQueryBuilder('e')
			->delete()
			->getQuery()
			->getResult();
	}

	/**
	 * @return IEntity
	 */
	private function createEntity() {
		return new $this->entity;
	}

	/**
	 * @param string $entity
	 * @return self
	 */
	public function setEntity($entity) {
		$this->entity = $entity;

		return $this;
	}

}
